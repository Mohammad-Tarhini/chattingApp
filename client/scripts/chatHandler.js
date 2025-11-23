// chatHandlers.js
import {
    chatListComponent,
    newMessageComponent,
    conversationComponent,
    allMessagesComponent,
    renderChatItem,
    renderMessage,
    renderAllMessageItem,
    renderEmptyState,
    renderError,
    renderSuccess,
    renderLoading
} from './component.js';

import { BASE_URL } from './constants.js';

const container = document.getElementById("container");

// ==================== API SERVICE ====================
const ChatAPI = {
    sendMessage: (data) => {
        const params = new URLSearchParams();
        params.append('user_id', data.userId);
        params.append('content', data.content);

        if (data.receiverEmail) params.append('receiverEmail', data.receiverEmail);
        if (data.receiverId) params.append('receiverId', data.receiverId);

        return axios.post(BASE_URL + "chat/send", params, {
            headers: { "Content-Type": "application/x-www-form-urlencoded" }
        });
    },

    receiveNew: (userId) => {
        const params = new URLSearchParams();
        params.append('user_id', userId);

        return axios.post(BASE_URL + "chat/receiveNew", params, {
            headers: { "Content-Type": "application/x-www-form-urlencoded" }
        });
    },

    readMessage: (userId, messageId) => {
        const params = new URLSearchParams();
        params.append('user_id', userId);
        params.append('message_id', messageId);

        return axios.post(BASE_URL + "chat/readMessage", params, {
            headers: { "Content-Type": "application/x-www-form-urlencoded" }
        });
    },

    getAllWithUser: (userId, otherUserEmail) => {
        const params = new URLSearchParams();
        params.append('user_id', userId);
        params.append('otherUserEmail', otherUserEmail);

        return axios.post(BASE_URL + "chat/getAllWithUser", params, {
            headers: { "Content-Type": "application/x-www-form-urlencoded" }
        });
    },

    getAllForUser: (userId) => {
        const params = new URLSearchParams();
        params.append('user_id', userId);

        return axios.post(BASE_URL + "chat/getAllForUser", params, {
            headers: { "Content-Type": "application/x-www-form-urlencoded" }
        });
    },

    catchUpAI: (userId) => {
        const params = new URLSearchParams();
        params.append('user_id', userId);

        return axios.post(BASE_URL + "chat/catchUpAI", params, {
            headers: { "Content-Type": "application/x-www-form-urlencoded" }
        });
    }
};

// ==================== HELPERS ====================
function getUserId() {
    const userId = localStorage.getItem("userId");
    if (!userId) {
        alert("Please sign in first");
        window.location.href = "index.html";
        return null;
    }
    return userId;
}

// ==================== SHOW CHAT LIST ====================
export function showChatList() {
    const userId = getUserId();
    if (!userId) return;

    container.innerHTML = chatListComponent();
    loadChatList(userId);

    document.getElementById("newMessageBtn").addEventListener("click", showNewMessage);
    document.getElementById("viewAllMessagesBtn").addEventListener("click", showAllMessages);
    document.getElementById("refreshBtn").addEventListener("click", () => loadChatList(userId));

    document.getElementById("catchUpAIBtn").addEventListener("click", async () => {
        const summaryDiv = document.getElementById("aiSummary");
        summaryDiv.innerHTML = renderLoading();

        try {
            const res = await ChatAPI.catchUpAI(userId);

            if (res.data.success) {
                summaryDiv.innerHTML = `
                    <div class="ai-summary">
                        <div class="ai-summary-header">
                            <strong>📝 AI Summary</strong>
                            <button onclick="this.parentElement.parentElement.remove()" class="close-btn">×</button>
                        </div>
                        <div class="ai-summary-content">${res.data.data}</div>
                    </div>
                `;
            } else {
                summaryDiv.innerHTML = renderError(res.data.message);
            }
        } catch (err) {
            const message = err?.response?.data?.message || err?.message || "Failed to generate summary";
            summaryDiv.innerHTML = renderError(message);
        }
    });
}

// ==================== LOAD CHAT LIST ====================
async function loadChatList(userId) {
    const chatListDiv = document.getElementById("chatList");
    chatListDiv.innerHTML = renderLoading();

    try {
        const res = await ChatAPI.receiveNew(userId);

        if (res.data.success && Array.isArray(res.data.data) && res.data.data.length > 0) {
            chatListDiv.innerHTML = res.data.data.map(msg => renderChatItem(msg)).join('');

            document.querySelectorAll(".chat-item").forEach(item => {
                item.addEventListener("click", () => {
                    const senderEmail = item.getAttribute("data-sender-email");
                    if (senderEmail) showConversation(senderEmail);
                });
            });
        } else {
            chatListDiv.innerHTML = renderEmptyState("No new messages");
        }
    } catch (err) {
        const message = err?.response?.data?.message || err?.message || "Failed to load messages";
        chatListDiv.innerHTML = renderError(message);
    }
}

// ==================== NEW MESSAGE ====================
export function showNewMessage() {
    container.innerHTML = newMessageComponent();

    const form = document.getElementById("messageForm");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const userId = getUserId();
        if (!userId) return;

        const receiverEmail = document.getElementById("receiverEmail").value.trim();
        const content = document.getElementById("messageContent").value.trim();
        const responseDiv = document.getElementById("sendResponse");

        if (!receiverEmail || !content) {
            responseDiv.innerHTML = renderError("Please fill in all fields");
            return;
        }

        const sendBtn = document.getElementById("sendMessageBtn");
        sendBtn.disabled = true;
        sendBtn.textContent = "Sending...";

        try {
            const res = await ChatAPI.sendMessage({
                userId,
                receiverEmail,
                content
            });

            if (res.data.success) {
                form.reset();
                responseDiv.innerHTML = renderSuccess("Message sent!");
                setTimeout(() => showChatList(), 1500);
            } else {
                responseDiv.innerHTML = renderError(res.data.message);
            }
        } catch (err) {
            const message = err?.response?.data?.message || err?.message || "Failed to send message";
            responseDiv.innerHTML = renderError(message);
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = "Send Message";
        }
    });

    document.getElementById("backToListBtn").addEventListener("click", showChatList);
}

// ==================== CONVERSATION VIEW ====================
export function showConversation(otherUserEmail) {
    const userId = getUserId();
    if (!userId) return;

    container.innerHTML = conversationComponent();
    document.getElementById("conversationTitle").textContent = otherUserEmail;

    loadConversation(userId, otherUserEmail);

    document.getElementById("backToChatsBtn").addEventListener("click", showChatList);

    // Send button
    document.getElementById("sendReplyBtn").addEventListener("click", async () => {
        const content = document.getElementById("replyContent").value.trim();

        if (!content) return alert("Type a message");

        const sendBtn = document.getElementById("sendReplyBtn");
        sendBtn.disabled = true;
        sendBtn.textContent = "Sending...";

        try {
            await ChatAPI.sendMessage({ userId, receiverEmail: otherUserEmail, content });
            document.getElementById("replyContent").value = "";
            loadConversation(userId, otherUserEmail);
        } catch (err) {
            alert("Error sending message");
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = "Send";
        }
    });

    // Enter = send, Shift+Enter = newline
    document.getElementById("replyContent").addEventListener("keydown", (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            document.getElementById("sendReplyBtn").click();
        }
    });

    // Mark all as read
    document.getElementById("markAllReadBtn").addEventListener("click", async () => {
        try {
            const res = await ChatAPI.getAllWithUser(userId, otherUserEmail);

            if (res.data.success) {
                for (let msg of res.data.data) {
                    if (msg.receiver_id == userId && msg.status !== 'seen') {
                        await ChatAPI.readMessage(userId, msg.id);
                    }
                }
                loadConversation(userId, otherUserEmail);
            }
        } catch (err) {
            console.error("Mark read error:", err);
        }
    });
}

// ==================== LOAD CONVERSATION ====================
async function loadConversation(userId, otherUserEmail) {
    const msgContainer = document.getElementById("messagesContainer");
    msgContainer.innerHTML = renderLoading();

    try {
        const res = await ChatAPI.getAllWithUser(userId, otherUserEmail);

        if (res.data.success) {
            msgContainer.innerHTML = res.data.data
                .map(msg => renderMessage(msg, userId))
                .join('');

            msgContainer.scrollTop = msgContainer.scrollHeight;
        } else {
            msgContainer.innerHTML = renderEmptyState("Start the conversation!");
        }
    } catch (err) {
        const message = err?.response?.data?.message || err?.message || "Failed to load conversation";
        msgContainer.innerHTML = renderError(message);
    }
}

// ==================== ALL MESSAGES ====================
export function showAllMessages() {
    const userId = getUserId();
    if (!userId) return;

    container.innerHTML = allMessagesComponent();
    loadAllMessages(userId);

    document.getElementById("backToChatsBtn").addEventListener("click", showChatList);
    document.getElementById("refreshAllBtn").addEventListener("click", () => loadAllMessages(userId));
}

async function loadAllMessages(userId) {
    const box = document.getElementById("allMessagesContainer");
    box.innerHTML = renderLoading();

    try {
        const res = await ChatAPI.getAllForUser(userId);

        if (res.data.success && Array.isArray(res.data.data)) {
            box.innerHTML = res.data.data
                .map(msg => renderAllMessageItem(msg, userId))
                .join('');
        } else {
            box.innerHTML = renderEmptyState("No messages found");
        }
    } catch (err) {
        const message = err?.response?.data?.message || err?.message || "Failed to load messages";
        box.innerHTML = renderError(message);
    }
}

// INITIALIZE PAGE
showChatList();
