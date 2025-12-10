// ====================== CHAT BUBBLE (FIXED BOTTOM RIGHT) ======================
const bubble = document.getElementById("chatToggle");
const box = document.getElementById("chatBox");

// Bubble fixed in bottom-right
bubble.style.position = "fixed";
bubble.style.bottom = "25px";
bubble.style.right = "25px";

// Chat box fixed in bottom-right
box.style.position = "fixed";
box.style.bottom = "90px";  // slightly above the bubble
box.style.right = "25px";   // aligned with the bubble

// Close chat when clicking outside


// Toggle chat box
bubble.onclick = () => {
  const isOpen = box.style.display === "flex";
  box.style.display = isOpen ? "none" : "flex";

  if (!isOpen) loadMessages();
};
