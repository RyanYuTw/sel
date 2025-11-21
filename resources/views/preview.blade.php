<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 2rem; 
            margin: 0;
            background: linear-gradient(135deg, #fffef0 0%, #fff9e6 50%, #fffef0 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.15);
        }
        .user-input {
            border: none;
            border-bottom: 2px solid #2196f3;
            background: transparent;
            padding: 4px 8px;
            font-size: inherit;
            font-family: inherit;
            color: #1976d2;
            min-width: 30px;
            width: auto;
        }
        .user-input:focus {
            outline: none;
            border-bottom-color: #1565c0;
            background: #e3f2fd;
        }
        .user-textarea {
            border: 2px solid #2196f3;
            border-radius: 4px;
            padding: 8px;
            font-size: inherit;
            font-family: inherit;
            width: 100%;
            min-height: 80px;
            resize: vertical;
        }
        .user-textarea:focus {
            outline: none;
            border-color: #1565c0;
            background: #e3f2fd;
        }
        .char-with-zhuyin { 
            display: inline-flex; 
            align-items: center; 
            margin-right: 0.2rem;
            line-height: 1;
        }
        .vertical-zhuyin { 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            margin-left: 1px; 
            max-height: 0.85em; 
            overflow: visible; 
            position: relative; 
        }
        .zhuyin-char { 
            font-size: 0.25em; 
            color: #333; 
            line-height: 0.85; 
        }
        .zhuyin-with-tone { 
            display: flex; 
            flex-direction: column; 
            position: relative; 
        }
        .tone-mark { 
            position: absolute; 
            right: -0.3em; 
            top: 50%; 
            transform: translateY(-50%); 
            font-size: 0.25em; 
        }
        .zhuyin-with-light { 
            position: relative; 
            display: inline-block; 
        }
        .light-tone { 
            position: absolute; 
            top: -0.6em; 
            left: 0; 
            right: 0; 
            text-align: center; 
            font-size: 0.7em; 
            color: #000; 
        }
        .image-input-wrapper {
            position: relative;
            display: inline-block;
            max-width: 100%;
        }
        .image-input-wrapper img {
            display: block;
            max-width: 100%;
            height: auto;
        }
        .draggable-input-wrapper {
            position: absolute;
            z-index: 10;
            margin: 0;
            padding: 0;
            transform: translateY(18px);
        }
        .draggable-input-wrapper input {
            background: transparent;
            border: none;
            border-bottom: 1px solid #000;
            padding: 2px 4px;
            cursor: text;
            min-width: 30px;
            outline: none;
            font-size: 14px;
            font-family: Arial, sans-serif;
            margin: 0;
            text-align: center;
        }
        .draggable-input-wrapper input:focus {
            border-bottom: 2px solid #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="content">{!! $content !!}</div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 處理按鈕點擊
            const buttons = document.querySelectorAll('.custom-button');
            buttons.forEach(button => {
                button.style.cursor = 'pointer';
                button.addEventListener('click', function() {
                    const link = this.getAttribute('data-link');
                    if (link && link !== '#') {
                        if (link === '/draw') {
                            const drawWindow = window.open(link, '塗鴉');
                            if (drawWindow) {
                                drawWindow.moveTo(0, 0);
                                drawWindow.resizeTo(screen.availWidth, screen.availHeight);
                            }
                            
                            window.addEventListener('message', (event) => {
                                if (event.data.type === 'saveDrawing') {
                                    const img = document.createElement('img');
                                    img.src = event.data.data;
                                    img.style.maxWidth = '100%';
                                    img.style.height = 'auto';
                                    img.style.display = 'block';
                                    img.style.margin = '20px auto';
                                    img.style.border = '2px dashed #000';
                                    
                                    this.parentNode.insertBefore(img, this.nextSibling);
                                    this.style.display = 'none';
                                }
                            });
                        } else {
                            window.location.href = link;
                        }
                    }
                });
            });
            
            const fields = document.querySelectorAll('.input-field');
            fields.forEach(field => {
                const label = field.getAttribute('data-label') || '輸入';
                const type = field.getAttribute('data-type') || 'text';
                const width = field.getAttribute('data-width');
                let input;
                if (type === 'textarea') {
                    input = document.createElement('textarea');
                    input.className = 'user-textarea';
                    input.placeholder = label;
                } else {
                    input = document.createElement('input');
                    input.type = type;
                    input.className = 'user-input';
                    input.placeholder = label;
                    if (width && !isNaN(width)) {
                        input.style.width = width + 'px';
                        input.style.maxWidth = width + 'px';
                    }
                }
                field.parentNode.replaceChild(input, field);
            });
            
            // 處理圖片上的輸入欄
            const draggableInputs = document.querySelectorAll('.draggable-input');
            draggableInputs.forEach(inputDiv => {
                const leftPercent = inputDiv.getAttribute('data-left-percent');
                const topPercent = inputDiv.getAttribute('data-top-percent');
                const label = inputDiv.getAttribute('data-label') || inputDiv.textContent.trim();
                const width = inputDiv.style.width;
                
                const wrapper = document.createElement('span');
                wrapper.className = 'draggable-input-wrapper';
                wrapper.style.left = leftPercent + '%';
                wrapper.style.top = topPercent + '%';
                
                const input = document.createElement('input');
                input.type = 'text';
                input.placeholder = label;
                input.style.width = width;
                
                wrapper.appendChild(input);
                inputDiv.parentNode.replaceChild(wrapper, inputDiv);
            });
        });
    </script>
</body>
</html>
