<link rel="stylesheet" href="/sen_template/css/all.min.css">
<style>
/* ============================================================
   UNIVERSAL IMPROVED RESPONSIVE + MOBILE KEYBOARD SAFE CSS
   ============================================================ */

/* ====================== CHAT BUBBLE ====================== */
.chat-toggle {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 60px;
  height: 60px;
  background: #111;
  color: white;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  font-size: 26px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.3);
  transition: .25s;
  z-index: 9999;
}

.chat-toggle:hover {
  transform: scale(1.08);
}

/* ========= CHAT BOX (keyboard safe) ========= */
.chat-box {
  position: fixed;
  bottom: 80px;
  right: 20px;
  width: 420px;
  height: 520px;

  max-width: 95vw;
  max-height: calc(100vh - 90px) !important;

  background: white;
  border-radius: 14px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  display: none;
  flex-direction: column;
  overflow: hidden;
  z-index: 9999;

  overscroll-behavior: contain;
  transition: all 0.3s ease;
}

@supports (height: 100dvh) {
  .chat-box {
    max-height: calc(100dvh - 90px) !important;
  }
}

.chat-header {
  background: #000;
  padding: 12px;
  color: white;
  font-weight: 600;
  font-size: 16px;
  position: relative;
  text-align: center;
  flex-shrink: 0;
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
  display: flex;
  flex-direction: column;
  min-height: 0; /* FIX SCROLL BUG */
}

/* INPUT AREA ALWAYS FIXED */
.chat-input {
  display: flex;
  border-top: 1px solid #ddd;
  padding: 4px;
  gap: 4px;
  background: #fff;
  flex-shrink: 0;
}

.chat-input button {
  border: none;
  background: none;
  cursor: pointer;
  padding: 6px;
}

.chat-input textarea {
  flex: 1;
  padding: 10px;
  border: none;
  outline: none;
  font-size: 14px;
  resize: none;
  border-radius: 6px;
  background: #f5f5f5;
  overflow-y: auto;
  max-height: 120px;
}

/* ====================== LOGIN FORM ====================== */
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
  background: #111;
  color: white;
  margin-left: auto;
  text-align: right;
}

.chat-message.other {
  background: #f1f1f1;
  color: black;
  margin-right: auto;
  text-align: left;
  display: inline-block;
  max-width: 200px;
}

/* USER NAME ABOVE BUBBLE */
.other-message-wrapper,
.user-message-wrapper {
  display: flex;
  flex-direction: column;
}

.other-message-wrapper {
  align-items: flex-start;
}

.user-message-wrapper {
  align-items: flex-end;
}

/* REPLY PREVIEW */
.reply-preview {
  background: rgba(255,221,109,0.29);
  padding: 4px 8px;
  border-left: 3px solid #ccc;
  border-radius: 6px;
  font-size: 12px;
  max-width: 220px;
  word-wrap: break-word;
  margin-bottom: 2px;
}

/* ====================== RESPONSIVE DESIGN ====================== */
/* ===== Small Phones → Centered Fullscreen Chat ===== */
@media (max-width: 600px) {

  .chat-box {
    position: fixed;
    inset: 0 !important;             /* Fullscreen bounding */
    width: 100vw !important;
    height: 100dvh !important;       /* TRUE mobile fullscreen */

    max-width: 100vw !important;
    max-height: 100dvh !important;

    border-radius: 0 !important;
    transform: none !important;      /* Remove center transform */
    left: 0 !important;
    top: 0 !important;
    bottom: 0 !important;
    right: 0 !important;
  }

}
  .chat-toggle {
    width: 55px;
    height: 55px;
    font-size: 22px;
    bottom: 15px !important;
    right: 15px !important;
  }

  .chat-header {
    font-size: 17px;
    padding: 14px;
  }

  .chat-messages {
    padding: 12px;
    font-size: 14px;
  }

  .chat-input textarea {
    font-size: 14px;
    max-height: 120px;
  }

  #activeUsersModal {
    width: 90vw !important;
    max-height: 70vh !important;
  }
}

/* VERY SMALL DEVICES */
@media (max-width: 400px) {
  .chat-header { font-size: 13px; }
  .chat-toggle { width: 50px; height: 50px; font-size: 20px; }
  .chat-message { font-size: 12px; }
  .chat-input textarea { font-size: 12px; }
}

/* DESKTOP */
@media (min-width: 1200px) {
  .chat-box {
    width: 420px !important;
    height: 520px !important;
  }
}

/* ====================== EMOJI PICKER ====================== */
emoji-picker {
  position: absolute;
  bottom: 50px;
  left: 10px;
  display: none;
  max-height: 400px;
  overflow-y: auto;
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.2);
  z-index: 999;
}

/* ============================================================
   END OF FULL RESPONSIVE CSS
   ============================================================ */
/* Spinner animation */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Bubble loader animation */
.sending-bubble {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
  margin-bottom: 6px;
  animation: slideIn 0.3s ease-out;
}

.sending-bubble div.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.5);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

/* Slide in effect */
@keyframes slideIn {
  0% { opacity: 0; transform: translateX(20px); }
  100% { opacity: 1; transform: translateX(0); }
}
.mention-box {
    position: absolute;
    bottom: 60px;
    left: 10px;
    width: 260px;
    max-height: 220px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 8px;
    z-index: 9999;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

.mention-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    cursor: pointer;
}

.mention-item:hover,
.mention-item.active {
    background: #e6f2ff;
}

.mention-item img {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
}

.chat-message b.mention {
    color: #007bff;
    font-weight: 600;
}
.chat-message strong,
.chat-message b {
    font-weight: 700 !important;
    color: #007bff;
}
strong.mention {
    font-weight: 700 !important;
}

</style>

<!-- ====================== CHAT HTML ====================== -->
<div class="chat-toggle" id="chatToggle"><i class="fa-solid fa-comments"></i></div>
<div class="chat-box" id="chatBox">
<div class="chat-header">
    <span class="header-title">Message</span>

    <div style="
        position:absolute; 
        right:12px; 
        top:50%; 
        transform:translateY(-50%); 
        display:flex; 
        gap:14px;        /* <-- increased gap here */
        align-items:center;
    ">
<i class="fa-solid fa-ellipsis-vertical header-dots" style="margin-right:20px;"></i>
        <i class="fa-solid fa-xmark" id="closeChatHeader" style="cursor:pointer;"></i>
    </div>
</div>


  <div class="menu-dropdown" id="menuDropdown">

    <div id="activeUsersBtn">Active (<span id="activeCount">0</span>)</div>
    <div id="logoutBtn">Logout</div>
  </div>


  <!-- ACTIVE USERS PANEL (INSIDE CHAT UI) -->
  <div id="activeUsersOverlay" style="display:none; position:absolute; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(4px);
  z-index:99999; justify-content:center; align-items:center;">

    <div id="activeUsersModal" style="background:#fff; width:260px; max-height:350px; border-radius:10px; overflow:hidden;
    box-shadow:0 6px 25px rgba(0,0,0,.35); animation:fadeIn .2s;">

      <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; 
      background:#111; color:white; font-weight:600;">
        Active Users
        <button id="closeActiveModal"
          style="background:none; border:none; color:white; font-size:18px; cursor:pointer;">✖</button>
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
    <img id="secondaryUserImg" src="" alt="User Icon"
      style="width:30px; height:30px; border-radius:50%; object-fit:cover;" />
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
<!-- Editing message bar -->
<div id="editingBar" style="
    display:none;
    position: relative;
    padding:6px 10px;
    background:#ffeeba;
    border-left:3px solid #f0ad4e;
    font-size:12px;
    margin:6px;
    border-radius:6px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
">
  <span id="editingText">Editing message...</span>
  <button onclick="cancelEdit()" style="
      position: absolute;
      right: 6px;
      top: 50%;
      transform: translateY(-50%);
      border:none;
      background:none;
      cursor:pointer;
      font-size:12px;
  ">✖</button>
</div>

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
   <span id="replyingToText"></span>
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
<div id="sendingLoaderContainer" style="display:none; margin:6px 10px;"></div>



  <div class="chat-input">
    <button id="attachBtn" title="Attachment">
      <i class="fa-solid fa-paperclip"></i>
    </button>
    <input type="file" id="fileInput" multiple style="display:none;" />
<div id="mentionBox" class="mention-box" style="display:none;"></div>

    <textarea id="chatInput" placeholder="Type a message..." rows="1" style="resize:none;"></textarea>
    <div style="position: relative; display: inline-block;">
      <button id="emojiBtn" title="Emoji">
        <i class="fa-regular fa-face-smile"></i>
      </button>
      <emoji-picker id="emojiPicker"
        style="display: none; position: absolute; bottom: 100%; left: 0; z‑index: 9999;"></emoji-picker>
    </div>

    <button id="chatSend" title="Send">
      <i class="fa-solid fa-paper-plane"></i>
    </button>
  </div>


</div>
<script src="/sen_template/js/react.js"></script>
<script src="/sen_template/js/reply.js"></script>
<script src="/sen_template/js/drag.js"></script>
<script src="/sen_template/js/login.js"></script>
<script src="/sen_template/js/activeUsers.js"></script>
<script src="/sen_template/js/dateTime.js"></script>
<script src="/sen_template/js/heartbeat.js"></script>
<script src="/sen_template/js/message.js"></script>
<script>
  let usersList = [];
let mentionIndex = -1;

async function loadUsers() {
    const res = await fetch("/sen_template/process/account_view.php", {
        credentials: "same-origin"
    });
    usersList = await res.json();
}

loadUsers();

</script>
<script>
document.getElementById("closeChatHeader").onclick = function () {
    document.getElementById("chatBox").style.display = "none";
};

  const ROOT_PATH = "/sen_template";
  const chatMessages = document.getElementById("chatMessages");
  const chatInput = document.getElementById("chatInput");
  const chatSend = document.getElementById("chatSend");
  let user = null; 
let activeBubble = null; 
async function loadMessages() {
    if (!user) return;
    try {
        const resp = await fetch("/sen_template/process/chat/fetch_messages.php", { credentials: "same-origin" });
        let data = await resp.json();
        if (!Array.isArray(data)) return;

        // Reverse to show oldest first at top
        data = data.reverse();

        chatMessages.innerHTML = "";

        data.forEach(msg => {
            const isSelf = msg.full_name === user.full_name;

            const msgWrapper = document.createElement("div");
            msgWrapper.className = isSelf ? "user-message-wrapper" : "other-message-wrapper";
            msgWrapper.style.marginBottom = "8px";
            msgWrapper.style.position = "relative";

            const msgBubble = document.createElement("div");
            msgBubble.className = isSelf ? "chat-message user" : "chat-message other";
            msgBubble.style.position = "relative";
            msgBubble.style.cursor = "pointer";
            msgBubble.id = "msg-" + (msg.id || `msg-${Math.random()}`);

            // --- Message text ---
            const textDiv = document.createElement("div");
       textDiv.innerHTML = msg.message.replace(
    /@([a-zA-Z]+(?:\s[a-zA-Z]+)*)/g,
    '<strong class="mention">@$1</strong>'
);


            msgBubble.appendChild(textDiv);

            if (msg.datetime) {
                const timeDiv = document.createElement("div");
                const dt = new Date(msg.datetime);
                timeDiv.textContent = dt.toLocaleString();
                timeDiv.style.fontSize = "10px";
                timeDiv.style.color = "#8d8d8dff";
                timeDiv.style.marginTop = "2px";
                timeDiv.style.textAlign = isSelf ? "right" : "left";
                msgBubble.appendChild(timeDiv);
            }
            // --- Attachments ---
          // --- Attachments (lazy load) ---
if (Array.isArray(msg.attachments) && msg.attachments.length > 0) {
    const attachContainer = document.createElement("div");
    attachContainer.style.marginTop = "6px";

    msg.attachments.forEach(att => {
        const type = att.type.split('/')[0];

        if (type === "image" || type === "video") {
            const placeholder = document.createElement("button");
            placeholder.textContent = type === "image" ? `View Image (${att.name})` : `Play Video (${att.name})`;
            placeholder.style.display = "block";
            placeholder.style.color = "#00aaff";
            placeholder.style.cursor = "pointer";
            placeholder.style.border = "1px solid #00aaff";
            placeholder.style.borderRadius = "6px";
            placeholder.style.background = "transparent";
            placeholder.style.padding = "4px 8px";
            placeholder.style.marginBottom = "4px";

            placeholder.addEventListener("click", () => {
                if (type === "image") {
                    const img = document.createElement("img");
                    img.src = `data:${att.type};base64,${att.content}`; // load on click
                    img.alt = att.name;
                    img.style.maxWidth = "200px";
                    img.style.borderRadius = "6px";
                    placeholder.replaceWith(img);
                } else if (type === "video") {
                    const vid = document.createElement("video");
                    vid.src = att.file_url || att.content; // prefer file_url if available
                    vid.controls = true;
                    vid.style.maxWidth = "220px";
                    placeholder.replaceWith(vid);
                }
            });

            attachContainer.appendChild(placeholder);
        } else {
            // other files (PDF, doc, etc.) load immediately as link
            const a = document.createElement("a");
            a.href = `data:${att.type};base64,${att.content}`;
            a.download = att.name;
            a.textContent = att.name;
            a.style.display = "block";
            attachContainer.appendChild(a);
        }
    });

    msgBubble.appendChild(attachContainer);
}

    // --- Edited Badge ---
     if (msg.message_history) {
                const editedBadge = document.createElement("span");
                editedBadge.textContent = "Edited";
                editedBadge.style.fontSize = "10px";
                editedBadge.style.color = "#007bff";
                editedBadge.style.pointerEvents = "none";
                editedBadge.style.background = "rgba(255,255,255,0.2)";
                editedBadge.style.padding = "1px 4px";
                editedBadge.style.borderRadius = "4px";
                editedBadge.style.display = "inline-block";
                editedBadge.style.marginTop = "4px";
                editedBadge.style.marginBottom = "4px";
                msgBubble.appendChild(editedBadge);
            }

if (msg.attachment) {
    const ext = msg.attachment_type?.split('/')[0];

    if (ext === "image" || ext === "video") {
        const placeholder = document.createElement("button");
        placeholder.textContent = ext === "image" ? `View Image (${msg.attachment_name})` : `Play Video (${msg.attachment_name})`;
        Object.assign(placeholder.style, {
            display: "block",
            color: "#00aaff",
            cursor: "pointer",
            border: "1px solid #00aaff",
            borderRadius: "6px",
            background: "transparent",
            padding: "4px 8px",
            marginTop: "6px",
            wordBreak: "break-word",
            maxWidth: "100%" // ensures it never overflows the bubble
        });

        placeholder.addEventListener("click", () => {
            if (ext === "image") {
                const img = document.createElement("img");
                img.src = msg.attachment; // load on click
                img.alt = msg.attachment_name;
                Object.assign(img.style, {
                    maxWidth: "100%",  // fit bubble width
                    borderRadius: "6px",
                    display: "block",
                    marginTop: "6px"
                });
                placeholder.replaceWith(img);
            } else {
                const vid = document.createElement("video");
                vid.src = msg.attachment;
                vid.controls = true;
                Object.assign(vid.style, {
                    maxWidth: "100%",
                    display: "block",
                    marginTop: "6px",
                    borderRadius: "6px"
                });
                placeholder.replaceWith(vid);
            }
        });

        msgBubble.appendChild(placeholder);
    } else {
        const a = document.createElement("a");
        a.href = msg.attachment;
        a.download = msg.attachment_name;
        a.textContent = msg.attachment_name;
        a.style.display = "block";
        a.style.marginTop = "6px";
        msgBubble.appendChild(a);
    }
}



    // --- Reply Preview ---
    if (msg.reply_to_id && msg.reply_message) {
        const replyDiv = document.createElement("div");
        replyDiv.className = "reply-preview";
        replyDiv.innerHTML = `<strong>${msg.reply_full_name || "Unknown"}:</strong> ${msg.reply_message}`;
        Object.assign(replyDiv.style, {
            textAlign: isSelf ? "right" : "left",
            opacity: "0.7",
            margin: "2px 0",
            maxWidth: "220px",
            wordWrap: "break-word",
            padding: "4px 8px",
            borderLeft: "3px solid #ccc",
            borderRadius: "6px",
            cursor: "pointer"
        });
        replyDiv.addEventListener("click", () => {
            const targetMsg = document.getElementById("msg-" + msg.reply_to_id);
            if (!targetMsg) return;
            targetMsg.scrollIntoView({ behavior: "smooth", block: "center" });
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

const reactionsDiv = document.createElement("div");
reactionsDiv.className = "reactions";
Object.assign(reactionsDiv.style, {
    display: "flex",
    gap: "4px",
    justifyContent: isSelf ? "flex-end" : "flex-start",
    alignItems: "center",
    cursor: "pointer",
    fontSize: "14px",
    padding: "2px 4px",
    borderRadius: "4px",
    background: "transparent"
});

// Combine all reactions in a single text line
if (Array.isArray(msg.reactions) && msg.reactions.length > 0) {
    let summaryText = "";
    msg.reactions.forEach(r => {
        summaryText += `${r.emoji}${r.users.length} `; 
    });

    reactionsDiv.textContent = summaryText.trim();

reactionsDiv.addEventListener("mouseenter", () => {
  const tooltip = document.getElementById("reactionsTooltip"); // always use the global one
  if (!tooltip) return; // safety check

  tooltip.innerHTML = "";
  msg.reactions.forEach(r => {
    r.users.forEach(u => {
      const line = document.createElement("div");
      line.textContent = `${r.emoji} - ${u}`;
      tooltip.appendChild(line);
    });
  });

  tooltip.style.display = "block";

  const tooltipWidth = 250;
  tooltip.style.width = tooltipWidth + "px";
  tooltip.style.maxWidth = tooltipWidth + "px";

  const rect = reactionsDiv.getBoundingClientRect();
  let left = rect.left + rect.width / 2 - tooltipWidth / 2;
  if (left + tooltipWidth > window.innerWidth) left = window.innerWidth - tooltipWidth - 8;
  if (left < 4) left = 4;
  tooltip.style.left = left + "px";

  const spaceBelow = window.innerHeight - rect.bottom;
  const spaceAbove = rect.top;
tooltip.style.top =
  (spaceAbove > tooltip.offsetHeight + 8)
    ? (rect.top - tooltip.offsetHeight - 4) + "px"
    : (rect.bottom + 4) + "px";

});

reactionsDiv.addEventListener("mouseleave", () => {
  const tooltip = document.getElementById("reactionsTooltip");
  if (tooltip) tooltip.style.display = "none";
});

}



    const actionsDiv = document.createElement("div");
    actionsDiv.className = "message-actions";
    Object.assign(actionsDiv.style, { display: "flex", gap: "6px", marginTop: "4px", alignItems: "center" });

    // Reply Icon
    const replyIcon = document.createElement("i");
    replyIcon.className = "fa-solid fa-reply";
    replyIcon.title = "Reply";
    replyIcon.style.cursor = "pointer";
    replyIcon.addEventListener("click", () => window.replyToMessage(msg.id || "", msg.full_name, msg.message));
    actionsDiv.appendChild(replyIcon);

    // Emoji picker & reactions (existing)
    const { reactIcon, emojiPicker } = window.attachEmojiPicker(msgBubble, msg.id, reactionsDiv);
    [reactIcon, emojiPicker, reactionsDiv].forEach(el => el.addEventListener("click", e => e.stopPropagation()));
    actionsDiv.appendChild(reactIcon);
    actionsDiv.appendChild(emojiPicker);
    actionsDiv.appendChild(reactionsDiv);
    msgBubble.appendChild(actionsDiv);

    // --- Edit & Delete Icons ---
    const editIcon = document.createElement("i");
    editIcon.className = "fa-solid fa-pen-to-square";
    editIcon.title = "Edit";
    Object.assign(editIcon.style, {
        position: "absolute",
        left: "-28px",
        top: "50%",
        transform: "translateY(-50%)",
        fontSize: "14px",
        cursor: "pointer",
        display: msg.message_history ? "none" : "none", // hide if message_history exists
        color: "#007bff",
        zIndex: "10"
    });
    editIcon.addEventListener("click", () => window.editMessage(msg.datetime, msg.message, msg.id));

    const deleteIcon = document.createElement("i");
    deleteIcon.className = "fa-solid fa-trash";
    deleteIcon.title = "Delete";
    Object.assign(deleteIcon.style, {
        position: "absolute",
        left: "-50px",
        top: "50%",
        transform: "translateY(-50%)",
        fontSize: "14px",
        cursor: "pointer",
        display: "none",
        color: "#dc3545",
        zIndex: "10"
    });
    deleteIcon.addEventListener("click", () => window.deleteMessage(msg.datetime));

    msgBubble.appendChild(editIcon);
    msgBubble.appendChild(deleteIcon);

// Toggle edit/delete icons
msgBubble.addEventListener("click", e => {
    e.stopPropagation();
    if (!isSelf) return;

    if (activeBubble && activeBubble !== msgBubble) {
        const icons = activeBubble.querySelectorAll("i.fa-pen-to-square, i.fa-trash");
        icons.forEach(i => i.style.display = "none");
    }

    // Only toggle edit icon if message is not edited
    const showEdit = !msg.message_history;
    editIcon.style.display = showEdit ? "inline-block" : "none";

    // Always allow delete icon to toggle
    const visible = deleteIcon.style.display === "inline-block";
    deleteIcon.style.display = visible ? "none" : "inline-block";

    activeBubble = msgBubble;
});


    document.addEventListener("click", () => {
        if (activeBubble) {
            const icons = activeBubble.querySelectorAll("i.fa-pen-to-square, i.fa-trash");
            icons.forEach(i => i.style.display = "none");
            activeBubble = null;
        }
    });

    // Show sender name for other messages
           if (!isSelf) {
                const nameDiv = document.createElement("div");
                nameDiv.className = "other-name";
                nameDiv.textContent = msg.full_name;
                msgWrapper.insertBefore(nameDiv, msgWrapper.firstChild);
            }

          msgWrapper.appendChild(msgBubble);
            chatMessages.appendChild(msgWrapper);
        });

        chatMessages.scrollTop = chatMessages.scrollHeight;

    } catch (err) {
        console.error("Failed to load messages:", err);
    }
}



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
const mentionBox = document.getElementById("mentionBox");

chatInput.addEventListener("input", e => {
    const cursorPos = chatInput.selectionStart;
    const text = chatInput.value.slice(0, cursorPos);
    const match = text.match(/@([\w\s]*)$/);

    if (!match) {
        mentionBox.style.display = "none";
        return;
    }

    const search = match[1].toLowerCase();
    const matches = usersList.filter(u =>
        u.full_name.toLowerCase().includes(search)
    ).slice(0, 6);

    if (matches.length === 0) {
        mentionBox.style.display = "none";
        return;
    }

    mentionBox.innerHTML = "";
    mentionIndex = -1;

    matches.forEach((u, i) => {
        const div = document.createElement("div");
        div.className = "mention-item";
        div.innerHTML = `
            <img src="${u.img || ''}">
            <span>${u.full_name}</span>
        `;

        div.onclick = () => insertMention(u.full_name);
        mentionBox.appendChild(div);
    });

    mentionBox.style.display = "block";
});
function insertMention(name) {
    const cursorPos = chatInput.selectionStart;
    const text = chatInput.value;
    const before = text.slice(0, cursorPos).replace(/@[\w\s]*$/, "");
    const after = text.slice(cursorPos);

    chatInput.value = before + `@${name} ` + after;
    chatInput.focus();
    mentionBox.style.display = "none";
}

</script>





<script type="module">
  import '/sen_template/emoji/node_modules/emoji-picker-element/index.js';
 
  const chatBox = document.getElementById('chatBox');
  const chatInput = document.getElementById("chatInput");
  const emojiBtn = document.getElementById("emojiBtn");

  // Append picker inside chat box
  const picker = document.createElement('emoji-picker');
  chatBox.appendChild(picker);

  // Toggle picker
  emojiBtn.addEventListener('click', e => {
    e.stopPropagation(); // prevent document click
    picker.style.display = picker.style.display === 'block' ? 'none' : 'block';
  });

  // Insert emoji without closing picker
  picker.addEventListener('emoji-click', event => {
    const emoji = event.detail.unicode;
    const start = chatInput.selectionStart;
    const end = chatInput.selectionEnd;
    chatInput.value = chatInput.value.slice(0, start) + emoji + chatInput.value.slice(end);
    chatInput.selectionStart = chatInput.selectionEnd = start + emoji.length;
    chatInput.focus();
    // picker.style.display = 'none';  <-- removed this line
  });

  // Close picker if click outside
  document.addEventListener('click', e => {
    if (!picker.contains(e.target) && e.target !== emojiBtn) {
      picker.style.display = 'none';
    }
  });

  // Prevent picker clicks from closing it
  picker.addEventListener('click', e => e.stopPropagation());
</script>