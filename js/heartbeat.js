// ====================== HEARTBEAT ======================
async function sendHeartbeat() {
  if (!user) return;
  const now = new Date();
  try {
    await fetch(`${ROOT_PATH}/process/chat/heartbeat.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        employee_id: user.employee_id,
        date_today: now.toLocaleDateString("en-US", { year:"numeric", month:"long", day:"numeric", timeZone:"Asia/Manila" }),
        time_now: now.toLocaleTimeString("en-US", { hour:"2-digit", minute:"2-digit", second:"2-digit", hour12:true, timeZone:"Asia/Manila" })
      })
    });
  } catch(err) {
    console.log("Heartbeat failed", err);
  }
}
setInterval(sendHeartbeat, 30000);
window.addEventListener("DOMContentLoaded", () => { setTimeout(sendHeartbeat, 500); });

// ====================== MENU CLOSE ======================
const headerDots = document.querySelector(".chat-header .header-dots");
const menuDropdown = document.getElementById("menuDropdown");

document.addEventListener("click", e => {
  const isClickInside = menuDropdown.contains(e.target) || headerDots.contains(e.target);
  if (!isClickInside) menuDropdown.style.display = "none";
});
