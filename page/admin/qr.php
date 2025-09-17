<?php
include 'plugins/navbar.php';
include 'plugins/sidebar/admin_bar.php';
?>

<style>
    .canva-row {
        display: flex;
        gap: 48px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 32px;
    }
    .canva-block {
        background: #fcfcfc;
        border-radius: 12px;
        padding: 24px 16px 16px 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        min-width: 220px;
    }
    canvas {
        background: #fff;
        border-radius: 8px;
        margin-bottom: 16px;
    }
    .download-btn {
        background: #00a108ff;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 8px 20px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .download-btn:hover {
        background: #00ad00ff;
    }
    .input-row {
        margin-top: 32px;
        width: 100%;
        display: flex;
        justify-content: center;
    }
    .input-row input {
        width: 350px;
        padding: 10px 14px;
        border-radius: 6px;
        border: none;
        font-size: 1.1rem;
        background: #fdfdfdff;
        color: #000000ff;
        box-shadow: 0 1px 4px #0004;
    }
    @media (max-width: 800px) {
        .canva-row {
            flex-direction: column;
            gap: 32px;
            align-items: center;
        }
    }
</style>


<div class="content-wrapper">
  <div class="content-header"></div>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-gray-dark card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="nav-icon fas fa-qrcode"></i> QR Code & Barcode
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
                    <div class="canva-row">
                        <div class="canva-block">
                            <canvas id="qrCanvas" width="200" height="200"></canvas>
                            <button class="download-btn" onclick="downloadCanvas('qrCanvas', 'qr_code.png')">Download QR</button>
                        </div>
                        <div class="canva-block">
                            <canvas id="barcodeCanvas" width="300" height="200"></canvas>
                            <button class="download-btn" onclick="downloadCanvas('barcodeCanvas', 'barcode.png')">Download Barcode</button>
                        </div>
                    </div>
                    <div class="input-row">
                        <input type="text" id="inputText" placeholder="Enter text for QR and Barcode..." oninput="updateCanvases()">
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script src="plugins/js/qrcode.min.js"></script>
<script src="plugins/js/jsbarcode.all.min.js"></script>
<script>
function updateCanvases() {
    const text = document.getElementById('inputText').value || 'Hello World';

    // === QR CODE (normal square style) ===
    const qrCanvas = document.getElementById('qrCanvas');
    const qrCtx = qrCanvas.getContext('2d');
    qrCtx.clearRect(0, 0, qrCanvas.width, qrCanvas.height);

    const qrDiv = document.createElement('div');
    qrDiv.style.position = 'absolute';
    qrDiv.style.left = '-9999px';
    document.body.appendChild(qrDiv);

    const qr = new QRCode(qrDiv, {
        text: text,
        width: 200,
        height: 200,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    // Copy generated QR into your canvas
    setTimeout(() => {
        const qrImg = qrDiv.querySelector('img'); 
        if (qrImg) {
            const tempImg = new Image();
            tempImg.src = qrImg.src;
            tempImg.onload = () => {
                qrCtx.drawImage(tempImg, 0, 0, qrCanvas.width, qrCanvas.height);
                document.body.removeChild(qrDiv);
            };
        }
    }, 300);

    // === BARCODE ===
    const barcodeCanvas = document.getElementById('barcodeCanvas');
    JsBarcode(barcodeCanvas, text, {
        format: "CODE128",  // Good general barcode
        lineColor: "#000",
        width: 2,
        height: 80,
        displayValue: true
    });
}

function downloadCanvas(canvasId, filename) {
    const canvas = document.getElementById(canvasId);
    const link = document.createElement('a');
    link.download = filename;
    link.href = canvas.toDataURL();
    link.click();
}

// Initial render
window.onload = updateCanvases;
</script>

<?php
include 'plugins/footer.php';
?>
