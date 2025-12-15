



 


// ----------------- EDIT / REPLY HANDLING -----------------
let editingMessageId = null;
let editingMessageDatetime = null;
let selectedFiles = [];
function editMessage(datetime, message, message_id) {
    chatInput.value = message;
    chatInput.focus();

    editingMessageId = message_id;
    editingMessageDatetime = datetime;

    const editingBar = document.getElementById("editingBar");
    editingBar.style.display = "block";
}

function cancelEdit() {
    chatInput.value = "";
    editingMessageId = null;
    editingMessageDatetime = null;

    const editingBar = document.getElementById("editingBar");
    editingBar.style.display = "none";

    chatInput.focus();
}


// ----------------- ATTACHMENTS -----------------
const attachBtn = document.getElementById("attachBtn");
const fileInput = document.getElementById("fileInput");

attachBtn.addEventListener("click", () => fileInput.click());
fileInput.addEventListener("change", () => {
    selectedFiles = Array.from(fileInput.files);

    const replyingDiv = document.getElementById("replyingTo");
    const textEl = replyingDiv.querySelector("#replyingToText");

    if (window.replyToId && textEl.dataset.replyText) {
        // If replying, preserve reply text and optionally show attachments
        if (selectedFiles.length > 0) {
            textEl.textContent = `${textEl.dataset.replyText} + ${selectedFiles.length === 1 ? selectedFiles[0].name : selectedFiles.length + " files"}`;
        } else {
            textEl.textContent = textEl.dataset.replyText;
        }
    } else if (selectedFiles.length > 0) {
        // Only attachment, no reply
        textEl.textContent = selectedFiles.length === 1 ? `Attachment selected: ${selectedFiles[0].name}` : `${selectedFiles.length} files selected`;
    }

    // Show box only if replying or attachments exist
    replyingDiv.style.display = (window.replyToId || selectedFiles.length > 0) ? "block" : "none";
});

function replyToMessage(msgId, fullName, message) {
    window.replyToId = msgId;

    const replyingDiv = document.getElementById("replyingTo");
    const textEl = replyingDiv.querySelector("#replyingToText");
    textEl.textContent = `Replying to ${fullName}: ${message}`;
    textEl.dataset.replyText = textEl.textContent; // store original reply text

    replyingDiv.style.display = "block";
}


// ----------------- SEND MESSAGE HANDLER -----------------
chatSend.onclick = async () => {
    let message = chatInput.value.trim();
    if (!message && selectedFiles.length === 0 && !window.replyToId) return;
    if (!message && window.replyToId) message = "[Reply]";

    // Clear input immediately to prevent double sending
    chatInput.value = "";

    const loaderContainer = document.getElementById("sendingLoaderContainer");
    loaderContainer.innerHTML = ""; // Clear previous loader
    loaderContainer.style.display = "block";

    // Create sending bubble
    const sendingWrapper = document.createElement("div");
    sendingWrapper.style.display = "flex";
    sendingWrapper.style.alignItems = "center";
    sendingWrapper.style.justifyContent = "flex-end";
    sendingWrapper.style.gap = "8px";
    sendingWrapper.style.marginBottom = "6px";

    const bubble = document.createElement("div");
    bubble.textContent = message;
    bubble.style.background = "#d8d8d8ff";
    bubble.style.color = "#fff";
    bubble.style.padding = "6px 10px";
    bubble.style.borderRadius = "12px";
    bubble.style.fontSize = "13px";
    bubble.style.border = "1px solid #ebebebff";
    bubble.style.maxWidth = "70%";
    bubble.style.wordBreak = "break-word";
    bubble.style.display = "flex";
    bubble.style.alignItems = "center";
    bubble.style.gap = "6px";

    const spinner = document.createElement("div");
    spinner.style.width = "14px";
    spinner.style.height = "14px";
    spinner.style.border = "2px solid rgba(255,255,255,0.5)";
    spinner.style.borderTopColor = "#fff";
    spinner.style.borderRadius = "50%";
    spinner.style.animation = "spin .8s linear infinite";

    bubble.prepend(spinner);
    sendingWrapper.appendChild(bubble);
    loaderContainer.appendChild(sendingWrapper);

    const form = new FormData();
    if (message) form.append("message", message);
    if (window.replyToId) form.append("reply_to_id", window.replyToId);
    selectedFiles.forEach(f => form.append("attachments[]", f));

    try {
        let resp;
        if (editingMessageId) {
            resp = await fetch("/sen_template/process/chat/edit_message.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    message_id: editingMessageId,
                    datetime: editingMessageDatetime,
                    message: message
                }),
                credentials: "same-origin"
            });
        } else {
            resp = await fetch("/sen_template/process/chat/send_messages.php", {
                method: "POST",
                body: form,
                credentials: "same-origin"
            });
        }
   chatInput.value = "";

    // Hide editing banner
    const editingBar = document.getElementById("editingBar");
    if (editingBar) editingBar.style.display = "none";
        const data = await resp.json();

        if (data.status === "success") {
            selectedFiles = [];
            fileInput.value = "";
            window.replyToId = null;
            editingMessageId = null;
            editingMessageDatetime = null;

            const replyingDiv = document.getElementById("replyingTo");
            replyingDiv.style.display = "none";
            replyingDiv.querySelector("#replyingToText").textContent = "";

            await loadMessages(); // Replace the temporary bubble with real messages
        } else {
            chatInput.value = "⚠️ " + (data.message || "Failed");
            chatInput.focus();
        }
    } catch (err) {
        console.error("Send/Edit error:", err);
        chatInput.value = "⚠️ Network error";
    } finally {
        loaderContainer.style.display = "none"; // hide sending bubble
    }
};




// ----------------- DELETE MESSAGE -----------------
function deleteMessage(datetime, message_id) {
    if (!confirm("Are you sure you want to delete this message?")) return;

    fetch("/sen_template/process/chat/delete_message.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ datetime, message_id }),
        credentials: "same-origin"
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === "success") {
            loadMessages();
        } else {
            chatInput.value = "⚠️ " + (data.message || "Delete failed");
            chatInput.focus();
        }
    });
};


