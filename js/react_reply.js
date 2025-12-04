// ===================== REPLY SYSTEM ===================== //

let replyToId = null;

export function replyToMessage(messageId, fullName, messageText) {
    replyToId = messageId;
    document.getElementById("replyingToText").textContent = `${fullName}: ${messageText}`;
    document.getElementById("replyingTo").style.display = "block";
    document.getElementById("chatInput").focus();
}

export function cancelReply() {
    replyToId = null;
    document.getElementById("replyingTo").style.display = "none";
    document.getElementById("chatInput").placeholder = "Type a message...";
}

export function getReplyToId() {
    return replyToId;
}

// ===================== REACTION SYSTEM ===================== //

export function updateReactionsDiv(container, reactions) {
    container.innerHTML = "";
    if (!Array.isArray(reactions) || reactions.length === 0) {
        container.style.display = "none";
        return;
    }

    container.style.display = "flex";
    container.style.gap = "4px";

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
            zIndex: "99999"
        });
        document.body.appendChild(tooltip);
    }

    reactions.forEach(r => {
        if (!Array.isArray(r.users) || r.users.length === 0) return;

        const span = document.createElement("span");
        span.textContent = `${r.emoji} ${r.users.length}`;
        span.style.cursor = "pointer";
        span.style.padding = "2px 6px";

        span.addEventListener("mouseenter", () => {
            tooltip.innerHTML = "";
            reactions.forEach(rx => {
                rx.users.forEach(u => {
                    const line = document.createElement("div");
                    line.textContent = `${rx.emoji} - ${u}`;
                    tooltip.appendChild(line);
                });
            });

            const rect = container.getBoundingClientRect();
            tooltip.style.left = rect.left + "px";
            tooltip.style.top = (rect.bottom + 6) + "px";
            tooltip.style.display = "block";
        });

        span.addEventListener("mouseleave", () => {
            tooltip.style.display = "none";
        });

        container.appendChild(span);
    });
}
