<!-- ===== STYLES ===== -->
<style>
/* ===== MODAL BASE ===== */
.modal-content {
  border-radius: 14px;
  overflow: hidden;
  background: #ffffff;
}

.modal-header {
  background: #f8f9fc;
  color: #1f2937;
  border-bottom: 1px solid #e5e7eb;
}

.modal-title i {
  color: #4f46e5;
}

/* ===== FILE LIST ===== */
.list-group {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  max-height: 60vh;
  overflow-y: auto;
}

.file-item {
  background: #ffffff;
  color: #374151;
  border: none;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: background .15s ease, transform .15s ease;
}

.file-item i {
  font-size: 1rem;
  opacity: .75;
}

.file-item:hover {
  background: #f1f5ff;
  transform: translateX(2px);
}

.file-item.active {
  background: #eef2ff;
  color: #1e40af;
  font-weight: 600;
}

/* ===== CODE VIEWER ===== */
#fileViewerCode {
  background: #2d2d34;
  color: #f8f8f2;
  border-radius: 12px;
  font-size: 13px;
  font-family: Consolas, Monaco, monospace;
  white-space: pre-wrap;
  overflow: auto;
  max-height: 70vh;
  line-height: 1.6;
  border: 1px solid #d1d5db;
  box-shadow: inset 0 0 5px rgba(0,0,0,0.15);
  padding: 15px;
}

/* ===== SCROLLBAR ===== */
#fileViewerCode::-webkit-scrollbar {
  width: 10px;
}
#fileViewerCode::-webkit-scrollbar-track {
  background: #1f1f26;
  border-radius: 12px;
}
#fileViewerCode::-webkit-scrollbar-thumb {
  background: #4e4e4eff;
  border-radius: 12px;
  border: 2px solid #2d2d34;
}
#fileViewerCode::-webkit-scrollbar-thumb:hover {
  background: #313131ff;
}

.list-group::-webkit-scrollbar {
  width: 8px;
}
.list-group::-webkit-scrollbar-track {
  background: #f9fafb;
  border-radius: 12px;
}
.list-group::-webkit-scrollbar-thumb {
  background: #c7d2fe;
  border-radius: 12px;
}
.list-group::-webkit-scrollbar-thumb:hover {
  background: #a5b4fc;
}

/* ===== COPY BUTTON ===== */
#copyBtn {
  background: #4f46e5;
  border: none;
  border-radius: 999px;
  padding: 6px 14px;
  font-size: 12px;
  box-shadow: 0 6px 18px rgba(79,70,229,.25);
  transition: transform .15s ease, box-shadow .15s ease;
}

#copyBtn:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 26px rgba(79,70,229,.35);
}

#copyBtn:active {
  transform: translateY(0);
}

/* ===== TOOLTIP ===== */
#copyTooltip {
  font-size: .75rem;
  font-weight: 600;
  color: #16a34a;
  animation: fadeUp .2s ease;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(6px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>

<!-- ===== FILE LIST MODAL WITH TABS ===== -->
<div class="modal fade" id="codeModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-code mr-2"></i>System Code Reference</h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Tabs -->
        <ul class="nav nav-tabs" id="fileTabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="php-tab" data-toggle="tab" href="#php" role="tab">PHP</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="js-tab" data-toggle="tab" href="#js" role="tab">JavaScript</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="sql-tab" data-toggle="tab" href="#sql" role="tab">Database</a>
          </li>
        </ul>

        <!-- Tab contents -->
        <div class="tab-content mt-3">
          <!-- PHP Files -->
          <div class="tab-pane fade show active" id="php" role="tabpanel">
            <div class="list-group">
              <a href="#" class="list-group-item file-item" data-file="../../modals/code/files/account/index.txt"><i class="fas fa-file-code mr-2 text-primary"></i> index.php</a>
              <a href="#" class="list-group-item file-item" data-file="../../modals/code/files/account/delete_account.txt"><i class="fas fa-trash-alt mr-2 text-danger"></i> delete_account.php</a>
              <a href="#" class="list-group-item file-item" data-file="../../modals/code/files/account/fetch_account.txt"><i class="fas fa-download mr-2 text-info"></i> fetch_account.php</a>
              <a href="#" class="list-group-item file-item" data-file="../../modals/code/files/account/save_account.txt"><i class="fas fa-save mr-2 text-success"></i> save_account.php</a>
              <a href="#" class="list-group-item file-item" data-file="../../modals/code/files/account/update_account.txt"><i class="fas fa-edit mr-2 text-warning"></i> update_account.php</a>
            </div>
          </div>

          <!-- JS Files -->
          <div class="tab-pane fade" id="js" role="tabpanel">
            <div class="list-group">
              <a href="#" class="list-group-item file-item" data-file="../../modals/code/files/account/admin_script.txt"><i class="fas fa-bolt mr-2 text-warning"></i> admin_script.js</a>
            </div>
          </div>

          <!-- SQL Files -->
          <div class="tab-pane fade" id="sql" role="tabpanel">
            <div class="list-group">
              <a href="#" class="list-group-item file-item" data-file="../../modals/code/files/account/database.sql.txt"><i class="fas fa-database mr-2 text-success"></i> database.sql</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== CODE VIEWER MODAL ===== -->
<div class="modal fade" id="fileViewerModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content bg-dark text-dark">
      <div class="modal-header bg-dark">
        <h5 class="modal-title"><i class="fas fa-file-code mr-2"></i><span id="fileViewerTitle"></span></h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body position-relative">
        <button id="copyBtn" type="button" class="btn btn-sm btn-primary position-absolute" style="top:10px; right:10px;"><i class="fas fa-copy"></i> Copy</button>
        <span id="copyTooltip" class="position-absolute text-success" style="top:15px; right:80px; display:none; font-weight:bold;">Copied!</span>
        <pre id="fileViewerCode" class="p-3">// Select a file</pre>
      </div>
    </div>
  </div>
</div>

<!-- ===== SCRIPT ===== -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  let currentCode = '';

  document.querySelectorAll('.file-item').forEach(item => {
    item.addEventListener('click', async e => {
      e.preventDefault();
      const path = item.dataset.file;
      const title = path.split('/').pop();

      const viewer = document.getElementById('fileViewerCode');
      document.getElementById('fileViewerTitle').textContent = title;
      viewer.textContent = '// Loading...';

      try {
        const res = await fetch(path, { cache: 'no-store' });
        currentCode = await res.text();
        viewer.textContent = currentCode;
        $('#fileViewerModal').modal('show');
      } catch {
        viewer.textContent = '// Failed to load file';
        currentCode = '';
      }
    });
  });

  // Copy button
  document.getElementById('copyBtn').addEventListener('click', () => {
    const viewer = document.getElementById('fileViewerCode');
    const tooltip = document.getElementById('copyTooltip');
    if (!viewer.textContent.trim()) return;

    const range = document.createRange();
    range.selectNodeContents(viewer);
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);

    try {
      document.execCommand('copy');
      selection.removeAllRanges();
      tooltip.style.display = 'inline';
      setTimeout(() => tooltip.style.display = 'none', 1400);
    } catch (e) {
      console.error('Copy failed', e);
    }
  });
});
</script>
