// ====================== CHAT BUBBLE & DRAGGING ======================
const bubble = document.getElementById("chatToggle");
const box = document.getElementById("chatBox");

let dragging = false, offsetX = 0, offsetY = 0, lastX = 0, lastY = 0, side = "right";

// Initial position
bubble.style.bottom = "25px";
bubble.style.right = "25px";
lastX = bubble.getBoundingClientRect().left;
lastY = bubble.getBoundingClientRect().top;

// Drag start
bubble.addEventListener("mousedown", e => {
  dragging = true;
  offsetX = e.clientX - bubble.getBoundingClientRect().left;
  offsetY = e.clientY - bubble.getBoundingClientRect().top;
  bubble.style.transition = "none";
  box.style.transition = "none";
});
// Close chat when clicking outside
document.addEventListener("click", (e) => {
  const isClickInsideBubble = bubble.contains(e.target);
  const isClickInsideBox = box.contains(e.target);

  if (!isClickInsideBubble && !isClickInsideBox) {
    box.style.display = "none";
  }
});


// Dragging
document.addEventListener("mousemove", e => {
  if (!dragging) return;
  lastX = Math.max(0, Math.min(e.clientX - offsetX, window.innerWidth - bubble.offsetWidth));
  lastY = Math.max(0, Math.min(e.clientY - offsetY, window.innerHeight - bubble.offsetHeight));
  bubble.style.left = lastX + "px";
  bubble.style.top = lastY + "px";
  updateChatPosition();
});

// Drag end
document.addEventListener("mouseup", () => {
  if (!dragging) return;
  dragging = false;
  const bubbleCenterX = lastX + bubble.offsetWidth / 2;
  side = bubbleCenterX < window.innerWidth / 2 ? "left" : "right";
  bubble.style.left = side === "left" ? "12px" : "auto";
  bubble.style.right = side === "right" ? "12px" : "auto";
  updateChatPosition();
});

// Toggle chat box
bubble.onclick = () => {
  const isOpen = box.style.display === "flex";
  box.style.display = isOpen ? "none" : "flex";
  updateChatPosition();
  if (!isOpen) loadMessages(); // load messages on open
};

function updateChatPosition() {
  const r = bubble.getBoundingClientRect();
  box.style.left = side === "left" ? r.left + "px" : "auto";
  box.style.right = side === "right" ? (window.innerWidth - r.right) + "px" : "auto";
  box.style.top = r.top + r.height / 2 < window.innerHeight / 2 ? (r.bottom + 10) + "px" : "auto";
  box.style.bottom = r.top + r.height / 2 >= window.innerHeight / 2 ? (window.innerHeight - r.top + 10) + "px" : "auto";
}
