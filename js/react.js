// ===================== REACTIONS SYSTEM ===================== //

// Create a global tooltip once
let reactionsTooltip = document.getElementById("reactionsTooltip");
if (!reactionsTooltip) {
  reactionsTooltip = document.createElement("div");
  reactionsTooltip.id = "reactionsTooltip";
  Object.assign(reactionsTooltip.style, {
    position: "fixed",
    background: "#fff",
    color: "#000",
    border: "1px solid #ccc",
    borderRadius: "8px",
    padding: "6px 10px",
    boxShadow: "0 2px 12px rgba(0,0,0,0.3)",
    fontSize: "12px",
    minWidth: "140px",
    maxWidth: "300px",
    maxHeight: "150px",
    overflowY: "auto",
    display: "none",
    zIndex: "99999",
    whiteSpace: "normal",
    wordWrap: "break-word",
    textAlign: "left"
  });
  document.body.appendChild(reactionsTooltip);
}

// Update reactions UI for a message
window.updateReactionsDiv = function(container, reactions) {
  container.innerHTML = "";
  if (!Array.isArray(reactions) || reactions.length === 0) {
    container.style.display = "none";
    return;
  }
  container.style.display = "flex";
  container.style.gap = "4px";

  // Tooltip per message container
  let tooltip = container.querySelector(".reactionsTooltip");
  if (!tooltip) {
    tooltip = document.createElement("div");
    tooltip.className = "reactionsTooltip";
    Object.assign(tooltip.style, {
      position: "fixed",
      background: "#fff",
      color: "#000",
      border: "1px solid #ccc",
      borderRadius: "8px",
      padding: "6px 10px",
      boxShadow: "0 2px 12px rgba(0,0,0,0.3)",
      fontSize: "12px",
      minWidth: "140px",
      maxWidth: "300px",
      maxHeight: "150px",
      overflowY: "auto",
      display: "none",
      zIndex: "99999",
      whiteSpace: "normal",
      wordWrap: "break-word",
      textAlign: "left"
    });
    document.body.appendChild(tooltip);
  }

  reactions.forEach(r => {
    if (!Array.isArray(r.users) || r.users.length === 0) return;

    const span = document.createElement("span");
    span.textContent = `${r.emoji} ${r.users.length}`;
    span.style.cursor = "pointer";
    span.style.padding = "2px 6px";
    span.style.borderRadius = "4px";

    // Hover tooltip
    span.addEventListener("mouseenter", () => {
      tooltip.innerHTML = "";
      reactions.forEach(rx => {
        rx.users.forEach(u => {
          const line = document.createElement("div");
          line.textContent = `${rx.emoji} - ${u}`;
          line.style.marginBottom = "2px";
          tooltip.appendChild(line);
        });
      });

      tooltip.style.display = "block";

      const rect = container.getBoundingClientRect();
      let left = rect.left;
      if (left + tooltip.offsetWidth > window.innerWidth) left = window.innerWidth - tooltip.offsetWidth - 8;
      if (left < 4) left = 4;
      tooltip.style.left = left + "px";

      const spaceBelow = window.innerHeight - rect.bottom;
      const spaceAbove = rect.top;
      tooltip.style.top = (spaceBelow < tooltip.offsetHeight && spaceAbove > tooltip.offsetHeight)
        ? rect.top - tooltip.offsetHeight - 4
        : rect.bottom + 4 + "px";
    });

    span.addEventListener("mouseleave", () => {
      tooltip.style.display = "none";
    });

    container.appendChild(span);
  });

  // Hide tooltip on click outside
  document.addEventListener("click", (event) => {
    if (!tooltip.contains(event.target)) tooltip.style.display = "none";
  });
};

// Attach emoji picker to a message
window.attachEmojiPicker = function(msgBubble, msgId, reactionsDiv) {
  const reactIcon = document.createElement("i");
  reactIcon.className = "fa-regular fa-face-smile";
  reactIcon.title = "React";
  reactIcon.style.cursor = "pointer";

  const emojiPicker = document.createElement("div");
  emojiPicker.classList.add("emoji-picker");
  Object.assign(emojiPicker.style, {
    display: "none",
    position: "absolute",
    bottom: "24px",
    left: "0",
    background: "#fff",
    border: "1px solid #ccc",
    borderRadius: "8px",
    padding: "2px 4px",
    boxShadow: "0 4px 10px rgba(0,0,0,0.2)",
    gap: "4px",
    flexWrap: "wrap",
    zIndex: "9999"
  });

  const emojis = ["😄", "😢", "😡", "❤️", "👍", "😲", "😵‍💫", "🤯"];
  emojis.forEach(e => {
    const btn = document.createElement("button");
    btn.textContent = e;
    Object.assign(btn.style, {
      border: "none",
      background: "transparent",
      cursor: "pointer",
      fontSize: "16px",
      padding: "2px"
    });

    btn.addEventListener("click", async () => {
      try {
        const resp = await fetch("/sen_template/process/chat/react_message.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ message_id: msgId, emoji: e }),
          credentials: "same-origin"
        });
        const data = await resp.json();
        if (data.status === "success") {
          updateReactionsDiv(reactionsDiv, data.reactions);
        }
      } catch(err) {
        console.error("Failed to react:", err);
      }
    });

    emojiPicker.appendChild(btn);
  });

reactIcon.addEventListener("click", (event) => {
  event.stopPropagation();
  emojiPicker.style.display = emojiPicker.style.display === "block" ? "none" : "block"; // <- block instead of flex
});

  document.addEventListener("click", (event) => {
    if (!emojiPicker.contains(event.target) && event.target !== reactIcon) {
      emojiPicker.style.display = "none";
    }
  });

  return { reactIcon, emojiPicker };
};
