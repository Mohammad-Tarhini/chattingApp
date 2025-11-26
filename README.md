Chatting App (PHP Backend + Vanilla JavaScript Frontend)

A lightweight real-time messaging system built with pure PHP (no framework) and a vanilla JavaScript frontend.
The application includes user authentication, user profile management, messaging features, and an AI-powered message summary using the OpenAI API.

This project was built following clean structure, SOLID-inspired layering, and proper separation between:

Controllers

Services

Models

External Services

Frontend

Configuration & Utilities

🚀 Features
🔐 Authentication System

User Sign Up with validation

User Sign In

Password hashing (PHP password_hash)

Authorization middleware

Email format validation

👤 User Management

Get User Info

Update User Info

Get All Users

💬 Chat System

Send Messages

Receive new messages (delivered status)

Mark messages as read (seen status)

Retrieve conversation with a specific user

Retrieve all user messages

Fully timestamped (send, delivered, read)

🤖 AI-Powered Message Summary

Uses OpenAI API to:

Summarize all delivered messages for a user

Retry automatically on invalid responses

Accepts large message arrays and generates short summaries

🌐 Vanilla Frontend

The project includes a simple frontend built using:

HTML

CSS

Vanilla JavaScript + Fetch API

The frontend contains:

Signup & login pages

Chat UI

Fetch calls to the backend endpoints

Auto-refresh of messages

Message sending box

📁 Project Structure
project/
│
├── controllers/
│   ├── AuthoController.php
│   ├── ChatController.php
│   └── UserController.php
│
├── services/
│   ├── AuthoService.php
│   ├── ChatService.php
│   ├── UserService.php
│   └── ResponseService.php
│
├── externalServices/
│   ├── GenerateAiSummary.php
│   └── CallOpenAI.php
│
├── models/
│   ├── User.php
│   └── message.php
│
├── frontend/
│   ├── index.html
│   ├── chat.html
│   ├── style.css
│   └── script.js
│
├── utils/
│   ├── config.php
│   ├── headers.php
│   └── helpers.php
│
├── connection/
│   └── connection.php
│
└── index.php (router)

🛠️ Tech Stack
Backend

PHP 8+

MySQL

cURL

OpenAI Chat API

Vanilla PHP OOP

Frontend

HTML5

CSS3

Vanilla JavaScript

Fetch API

🔑 API Endpoints
Auth
Method	Endpoint	Description
POST	/auth/signup	Register a new user
POST	/auth/signin	Login user
User
Method	Endpoint	Description
GET	/user/info?user_id=	Get user details
POST	/user/update	Update user info
GET	/user/all	List all users
Chat
Method	Endpoint	Description
POST	/chat/send	Send a message
POST	/chat/receive	Receive new delivered messages
POST	/chat/read	Mark message as read
POST	/chat/conversation	Get messages with specific user
POST	/chat/all	Get all user's messages
POST	/chat/ai-summary	Get AI summary for delivered messages
🤖 AI Summary Example

Request:

POST /chat/ai-summary
{
  "user_id": 1
}


Response:

{
  "status": true,
  "data": "You have missed messages about meeting plans and updates from your contacts."
}

🧪 How to Run the Project
1️⃣ Clone the repo
git clone https://github.com/yourusername/chat-app.git
cd chat-app

2️⃣ Configure Database

Import SQL tables for:

users

messages

Update database credentials in:

connection/connection.php
utils/config.php

3️⃣ Add Your OpenAI API Key

Inside utils/config.php:

define("OPEN_AI_KEY", "your-api-key");

4️⃣ Start Local Server

Using XAMPP or PHP built-in server:


5️⃣ Open Frontend

Open in browser:

http://localhost:8080/frontend/index.ht
