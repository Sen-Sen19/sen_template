<?php
include 'plugins/navbar.php';
include 'plugins/sidebar/admin_bar.php';
?>

<div class="content-wrapper">
  <div class="content-header"></div>
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12">
          <div class="card card-gray-dark card-outline">
            <div class="card-header">
              <h3 class="card-title">
                <i class="nav-icon fas fa-book"></i> Notes
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                  <i class="fas fa-expand"></i>
                </button>
              </div>
            </div>
            <div class="card-body">

              <!-- Modern Toolbar -->
              <div id="toolbar" class="toolbar mb-3">
                <!-- Font Family -->
                <select onchange="execCmd('fontName', this.value)" class="toolbar-select">
                  <option value="Arial">Arial</option>
                  <option value="Verdana">Verdana</option>
                  <option value="Times New Roman">Times New Roman</option>
                  <option value="Courier New">Courier New</option>
                </select>

                <!-- Font Size -->
                <select onchange="execCmd('fontSize', this.value)" class="toolbar-select">
                  <option value="1">Small</option>
                  <option value="3" selected>Normal</option>
                  <option value="5">Large</option>
                  <option value="7">Huge</option>
                </select>

                <!-- Style Buttons -->
                <button class="toolbar-btn" onclick="execCmd('bold')" title="Bold"><i class="fas fa-bold"></i></button>
                <button class="toolbar-btn" onclick="execCmd('italic')" title="Italic"><i class="fas fa-italic"></i></button>
                <button class="toolbar-btn" onclick="execCmd('underline')" title="Underline"><i class="fas fa-underline"></i></button>
                <button class="toolbar-btn" onclick="execCmd('strikeThrough')" title="Strikethrough"><i class="fas fa-strikethrough"></i></button>

<!-- Lists -->
<button class="toolbar-btn" onclick="execCmd('insertUnorderedList')" title="Bullet List"><i class="fas fa-list-ul"></i></button>
<button class="toolbar-btn" onclick="execCmd('insertOrderedList')" title="Numbered List"><i class="fas fa-list-ol"></i></button>
                <!-- Alignment -->
                <button class="toolbar-btn" onclick="execCmd('justifyLeft')" title="Align Left"><i class="fas fa-align-left"></i></button>
                <button class="toolbar-btn" onclick="execCmd('justifyCenter')" title="Align Center"><i class="fas fa-align-center"></i></button>
                <button class="toolbar-btn" onclick="execCmd('justifyRight')" title="Align Right"><i class="fas fa-align-right"></i></button>

                <!-- Font Color -->
        <label class="toolbar-label">
                  <i class="fas fa-palette"></i> Font Color
                  <input type="color" onchange="execCmd('foreColor', this.value)">
                </label>

                <!-- Highlight -->
                <label class="toolbar-label">
                  <i class="fas fa-highlighter"></i> Highlight
                  <input type="color" onchange="execCmd('hiliteColor', this.value)">
                </label>

                <!-- Insert Date/Time -->
                <button class="toolbar-btn" onclick="insertDateTime()" title="Insert Date & Time">
                  <i class="fas fa-calendar-alt"></i>
                </button>
              </div>

              <!-- Editable Text Area -->
              <div id="editor" contenteditable="true" 
                   style="min-height:63vh; border:1px solid #ddd; padding:15px; border-radius:8px; background:#fff; box-shadow:inset 0 0 5px rgba(0,0,0,0.05); font-weight:400;">
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<style>
  #editor {
  min-height: 63vh;
  max-height: 63vh;   /* ✅ lock height */
  border: 1px solid #ddd;
  padding: 15px;
  border-radius: 8px;
  background: #fff;
  box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
  font-weight: 400;
  overflow-y: auto;   /* ✅ add vertical scrollbar */
  white-space: pre-wrap; /* ✅ prevent text from overflowing horizontally */
  word-wrap: break-word;
}
  .toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #f8f9fa;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }

  .toolbar-select {
    padding: 5px 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 400;
    background: white;
    cursor: pointer;
  }

  .toolbar-btn {
    border: none;
    background: #fff;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
    font-size: 14px;
    color: #444;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
  }

  .toolbar-btn:hover {
    background: #e9ecef;
    transform: scale(1.05);
  }
.toolbar-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 400;
  background: #fff;
  padding: 4px 8px;
  border-radius: 6px;
  border: 1px solid #ccc;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  line-height: 1;
  margin-top: 8px;   /* ✅ Add space from the top */
}

.toolbar-label input[type="color"] {
  appearance: none;
  border: none;
  background: transparent;
  cursor: pointer;
  width: 24px;
  height: 24px;
  padding: 0;
  margin: 0;
  border-radius: 4px;
}


</style>

<script>
  function execCmd(command, value = null) {
    document.execCommand(command, false, value);
  }

  function insertDateTime() {
    const now = new Date();
    const formatted = now.toLocaleString(); // e.g., "9/17/2025, 1:35:22 PM"
    document.execCommand("insertText", false, formatted + " ");
  }


document.addEventListener("DOMContentLoaded", () => {
  const editor = document.getElementById("editor");

  // Load content on page load
  fetch("../../process/load_note.php")
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        editor.innerHTML = data.content;
      }
    });

  // Auto-save every 2 seconds after typing
  let timeout;
  editor.addEventListener("input", () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      fetch("../../process/save_note.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "content=" + encodeURIComponent(editor.innerHTML)
      });
    }, 2000);
  });
});
  
</script>

<?php
include 'plugins/footer.php';
?>
