<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
<style>
/* ====================== CHAT BUBBLE ====================== */
.chat-toggle {
  position: fixed;
  width: 60px;
  height: 60px;
  background:#111;
  color:white;
  border-radius:50%;
  display:flex;
  justify-content:center;
  align-items:center;
  cursor:pointer;
  font-size:26px;
  box-shadow:0 6px 18px rgba(0,0,0,0.3);
  transition:.25s;
  z-index:9999;
}
.chat-toggle:hover { transform:scale(1.08); }

.chat-box {
  position: fixed;
  width: 320px;
  height: 500px;
  background:white;
  border-radius:14px;
  box-shadow:0 10px 25px rgba(0,0,0,0.2);
  display:none;
  flex-direction:column;
  overflow:hidden;
  z-index:9999;
  transition: all 0.3s ease;
}

.chat-header {
  background:#000; 
  padding:12px; 
  color:white; 
  font-weight:600; 
  font-size:16px; 
  position: relative;
  text-align: center;
}

.chat-header .header-dots {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
}

.chat-messages {
  flex: 1;
  padding: 12px;
  overflow-y: auto;
  font-size: 14px;
  
  display: flex;        /* add this */
  flex-direction: column; /* stack messages vertically */
}



.chat-input { 
  display:flex; 
  border-top:1px solid #ddd; 
}

.chat-input input { 
  flex:1; 
  padding:10px; 
  border:none; 
  outline:none; 
}

.chat-input button { 
  background:#000; 
  border:none; 
  padding:0 18px; 
  color:white; 
  cursor:pointer; 
}

.datetime-box { 
  background:#f1f1f1; 
  text-align:center; 
  padding:6px; 
  font-size:12px; 
  border-bottom:1px solid #ddd;
  display:flex;
  justify-content:center;
  gap:5px;
}

.login-form {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  text-align: center;
}

.login-form input {
  padding: 10px;
  margin: 10px;
  width: 80%;
  border-radius: 6px;
  border: 1px solid #ccc;
}

.login-form button {
  background: #000;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

#loginError {
  color: red;
  display: none;
}

/* Blur effect only for chat input/button */
.blur-chat {
  filter: blur(5px);
  pointer-events: none;
}

.menu-dropdown {
  position: absolute;
  top: 40px;
  right: 10px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 6px;
  display: none;
  flex-direction: column;
  width: 120px;
  box-shadow: 0px 4px 12px rgba(0,0,0,0.2);
  z-index: 99999;
}

.menu-dropdown div {
  padding: 10px;
  cursor: pointer;
}

.menu-dropdown div:hover {
  background: #f5f5f5;
}

/* ====================== CHAT MESSAGES ====================== */
.chat-message {
  margin: 5px 0;
  padding: 8px 12px;
  border-radius: 12px;
  max-width: 70%;
  word-wrap: break-word;
}

.chat-message.user {  
  background-color: #111;
  color: white;
  margin-left: auto;
  text-align: right;
}


.chat-message.other {
  background-color: #f1f1f1;
  color: black;
  margin-right: auto;
  text-align: left;
  display: inline-block;       /* make bubble shrink to content */
  max-width: 200px;            /* limit width */
  word-wrap: break-word;
}

/* Container for other users to display name above bubble */
.other-message-container {
  margin-bottom: 8px;
}

.other-name {
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 2px;
  color: #333;
  padding-left: 4px;
}

.reply-icon {
  display: inline-block;
  margin-left: 8px;
  cursor: pointer;
  color: #888;
  font-size: 12px;
  transition: 0.2s;
}

.reply-icon:hover {
  color: #000;
}
.chat-input textarea { 
  flex:1; 
  padding:10px; 
  border:none; 
  outline:none; 
  font-family: inherit;
  font-size: 14px;
  line-height: 1.4;
  overflow-y: auto;
}
.reply-preview {
  font-size: 12px;
  color: #555;
  background: rgba(0,0,0,0.05);
  padding: 4px 8px;
  border-left: 3px solid #ccc;
  border-radius: 6px;
  max-width: 220px;
  word-wrap: break-word;
  margin-bottom: 2px;
}

.user-message-wrapper,
.other-message-wrapper {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.user-message-wrapper {
  align-items: flex-end; /* right align user messages and reply previews */
}

.other-message-wrapper {
  align-items: flex-start; /* left align others */
}



</style>

<!-- ====================== CHAT HTML ====================== -->
<div class="chat-toggle" id="chatToggle"><i class="fa-solid fa-comments"></i></div>
<div class="chat-box" id="chatBox">
  <div class="chat-header">
    <span class="header-title">Sen Template</span>
    <i class="fa-solid fa-ellipsis-vertical header-dots"></i>
  </div>
<div class="menu-dropdown" id="menuDropdown">
  <div id="activeUsersBtn">Active (<span id="activeCount">0</span>)</div>
  <div id="logoutBtn">Logout</div>
</div>

<!-- ACTIVE USERS PANEL (INSIDE CHAT UI) -->
<div id="activeUsersOverlay" 
  style="display:none; position:absolute; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(4px);
  z-index:99999; justify-content:center; align-items:center;">
  
  <div id="activeUsersModal" 
    style="background:#fff; width:260px; max-height:350px; border-radius:10px; overflow:hidden;
    box-shadow:0 6px 25px rgba(0,0,0,.35); animation:fadeIn .2s;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; 
      background:#111; color:white; font-weight:600;">
      Active Users
      <button id="closeActiveModal" style="background:none; border:none; color:white; font-size:18px; cursor:pointer;">✖</button>
    </div>

<ul id="activeUsersList" style="
    list-style:none; 
    margin:0; 
    padding:10px; 
    max-height:250px;   /* <-- set max height */
    overflow-y:auto;    /* <-- enable vertical scroll */
"></ul>

  </div>
</div>



  <!-- ===== User Info (image + full name) ===== -->
<div id="secondaryUser" style="
    display:none; 
    align-items:center; 
    gap:8px; 
    margin:10px; 
    padding-bottom:8px; 
    border-bottom:1px solid #ddd;
">
  <img id="secondaryUserImg" src="" alt="User Icon" style="width:30px; height:30px; border-radius:50%; object-fit:cover;" />
  <span id="secondaryUserName" style="font-size:14px; font-weight:500;"></span>
  <span id="secondaryUserDot" style="
      width:10px; height:10px; 
      border-radius:50%; 
      background:green; 
      display:inline-block;
  "></span>
</div>

  <!-- Secondary user container below Sen Template -->
<!-- Secondary user container below Sen Template -->

<div class="datetime-box" style="display:none;">
  <div id="dateToday"></div>
  <div id="timeNow"></div>
</div>

  <div class="login-form" id="loginForm">
    <h3>Login</h3>
    <input type="text" id="employeeID" placeholder="Enter your Employee ID" />
    <button id="loginButton">Login</button>
    <div id="loginError">Invalid Employee ID</div>
  </div>

<!-- Replying message bar -->

<!-- Chat messages container -->
<div class="chat-messages" id="chatMessages"></div>

<!-- Input -->
<div id="replyingTo" style="
    display:none;
    position: relative;       /* make container relative */
    padding:6px 10px;
    background:#f1f1f1;
    border-left:3px solid #000;
    font-size:12px;
    margin:6px;
    border-radius:6px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
">
  Replying to: <span id="replyingToText"></span>
  <button onclick="cancelReply()" style="
      position: absolute;  /* fix position */
      right: 6px;          /* distance from right */
      top: 50%;            /* vertically center */
      transform: translateY(-50%);
      border:none;
      background:none;
      cursor:pointer;
      font-size:12px;
  ">✖</button>
</div>

<div class="chat-input">
  <textarea id="chatInput" placeholder="Type a message..." rows="1" style="resize:none;"></textarea>
  <button id="chatSend">Send</button>
</div>


</div>


<script src="/sen_template/js/drag.js"></script>
<script src="/sen_template/js/login.js"></script>
<script src="/sen_template/js/activeUsers.js"></script>
<script src="/sen_template/js/dateTime.js"></script>
<script src="/sen_template/js/heartbeat.js"></script>
<script>
const ROOT_PATH = "/sen_template"; 
const chatMessages = document.getElementById("chatMessages");
const chatInput = document.getElementById("chatInput");
const chatSend = document.getElementById("chatSend");
let user = null; // logged-in user { employee_id, full_name }
let replyToId = null;

// Set reply
function replyToMessage(messageId, fullName, messageText) {
  replyToId = messageId;
  document.getElementById("replyingToText").textContent = `${fullName}: ${messageText}`;
  document.getElementById("replyingTo").style.display = "block";
  chatInput.focus();
}

// Cancel reply
function cancelReply() {
  replyToId = null;
  document.getElementById("replyingTo").style.display = "none";
  chatInput.placeholder = "Type a message...";
}

// Load messages
async function loadMessages() {
  if (!user) return;

  try {
    const resp = await fetch("/sen_template/process/chat/fetch_messages.php", { credentials: "same-origin" });
    const data = await resp.json();
    if (!Array.isArray(data)) return;

    chatMessages.innerHTML = "";

    data.forEach(msg => {
      const isSelf = msg.full_name === user.full_name;
      const timestamp = new Date(msg.datetime).toLocaleString("en-US", {
        year: "numeric", month: "short", day: "numeric",
        hour: "2-digit", minute: "2-digit", second: "2-digit",
        hour12: true, timeZone: "Asia/Manila"
      });

      // Message wrapper
      const msgWrapper = document.createElement("div");
      msgWrapper.className = isSelf ? "user-message-wrapper" : "other-message-wrapper";
      msgWrapper.style.marginBottom = "8px";

      // Message bubble
      const msgBubble = document.createElement("div");
      msgBubble.className = isSelf ? "chat-message user" : "chat-message other";
      msgBubble.innerHTML = `${msg.message}<div style="font-size:10px;color:${isSelf ? "#ccc" : "#555"};margin-top:2px;">${timestamp}</div>`;
      
      // Assign a unique ID to each message bubble
      const msgId = msg.id || `msg-${Math.random()}`;
      msgBubble.id = "msg-" + msgId;

      // Reply preview outside bubble
      if (msg.reply_to_id && msg.reply_message) {
        const replyDiv = document.createElement("div");
        replyDiv.className = "reply-preview";
        replyDiv.innerHTML = `<strong>${msg.reply_full_name || "Unknown"}:</strong> ${msg.reply_message}`;
        replyDiv.style.textAlign = isSelf ? "right" : "left";
        replyDiv.style.opacity = "0.7";
        replyDiv.style.marginLeft = isSelf ? "auto" : "0";
        replyDiv.style.marginRight = isSelf ? "0" : "auto";
        replyDiv.style.maxWidth = "220px";
        replyDiv.style.wordWrap = "break-word";
        replyDiv.style.padding = "4px 8px";
        replyDiv.style.borderLeft = "3px solid #ccc";
        replyDiv.style.borderRadius = "6px";
        replyDiv.style.marginBottom = "2px";
        replyDiv.style.cursor = "pointer";

        // Scroll and blink original message when clicking the reply preview
        replyDiv.addEventListener("click", () => {
          const targetMsg = document.getElementById("msg-" + msg.reply_to_id);
          if (!targetMsg) return;
          targetMsg.scrollIntoView({ behavior: "smooth", block: "center" });

          // Blink blue 2 times
          let blinkCount = 0;
          const originalBg = targetMsg.style.backgroundColor;
          const blinkInterval = setInterval(() => {
            targetMsg.style.backgroundColor = blinkCount % 2 === 0 ? "#cce5ff" : originalBg;
            blinkCount++;
            if (blinkCount > 3) clearInterval(blinkInterval);
          }, 300);
        });

        msgWrapper.appendChild(replyDiv);
      }

      msgWrapper.appendChild(msgBubble);

      // Reply icon
      const replyIcon = document.createElement("i");
      replyIcon.className = "fa-solid fa-reply reply-icon";
      replyIcon.title = "Reply";
      replyIcon.style.cursor = "pointer";
      replyIcon.style.marginLeft = "6px";
      replyIcon.addEventListener("click", () => {
        replyToMessage(msg.id || "", msg.full_name, msg.message);
      });
      msgBubble.appendChild(replyIcon);

      // If other user, show name above bubble
      if (!isSelf) {
        const nameDiv = document.createElement("div");
        nameDiv.className = "other-name";
        nameDiv.textContent = msg.full_name;
        msgWrapper.insertBefore(nameDiv, msgWrapper.firstChild);
      }

      chatMessages.appendChild(msgWrapper);
    });

    chatMessages.scrollTop = chatMessages.scrollHeight;

  } catch (err) {
    console.error("Failed to load messages:", err);
  }
}

// Send message
chatSend.onclick = async () => {
  const message = chatInput.value.trim();
  if (!message) return;

  try {
    const resp = await fetch("/sen_template/process/chat/send_messages.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message, reply_to_id: replyToId }),
      credentials: "same-origin"
    });
    const data = await resp.json();
    if (data.status === "success") {
      chatInput.value = "";
      cancelReply();
      loadMessages();
    } else {
      alert("Send failed: " + (data.message || "Unknown error"));
    }
  } catch (err) {
    console.error("Send failed:", err);
    alert("Send failed: error");
  }
};

// Enter key to send
chatInput.addEventListener("keydown", e => {
  if (e.key === "Enter") {
    if (e.shiftKey) {
      const start = chatInput.selectionStart;
      const end = chatInput.selectionEnd;
      chatInput.value = chatInput.value.substring(0, start) + "\n" + chatInput.value.substring(end);
      chatInput.selectionStart = chatInput.selectionEnd = start + 1;
      e.preventDefault();
    } else {
      e.preventDefault();
      chatSend.click();
    }
  }
});

// Auto refresh every 30s
setInterval(loadMessages, 30000);
window.addEventListener("DOMContentLoaded", loadMessages);
</script>

