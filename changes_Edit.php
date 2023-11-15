<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
<form method="POST" action="save_canvas.php" enctype="multipart/form-data">
<input type="file" name="imageFile" id="imageUpload" />
  <input type="submit" value="完了" id="complete">
</form>

<canvas id="canvas"></canvas>
<button id="r_rotate">右回転</button>
<button id="l_rotate">左回転</button>
<button id="zoomIn">ズームイン</button>
<button id="zoomOut">ズームアウト</button>
<button id="crop">トリミング</button>
<script>
let imageUpload = document.getElementById('imageUpload');
let canvas = document.getElementById('canvas');
let ctx = canvas.getContext('2d');
let img = new Image();
let rotation = 0;
let scale = 1;
let cropping = false;
let dragging = false;
let cropStart, cropEnd;
let lastX = 0;
let lastY = 0;
let translateX = 0;
let translateY = 0;
let historyIndex = -1;
let scrollX = 0;
let scrollY = 0;
let history = [];
let picture = [];
let list_lastX = [];
let list_lastY = [];
let scales = [];
let rotate = [];
let line_Width = 3;

function trimImage() {
  if (cropping && cropStart) {
    // トリミング範囲の座標を取得
    let x1 = Math.min(cropStart.x, cropEnd.x);
    let y1 = Math.min(cropStart.y, cropEnd.y);
    let x2 = Math.max(cropStart.x, cropEnd.x);
    let y2 = Math.max(cropStart.y, cropEnd.y);

    // トリミング範囲のサイズを計算
    let width = x2 - x1;
    let height = y2 - y1;

    // トリミングされた画像データを取得
    let trimmedImageData = ctx.getImageData(x1, y1, width, height);

    // キャンバス全体をクリア
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // キャンバスのサイズをトリミングされた画像のサイズに設定
    canvas.width = width;
    canvas.height = height;

    // トリミングされた画像データを表示
    ctx.putImageData(trimmedImageData, 0, 0);

    // トリミングを終了
    cropping = false;
    cropStart = null;
    cropEnd = null;
  }
}

// トリミングを行うボタンに trimImage 関数を結びつける
document.getElementById('crop').addEventListener('click', function() {
  trimImage();
});

imageUpload.addEventListener('change', function(e) {
  let file = e.target.files[0];
  let reader = new FileReader();

  reader.onloadend = function() {
    img.src = reader.result;
    img.onload = drawImage;
  }

  if (file) {
    reader.readAsDataURL(file);
  }
});

document.getElementById('r_rotate').addEventListener('click', function() {
  rotation += 10;
  drawImage();
});

document.getElementById('l_rotate').addEventListener('click', function() {
  rotation -= 10;
  drawImage();
});

document.getElementById('zoomIn').addEventListener('click', function() {
  scale *= 1.1;
  drawImage();
});

document.getElementById('zoomOut').addEventListener('click', function() {
  scale /= 1.1;
  drawImage();
});

document.getElementById('crop').addEventListener('click', function() {
  cropping = true;
});

function Saves() {
  scales.push(scale)
  rotate.push(rotation)
}

function saveHistory(imageData) {
  if (historyIndex !== history.length - 1) {
    history = history.slice(0, historyIndex + 1);
  }

  history.push(imageData);

  historyIndex++;
}

canvas.addEventListener('mousedown', function(e) {
  lastX = e.clientX - canvas.offsetLeft;
  lastY = e.clientY - canvas.offsetTop;

  if (cropping) {
    cropStart = getMousePos(canvas, e);
    ctx.beginPath();
    ctx.lineWidth = line_Width;
    ctx.strokeStyle = "black";
    ctx.rect(cropStart.x, cropStart.y, e.clientX - canvas.offsetLeft - cropStart.x, e.clientY - canvas.offsetTop - cropStart.y);
    ctx.stroke();
  } else {
    dragging = true;
  }
});

canvas.addEventListener('mousemove', function(e) {
  if (cropping && cropStart) {
    drawImage();
    ctx.beginPath();
    ctx.lineWidth = line_Width;
    ctx.strokeStyle = "black";
    ctx.rect(cropStart.x, cropStart.y, e.clientX - canvas.offsetLeft - cropStart.x, e.clientY - canvas.offsetTop - cropStart.y);
    ctx.stroke();

  } else if (dragging) {
    if (!cropping) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
    let x = e.clientX - canvas.offsetLeft;
    let y = e.clientY - canvas.offsetTop;

    translateX += x - lastX;
    translateY += y - lastY;

    lastX = x;
    lastY = y;

    picture.push(image)
    list_lastX.push(lastX)
    list_lastY.push(lastY)

    drawImage();
  }
});

canvas.addEventListener('mouseup', function(e) {
  if (cropping) {
    cropping = false;
    cropEnd = getMousePos(canvas, e);
    let width = Math.abs(cropEnd.x - cropStart.x);
    let height = Math.abs(cropEnd.y - cropStart.y);
    imageData = ctx.getImageData(Math.min(cropStart.x, cropEnd.x)+line_Width, Math.min(cropStart.y, cropEnd.y)+line_Width, width-line_Width*2, height-line_Width*2);
    img.src = getImageURL(imageData);
    img.onload = function() {
      drawImage();
      cropStart = null;
      cropEnd = null;
    };
  } else {
    dragging = false;
  }
});

canvas.addEventListener('wheel', function(e) {
  e.preventDefault();
  if (e.deltaY < 0) {
    scale *= 1.1;
  } else {
    scale /= 1.1;
  }

  drawImage();
});

function drawImage() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.save();
  ctx.translate(canvas.width / 2 + translateX + scrollX, canvas.height / 2 + translateY + scrollY); // スクロールを考慮
  ctx.rotate(rotation * Math.PI / 180);
  ctx.scale(scale, scale);
  ctx.drawImage(img, -img.width / 2, -img.height / 2);
  ctx.restore();
  Saves();
}

canvas.addEventListener('mousemove', function(e) {
  if (dragging) {
    let x = e.clientX - canvas.offsetLeft;
    let y = e.clientY - canvas.offsetTop;

    scrollX += x - lastX;
    scrollY += y - lastY;

    lastX = x;
    lastY = y;

    drawImage();
  }
});

canvas.addEventListener('mouseup', function(e) {
  if (dragging) {
    dragging = false;
  }
});

function getMousePos(canvas, evt) {
  var rect = canvas.getBoundingClientRect();
  return {
    x: evt.clientX - rect.left,
    y: evt.clientY - rect.top
  };
}

function getImageURL(imageData) {
  let canvasTemp = document.createElement("canvas");
  canvasTemp.width = imageData.width;
  canvasTemp.height = imageData.height;

  let contextTemp = canvasTemp.getContext("2d");
  contextTemp.putImageData(imageData, 0, 0);

  return canvasTemp.toDataURL("image/jpeg");
}

document.getElementById('complete').addEventListener('click', function() {
  // Canvasから画像データを取得
  var canvas = document.getElementById("canvas"); // Canvas要素のIDに置き換える
  var imageDataUrl = canvas.toDataURL("image/jpeg"); // 画像データURLを取得

  // 画像データをフォームに追加
  var imageDataInput = document.createElement("input");
  imageDataInput.type = "hidden";
  imageDataInput.name = "imageDataUrl";
  imageDataInput.value = imageDataUrl;

  // フォームに画像データの入力フィールドを追加
  var myForm = document.querySelector('form');
  myForm.appendChild(imageDataInput);

  // フォームを送信
  myForm.submit();
});

</script>
</body>
</html>