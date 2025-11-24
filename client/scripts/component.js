export function signin() {
    return `
        <h1>Sign In</h1>

        <input id="email" type="email" placeholder="Email"><br><br>
        <input id="password" type="password" placeholder="Password"><br><br>

        <button id="signinBtn">Sign In</button>

        <p>Don't have an account? 
            <button id="toSignup">Create one</button>
        </p>
    `;
}

export function signup() {
    return `
        <h1>Create Account</h1>

        <input id="name" type="text" placeholder="Full Name"><br><br>
        <input id="email" type="email" placeholder="Email"><br><br>
        <input id="password" type="password" placeholder="Password"><br><br>
        <button id="signupBtn">Sign Up</button>

        <p>Already have an account?
            <button id="toSignin">Sign In</button>
        </p>
    `;
}


// component.js - Chat Components

// ==================== CHAT LIST ====================
export function chatListComponent() {
    return `
        <div class="chat-header">
            <h1>Messages</h1>
            <div class="header-actions">
                <button id="viewAllMessagesBtn" class="btn-secondary">All Messages</button>
                <button id="newMessageBtn" class="btn-primary">New Message</button>
                <button id="catchUpAIBtn" class="btn-secondary">AI Summary</button>
            </div>
        </div>
        <div id="aiSummary" class="ai-summary-container"></div>
        <div class="chat-tabs">
            <button id="newChatsTab" class="tab-btn active">New Messages</button>
            <button id="refreshBtn" class="tab-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                </svg>
                Refresh
            </button>
        </div>
        <div id="chatList" class="chat-list">
            <div class="loading">Loading messages...</div>
        </div>
    `;
}

// ==================== NEW MESSAGE FORM ====================
export function newMessageComponent() {
    return `
        <div class="message-form-container">
            <div class="form-header">
                <h1>New Message</h1>
                <button id="backToListBtn" class="btn-back">← Back</button>
            </div>
            
            <form id="messageForm" class="message-form">
                <div class="form-group">
                    <label for="receiverEmail">Recipient Email</label>
                    <input 
                        type="email" 
                        id="receiverEmail" 
                        placeholder="user@example.com"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="messageContent">Message</label>
                    <textarea 
                        id="messageContent" 
                        placeholder="Type your message..."
                        rows="5"
                        required
                    ></textarea>
                </div>
                
                <button type="submit" id="sendMessageBtn" class="btn-primary">
                    Send Message
                </button>
            </form>
            
            <div id="sendResponse" class="response-message"></div>
        </div>
    `;
}

// ==================== CONVERSATION VIEW ====================
export function conversationComponent() {
    return `
        <div class="conversation-container">
            <div class="conversation-header">
                <button id="backToChatsBtn" class="btn-back">← Back</button>
                <h2 id="conversationTitle">Conversation</h2>
                <button id="markAllReadBtn" class="btn-secondary">Mark as Read</button>
            </div>
            
            <div id="messagesContainer" class="messages-container">
                <div class="loading">Loading conversation...</div>
            </div>
            
            <div class="message-input-area">
                <textarea 
                    id="replyContent" 
                    placeholder="Type a reply..."
                    rows="2"
                ></textarea>
                <button id="sendReplyBtn" class="btn-send">Send</button>
            </div>
        </div>
    `;
}

export function allMessagesComponent() {
    return `
        <div class="conversation-container">
            <div class="conversation-header">
                <button id="backToChatsBtn" class="btn-back">← Back</button>
                <h2>All Messages</h2>
                <button id="refreshAllBtn" class="btn-secondary">Refresh</button>
            </div>
            
            <div id="allMessagesContainer" class="messages-container all-messages-view">
                <div class="loading">Loading all messages...</div>
            </div>
        </div>
    `;
}

// ==================== RENDER FUNCTIONS ====================

export function renderChatItem(msg) {
    const isUnread = msg.status === 'delivered' || msg.status === 'sent';
    const senderName = msg.senderName || msg.sender_name || 'Unknown User';
    const senderEmail = msg.senderEmail || msg.sender_email || '';
    const content = msg.content || '';
    const sendAt = msg.sendAt || msg.sent_at || msg.send_at || '';
    const status = msg.status || 'sent';
    
    return `
        <div class="chat-item ${isUnread ? 'unread' : ''}" 
             data-sender-email="${senderEmail}">
            <div class="chat-avatar">${senderName[0].toUpperCase()}</div>
            <div class="chat-content">
                <div class="chat-sender">
                    <strong>${senderName}</strong>
                    <span class="status-badge status-${status}">${status}</span>
                </div>
                <div class="chat-preview">${content.substring(0, 60)}${content.length > 60 ? '...' : ''}</div>
                <div class="chat-time">${formatTime(sendAt)}</div>
            </div>
        </div>
    `;
}

export function renderMessage(msg, currentUserId) {
    const senderId = msg.sender_id || msg.senderId;
    const isSent = senderId == currentUserId;
    const content = msg.content || '';
    const sendAt = msg.sendAt || msg.send_at || msg.sent_at || '';
    const status = msg.status || '';
    
    return `
        <div class="message ${isSent ? 'sent' : 'received'}">
            <div class="message-content">${escapeHtml(content)}</div>
            <div class="message-meta">
                <span class="message-time">${formatTime(sendAt)}</span>
                ${isSent ? `<span class="message-status">${status}</span>` : ''}
            </div>
        </div>
    `;
}

export function renderAllMessageItem(msg, currentUserId) {
    console.log('Rendering message:', msg); // Debug log
    
    // Extract all possible field variations
    const senderId = msg.sender_id || msg.senderId;
    const receiverId = msg.receiver_id || msg.receiverId;
    const isSent = senderId == currentUserId;
    
    // Try all possible content field names
    const content = msg.content || msg.Content || msg.message || msg.text || 'No content';
    
    // Try all possible timestamp field names
    const sendAt = msg.send_at || msg.sendAt || msg.sent_at || msg.send_At || msg.created_at || '';
    
    // Try all possible status field names
    const status = msg.status || msg.Status || 'sent';
    
    // Get user names with multiple fallback options
    let otherUserName;
    if (isSent) {
        otherUserName = msg.receiverName || msg.receiver_name || msg.receiverEmail || msg.receiver_email || `User ${receiverId}`;
    } else {
        otherUserName = msg.senderName || msg.sender_name || msg.senderEmail || msg.sender_email || `User ${senderId}`;
    }
    
    // Ensure content is not empty
    const displayContent = content && content.trim() !== '' ? content : '(Empty message)';
    
    return `
        <div class="message-card ${isSent ? 'sent-card' : 'received-card'}">
            <div class="message-card-header">
                <div class="message-card-user">
                    <div class="small-avatar">${otherUserName[0] ? otherUserName[0].toUpperCase() : 'U'}</div>
                    <strong>${isSent ? 'To' : 'From'}: ${escapeHtml(otherUserName)}</strong>
                </div>
                <span class="status-badge status-${status}">${status}</span>
            </div>
            <div class="message-card-body">${escapeHtml(displayContent)}</div>
            <div class="message-card-footer">
                <span>${formatTime(sendAt)}</span>
                <span class="message-id">#${msg.id || ''}</span>
            </div>
        </div>
    `;
}

export function renderEmptyState(message) {
    return `
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <p>${message}</p>
        </div>
    `;
}

export function renderError(message) {
    return `
        <div class="error-message">
            <strong>Error:</strong> ${escapeHtml(message)}
        </div>
    `;
}

export function renderSuccess(message) {
    return `
        <div class="success-message">
            ${escapeHtml(message)}
        </div>
    `;
}

export function renderLoading() {
    return `
        <div class="loading">
            <div class="spinner"></div>
            <p>Loading...</p>
        </div>
    `;
}

// ==================== HELPER FUNCTIONS ====================

function formatTime(timestamp) {
    if (!timestamp) return '';
    
    const date = new Date(timestamp);
    
    // Check if date is valid
    if (isNaN(date.getTime())) return '';
    
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
