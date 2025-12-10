// ===================== REPLY SYSTEM ===================== //

// Make replyToId accessible globally
window.replyToId = null;

// Set reply
window.replyToMessage = function(messageId, fullName, messageText) {
  window.replyToId = messageId;

  const replyingToDiv = document.getElementById("replyingTo");
  const replyingToText = document.getElementById("replyingToText");
  const chatInput = document.getElementById("chatInput");

  if (replyingToDiv && replyingToText) {
    replyingToText.textContent = `${fullName}: ${messageText}`;
    replyingToDiv.style.display = "block";
    if (chatInput) chatInput.focus();
  }
};

// Cancel reply
window.cancelReply = function() {
  window.replyToId = null;

  const replyingToDiv = document.getElementById("replyingTo");
  const chatInput = document.getElementById("chatInput");

  if (replyingToDiv) replyingToDiv.style.display = "none";
  if (chatInput) chatInput.placeholder = "Type a message...";
};
