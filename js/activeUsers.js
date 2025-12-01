document.addEventListener("DOMContentLoaded", () => {
    const headerDots = document.querySelector(".chat-header .header-dots");
    const menuDropdown = document.getElementById("menuDropdown");
    const activeBtn = document.getElementById("activeUsersBtn");
    const activeOverlay = document.getElementById("activeUsersOverlay");
    const activeList = document.getElementById("activeUsersList");
    const activeCount = document.getElementById("activeCount");
    const closeActiveModal = document.getElementById("closeActiveModal");

    // Toggle menu dropdown
    headerDots.addEventListener("click", () => {
        menuDropdown.style.display = menuDropdown.style.display === "flex" ? "none" : "flex";
    });

    async function fetchActiveUsers() {
        try {
            const resp = await fetch(`${ROOT_PATH}/process/chat/fetch_active_users.php`);
            const users = await resp.json();

            activeList.innerHTML = "";
            const activeUsers = users.filter(u => u.status === "Active");
            activeCount.textContent = activeUsers.length;

            users.forEach(u => {
                const color = u.status === "Active" ? "green" : "gray";
                const text = u.status === "Active" ? "" : ` (${u.inactive})`;

                const li = document.createElement("li");
                li.style.display = "flex";
                li.style.alignItems = "center";
                li.style.gap = "8px";
                li.style.padding = "6px 0";
                li.style.fontSize = "14px";

                li.innerHTML = `<span style="width:10px; height:10px; border-radius:50%; background:${color};"></span>
                                <b>${u.full_name}</b><span style="opacity:.65">${text}</span>`;
                activeList.appendChild(li);
            });
        } catch (err) {
            console.log("Fetch error:", err);
        }
    }

    // Load active users instantly and auto-refresh every 30 seconds
    fetchActiveUsers();
    setInterval(fetchActiveUsers, 30000);

    // Open modal without needing to fetch again
    activeBtn.onclick = () => {
        activeOverlay.style.display = "flex";
        document.getElementById("chatMessages").style.filter = "blur(4px)";
        document.querySelector(".chat-input").style.filter = "blur(4px)";
    };

    // Close modal
    closeActiveModal.onclick = () => {
        activeOverlay.style.display = "none";
        document.getElementById("chatMessages").style.filter = "none";
        document.querySelector(".chat-input").style.filter = "none";
    };
});
