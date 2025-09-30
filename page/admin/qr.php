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
    .qr-wrapper {
        border: 2px solid #ccc;
        border-radius: 10px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        background: transparent;
    }
    .qr-text {
        margin-top: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        color: #333;
        word-wrap: break-word;
        text-align: center;
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
                            <div class="qr-wrapper" id="qrWrapper">
                                <canvas id="qrCanvas" width="200" height="200"></canvas>
                                <div id="qrValue" class="qr-text">Hello World</div>
                            </div>
                            <button class="download-btn" onclick="downloadCanvas('qrWrapper','qrCanvas',true)">Download QR</button>
                        </div>

                        <div class="canva-block">
                            <div class="qr-wrapper" id="barcodeWrapper">
                                <canvas id="barcodeCanvas" width="300" height="150"></canvas>
                            </div>
                            <button class="download-btn" onclick="downloadCanvas('barcodeWrapper','barcodeCanvas',false)">Download Barcode</button>
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

    // === QR CODE ===
    const qrCanvas = document.getElementById('qrCanvas');
    const qrCtx = qrCanvas.getContext('2d');
    qrCtx.clearRect(0, 0, qrCanvas.width, qrCanvas.height);

    const qrDiv = document.createElement('div');
    qrDiv.style.position = 'absolute';
    qrDiv.style.left = '-9999px';
    document.body.appendChild(qrDiv);

    new QRCode(qrDiv, {
        text: text,
        width: 200,
        height: 200,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    document.getElementById('qrValue').innerText = text;

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
        format: "CODE128",
        lineColor: "#000",
        width: 2,
        height: 80,
        displayValue: true // show text below bars
    });
}

function downloadCanvas(wrapperId, canvasId, includeText) {
    const inputText = document.getElementById("inputText").value || "Hello World";
    const safeText = inputText.replace(/\s+/g, "_"); 
    const filename = safeText + ".png";

    const wrapper = document.getElementById(wrapperId);
    const canvas = document.getElementById(canvasId);
    const text = includeText ? wrapper.querySelector(".qr-text")?.innerText : "";

    // Create temporary canvas
    const tempCanvas = document.createElement("canvas");
    const ctx = tempCanvas.getContext("2d");

    const width = canvas.width;
    const height = includeText ? canvas.height + 40 : canvas.height;
    tempCanvas.width = width;
    tempCanvas.height = height;

    // White background
    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, width, height);

    // Draw QR/Barcode
    ctx.drawImage(canvas, 0, 0);

    // Draw text under QR only
    if (includeText && text) {
        ctx.fillStyle = "#000000";
        ctx.font = "16px Arial";
        ctx.textAlign = "center";
        ctx.fillText(text, width / 2, canvas.height + 25);
    }

    // Download
    const link = document.createElement("a");
    link.download = filename;
    link.href = tempCanvas.toDataURL();
    link.click();
}

window.onload = updateCanvases;
</script>

<?php
include 'plugins/footer.php';
?>
