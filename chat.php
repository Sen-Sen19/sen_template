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


.chat-message.other { /* Messages from others */
  background-color: #f1f1f1;
  color: black;
  margin-right: auto;
  text-align: left;
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

  <div class="chat-messages" id="chatMessages"></div>
  <div class="chat-input">
    <input type="text" id="chatInput" placeholder="Type a message...">
    <button id="chatSend">Send</button>
  </div>
</div>



<script src="/sen_template/js/login.js"></script>
<script src="/sen_template/js/activeUsers.js"></script>
<script src="/sen_template/js/dateTime.js"></script>
<script>
const ROOT_PATH = "/sen_template"; 

// ====================== DOM ELEMENTS ======================
const bubble = document.getElementById("chatToggle");
const box = document.getElementById("chatBox");
const chatMessages = document.getElementById("chatMessages");
const chatInput = document.getElementById("chatInput");
const chatSend = document.getElementById("chatSend");
const headerDots = document.querySelector(".chat-header .header-dots");
const menuDropdown = document.getElementById("menuDropdown");

let dragging = false, offsetX = 0, offsetY = 0, lastX = 0, lastY = 0, side = "right";
let user = null; // logged-in user object { employee_id, full_name }

// ====================== DRAGGING ======================
bubble.style.bottom="25px";
bubble.style.right="25px";
lastX = bubble.getBoundingClientRect().left;
lastY = bubble.getBoundingClientRect().top;

bubble.addEventListener("mousedown", e => {
  dragging=true;
  offsetX = e.clientX - bubble.getBoundingClientRect().left;
  offsetY = e.clientY - bubble.getBoundingClientRect().top;
  bubble.style.transition = "none";
  box.style.transition = "none";
});
document.addEventListener("mousemove", e => {
  if(!dragging) return;
  lastX = Math.max(0, Math.min(e.clientX-offsetX, window.innerWidth-bubble.offsetWidth));
  lastY = Math.max(0, Math.min(e.clientY-offsetY, window.innerHeight-bubble.offsetHeight));
  bubble.style.left = lastX+"px";
  bubble.style.top = lastY+"px";
  updateChatPosition();
});
document.addEventListener("mouseup", () => {
  if(!dragging) return;
  dragging=false;
  const bubbleCenterX = lastX + bubble.offsetWidth/2;
  side = bubbleCenterX < window.innerWidth/2 ? "left":"right";
  bubble.style.left = side==="left"?"12px":"auto";
  bubble.style.right = side==="right"?"12px":"auto";
  updateChatPosition();
});
bubble.onclick = () => {
  const isOpen = box.style.display === "flex";
  box.style.display = isOpen ? "none" : "flex";
  updateChatPosition();

  if (!isOpen) {
    // Load messages immediately when chat opens
    loadMessages();
  }
};

function updateChatPosition(){
  const r = bubble.getBoundingClientRect();
  box.style.left = side==="left"? r.left+"px":"auto";
  box.style.right = side==="right"? (window.innerWidth-r.right)+"px":"auto";
  box.style.top = r.top + r.height/2 < window.innerHeight/2 ? (r.bottom+10)+"px":"auto";
  box.style.bottom = r.top + r.height/2 >= window.innerHeight/2 ? (window.innerHeight-r.top+10)+"px":"auto";
}

// ====================== LOAD MESSAGES ======================
async function loadMessages() {
  if (!user) return;

  try {
    const resp = await fetch(`${ROOT_PATH}/process/chat/fetch_messages.php`, { credentials: "same-origin" });
    const data = await resp.json();

    if (!Array.isArray(data)) {
      console.error("Messages not array:", data);
      return;
    }

    chatMessages.innerHTML = "";

data.forEach(msg => {
  // Compare full name to the secondary user display name
  const isSelf = msg.full_name === document.getElementById("secondaryUserName").textContent;

  const timestamp = new Date(msg.datetime).toLocaleString('en-US', {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: true,
    timeZone: "Asia/Manila"
  });

  if (isSelf) {
    chatMessages.innerHTML += `
      <div class="chat-message user">
        ${msg.message}
        <div style="font-size:10px; color:#ccc; margin-top:2px;">${timestamp}</div>
      </div>
    `;
  } else {
    chatMessages.innerHTML += `
      <div class="other-message-container">
        <div class="other-name">${msg.full_name || "Unknown"}</div>
        <div class="chat-message other">
          ${msg.message}
          <div style="font-size:10px; color:#555; margin-top:2px;">${timestamp}</div>
        </div>
      </div>
    `;
  }
});

    // Scroll to bottom automatically
    chatMessages.scrollTop = chatMessages.scrollHeight;

  } catch (err) {
    console.error("Failed to load messages:", err);
  }
}

// ====================== REFRESH MESSAGES ======================
setInterval(loadMessages, 30000);
window.addEventListener("DOMContentLoaded", loadMessages);

// ====================== SEND MESSAGE ======================
chatSend.onclick = async () => {
  const message = chatInput.value.trim();
  if (!message) return;

  try {
    const response = await fetch(`${ROOT_PATH}/process/chat/send_messages.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message }),
      credentials: "same-origin"
    });

    const data = await response.json();

    if (data.status === "success") {
      chatInput.value = "";
      loadMessages(); // reload messages immediately
    } else {
      alert("Send failed: " + (data.message || "Unknown error"));
    }
  } catch (err) {
    console.error("Send failed:", err);
    alert("Send failed: error");
  }
};


// Allow Enter key to send message
chatInput.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault(); // prevent new line if input is multiline
    chatSend.click();   // trigger the send button click
  }
});

// ====================== HEARTBEAT ======================
async function sendHeartbeat() {
  if (!user) return;
  const now = new Date();
  const optionsDate = { year:"numeric", month:"long", day:"numeric", timeZone:"Asia/Manila" };
  const optionsTime = { hour:"2-digit", minute:"2-digit", second:"2-digit", hour12:true, timeZone:"Asia/Manila" };

  try {
    await fetch(`${ROOT_PATH}/process/chat/heartbeat.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        employee_id: user.employee_id,
        date_today: now.toLocaleDateString("en-US", optionsDate),
        time_now: now.toLocaleTimeString("en-US", optionsTime)
      })
    });
  } catch(err) {
    console.log("Heartbeat failed", err);
  }
}
setInterval(sendHeartbeat, 30000);
window.addEventListener("DOMContentLoaded", () => { setTimeout(sendHeartbeat, 500); });

// ====================== MENU CLOSE ======================
document.addEventListener("click", (e) => {
  const isClickInside = menuDropdown.contains(e.target) || headerDots.contains(e.target);
  if (!isClickInside) menuDropdown.style.display = "none";
});
</script>

