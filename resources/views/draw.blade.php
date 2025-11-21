<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>塗鴉</title>
    <style>
        body { margin: 0; padding: 20px; background: #f5f5f5; font-family: Arial, sans-serif; display: flex; flex-direction: column; align-items: center; }
        #canvas { border: 2px solid #8bc34a; cursor: crosshair; background: #fff; display: block; }
        .toolbar { margin-bottom: 20px; padding: 15px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .toolbar label { margin-right: 20px; }
        .toolbar input, .toolbar button { margin-left: 5px; }
        button { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        #clear { background: #ff7043; color: white; }
        #save { background: #2196f3; color: white; margin-left: 10px; }
        #insert { background: #8bc34a; color: white; margin-left: 10px; }
        button:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="toolbar">
        <label>顏色: <input type="color" id="color" value="#000000"></label>
        <label>粗細: <input type="range" id="size" min="1" max="20" value="3" style="width: 150px;"> <span id="sizeValue">3</span>px</label>
        <button id="clear">清除</button>
        <button id="save" style="display: none;">儲存</button>
        <button id="insert" style="display: none;">插入</button>
    </div>
    <canvas id="canvas" width="750" height="750"></canvas>
    
    <script>
        const isFromPreview = window.opener && window.opener.location.pathname.includes('/preview/');
        const saveBtn = document.getElementById('save');
        const insertBtn = document.getElementById('insert');
        
        if (isFromPreview) {
            saveBtn.style.display = 'inline-block';
        } else {
            insertBtn.style.display = 'inline-block';
        }
        
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const colorInput = document.getElementById('color');
        const sizeInput = document.getElementById('size');
        const sizeValue = document.getElementById('sizeValue');
        const clearBtn = document.getElementById('clear');
        
        let drawing = false;
        let currentColor = '#000000';
        let currentSize = 3;
        
        // 檢查是否有背景圖片
        const urlParams = new URLSearchParams(window.location.search);
        const bgImage = urlParams.get('bgImage');
        
        if (bgImage) {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
            };
            img.src = decodeURIComponent(bgImage);
        } else {
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }
        
        canvas.addEventListener('mousedown', (e) => {
            drawing = true;
            const rect = canvas.getBoundingClientRect();
            ctx.beginPath();
            ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
        });
        
        canvas.addEventListener('mousemove', (e) => {
            if (!drawing) return;
            const rect = canvas.getBoundingClientRect();
            ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
            ctx.strokeStyle = currentColor;
            ctx.lineWidth = currentSize;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.stroke();
        });
        
        canvas.addEventListener('mouseup', () => { drawing = false; });
        canvas.addEventListener('mouseleave', () => { drawing = false; });
        
        colorInput.addEventListener('change', (e) => {
            currentColor = e.target.value;
        });
        
        sizeInput.addEventListener('input', (e) => {
            currentSize = parseInt(e.target.value);
            sizeValue.textContent = currentSize;
        });
        
        clearBtn.addEventListener('click', () => {
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        });
        
        saveBtn.addEventListener('click', () => {
            canvas.toBlob((blob) => {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'drawing_' + Date.now() + '.png';
                a.click();
                URL.revokeObjectURL(url);
                
                if (window.opener) {
                    const dataUrl = canvas.toDataURL('image/png');
                    window.opener.postMessage({ type: 'saveDrawing', data: dataUrl }, '*');
                    setTimeout(() => window.close(), 500);
                }
            });
        });
        
        insertBtn.addEventListener('click', () => {
            const dataUrl = canvas.toDataURL('image/png');
            if (window.opener) {
                window.opener.postMessage({ type: 'insertDrawing', data: dataUrl }, '*');
                window.close();
            }
        });
    </script>
</body>
</html>
