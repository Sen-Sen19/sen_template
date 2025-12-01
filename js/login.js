const loginForm = document.getElementById("loginForm");
const loginButton = document.getElementById("loginButton");
const loginError = document.getElementById("loginError");

const userInfoDiv = document.getElementById("userInfo");
const userImg = document.getElementById("userImg");
const userFullName = document.getElementById("userFullName");

const secondaryDiv = document.getElementById("secondaryUser");
const secondaryImg = document.getElementById("secondaryUserImg");
const secondaryName = document.getElementById("secondaryUserName");

window.addEventListener("DOMContentLoaded", async () => {
  try {
    const resp = await fetch(`${ROOT_PATH}/process/chat/check_session.php`);
    const data = await resp.json();
    if (data.status === "active") {
      user = data.user;
      loginForm.style.display = "none";
      removeChatBlur();
      await updateUserInfo();
      secondaryImg.src = user.img || `${ROOT_PATH}/dist/img/office-man.png`;
      secondaryName.textContent = user.full_name;
      secondaryDiv.style.display = "flex";
    } else {
      disableChatboxDuringLogin();
    }
  } catch(e){ console.log("Session check error", e); }
});

loginButton.onclick = async () => {
  const employeeID = document.getElementById("employeeID").value.trim();
  if(!employeeID){ 
    loginError.textContent="Employee ID is required!"; 
    loginError.style.display="block"; 
    return;
  }
  loginError.style.display="none";

  try {
    const formData = new FormData();
    formData.append("idno", employeeID);
    const resp = await fetch(`${ROOT_PATH}/process/chat/check_login.php`, {method:"POST", body:formData});
    const data = await resp.json();

    if(data.status==="success"){ 
      user = data; 
      loginForm.style.display = "none"; 
      removeChatBlur(); 
      await updateUserInfo();

      secondaryImg.src = user.img || `${ROOT_PATH}/dist/img/office-man.png`;
      secondaryName.textContent = user.full_name;
      secondaryDiv.style.display = "flex";
    } else { 
      loginError.textContent = data.status==="notfound"?"Employee ID not found!":"An error occurred!"; 
      loginError.style.display="block"; 
    }
  } catch(err){ 
    loginError.textContent="Failed to communicate with server!"; 
    loginError.style.display="block";
  }
};

document.getElementById("logoutBtn").onclick = async () => {
  try {
    const resp = await fetch(`${ROOT_PATH}/process/chat/logout.php`, { method: "POST" });
    const data = await resp.json();
    if (data.status === "logged_out") {
      user = null;
      loginForm.style.display = "flex";
      chatMessages.innerHTML = "";
      secondaryDiv.style.display = "none"; 
      disableChatboxDuringLogin();
    }
  } catch (e) { console.log("Logout JS error", e); alert("Logout failed"); }
};

// ====================== HELPERS ======================
function removeChatBlur(){
  chatInput.classList.remove("blur-chat");
  chatSend.classList.remove("blur-chat");
}
function disableChatboxDuringLogin(){
  chatInput.classList.add("blur-chat");
  chatSend.classList.add("blur-chat");
}
async function updateUserInfo(){
  if(!user || !user.username) return;
  try{
    const resp = await fetch(`${ROOT_PATH}/process/fetch_user_image/fetch_user.php?username=${encodeURIComponent(user.username)}`);
    const data = await resp.json();
    if(data.success){
      userImg.src = user.img || `${ROOT_PATH}/dist/img/office-man.png`; 
      userFullName.textContent = user.full_name || "";
      userInfoDiv.style.display = "block";
    }
  } catch(err){ console.log("Failed to load user image", err); }
}
