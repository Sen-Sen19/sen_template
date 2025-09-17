<?php include 'plugins/navbar.php'; ?>
<?php include 'plugins/sidebar/admin_bar.php'; ?>


<div class="content-wrapper">
  <div class="content-header"></div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-gray-dark card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                               <i class="nav-icon fas fa-globe"></i> Upload
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
                            <div class="row mt-1 align-items-center mb-2">
                                <div class="col-md-6 d-flex">
                                <select id="fileTypeDropdown" class="form-control form-control-sm" 
        style="width: 160px; height: 35px; font-size: 14px; margin-right: 5px;">
    <option value="">-- File Type --</option>
    <option value="pdf">PDF</option>
    <option value="txt">TXT</option>
    <option value="csv">CSV</option>
    <option value="doc">DOC</option>
    <option value="docx">DOCX</option>
    <option value="xls">XLS</option>
    <option value="xlsx">XLSX</option>
    <option value="ppt">PPT</option>
    <option value="pptx">PPTX</option>
    <option value="jpg">JPG</option>
    <option value="jpeg">JPEG</option>
    <option value="png">PNG</option>
    <option value="gif">GIF</option>
    <option value="zip">ZIP</option>
    <option value="rar">RAR</option>
    <option value="mp3">MP3</option>
    <option value="mp4">MP4</option>
    <option value="any">ALL</option>
</select>


                                    <button type="button" id="searchBtn" class="btn btn-primary" style="height: 35px; margin-right:5px;">
                                        <i class="fas fa-search" style="margin-right:5px;"></i> Search
                                    </button>

                                    <button type="button" id="importBtn" class="btn btn-success" style="height: 35px; background-color:#008b02; border-color:#008b02;">
                                        <i class="fas fa-globe" style="margin-right:5px;"></i> Upload
                                    </button>

                                    <input type="file" id="csvFileInput" name="csvFile" class="form-control" accept="*" style="display:none;" required>
                                </div>
                            </div>
                        </div>

                        <!-- File List Container with max-height like Viewer -->
                        <div id="fileListContainer" class="list-group"
                             style="height: 63vh; overflow-y: auto; margin-top: 10px; border-top:1px solid gray;">
                            <ul id="fileList" class="list-group list-group-flush">
                                <!-- Files will appear here -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'plugins/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Map extensions to FontAwesome icons and optional colors
function getFileIcon(extension) {
    extension = extension.toLowerCase();
    let color = '#7F8C8D'; // default gray
    let iconClass = 'fa-file';

    switch(extension) {
        case 'pdf': iconClass = 'fa-file-pdf'; color='#E74C3C'; break;
        case 'doc':
        case 'docx': iconClass='fa-file-word'; color='#3498DB'; break;
        case 'xls':
        case 'xlsx': iconClass='fa-file-excel'; color='#27AE60'; break;
        case 'ppt':
        case 'pptx': iconClass='fa-file-powerpoint'; color='#E67E22'; break;
        case 'txt': iconClass='fa-file-alt'; color='#95A5A6'; break;
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif': iconClass='fa-file-image'; color='#F1C40F'; break;
        case 'zip':
        case 'rar': iconClass='fa-file-archive'; color='#8E44AD'; break;
        case 'mp3': iconClass='fa-file-audio'; color='#2ECC71'; break;
        case 'mp4': iconClass='fa-file-video'; color='#E74C3C'; break;
        case 'csv': iconClass='fa-file-csv'; color='#2ECC71'; break; // CSV green
    }

    return `<i class="fas ${iconClass}" style="color:${color}; font-size:20px; margin-right:8px; width:24px; text-align:center;"></i>`;
}

function fetchFiles(highlightFile = '') {
    const selectedType = document.getElementById('fileTypeDropdown').value;

    fetch('../../process/get_files.php?type=' + selectedType)
        .then(res => res.json())
        .then(files => {
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = '';

            let filtered = files;
            if(selectedType && selectedType !== 'any'){
                filtered = files.filter(f => f.FileName.split('.').pop().toLowerCase() === selectedType.toLowerCase());
            }

            // SORT by UploadDate DESCENDING
            filtered.sort((a, b) => new Date(b.UploadDate) - new Date(a.UploadDate));

            if(filtered.length){
                filtered.forEach(f => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center';
                    if(f.FileName === highlightFile) li.style.backgroundColor = '#d4edda';

                    const ext = f.FileName.split('.').pop();
                    li.innerHTML = `
                        <span style="display:flex; align-items:center;">
                            ${getFileIcon(ext)} 
                            ${f.FileName} 
                            <small style="margin-left:10px; color:red;">(${f.UploadDate})</small>
                        </span>
                        <div>
                            <a href="../../process/download_file.php?file=${encodeURIComponent(f.FileName)}" class="btn btn-sm btn-primary mr-1">Download</a>
                            <button class="btn btn-sm btn-danger deleteBtn" data-filename="${encodeURIComponent(f.FileName)}">Delete</button>
                        </div>
                    `;
                    fileList.appendChild(li);
                });

                // Delete button logic
                document.querySelectorAll('.deleteBtn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const fileToDelete = decodeURIComponent(this.getAttribute('data-filename'));
                        Swal.fire({
                            title: `Are you sure you want to delete "${fileToDelete}"?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if(result.isConfirmed){
                                fetch('../../process/delete_file.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: 'file=' + encodeURIComponent(fileToDelete)
                                })
                                .then(res => res.text())
                                .then(res => {
                                    Swal.fire({ icon:'success', title:res, showConfirmButton:false, timer:1500 });
                                    fetchFiles();
                                });
                            }
                        });
                    });
                });

            } else {
                Swal.fire({ icon:'info', title:'No files found', showConfirmButton:false, timer:1500 });
            }
        })
        .catch(err => {
            Swal.fire({ icon:'error', title:'Error fetching files' });
        });
}

// Upload button triggers file input
document.getElementById('importBtn').addEventListener('click', () => document.getElementById('csvFileInput').click());

// Handle file upload
document.getElementById('csvFileInput').addEventListener('change', function() {
    const file = this.files[0];
    if(file){
        const formData = new FormData();
        formData.append('file', file);

        fetch('../../process/upload_file.php', { method:'POST', body:formData })
            .then(res => res.text())
            .then(res => {
                Swal.fire({ icon:'success', title:res, showConfirmButton:false, timer:1500 });
                fetchFiles(file.name); // Highlight uploaded file
            })
            .catch(err => Swal.fire({ icon:'error', title:'Error uploading file' }));
    }
});

// Manual search button
document.getElementById('searchBtn').addEventListener('click', fetchFiles);

// Auto-fetch on page load
window.addEventListener('DOMContentLoaded', fetchFiles);
</script>
