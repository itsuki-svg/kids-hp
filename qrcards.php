<?php require __DIR__ . '/lib.php';
require_login();

ensure_slots();
$slots = load_slots();
$base  = base_url();
?><!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>QRカード印刷 | <?= e(EVENT_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Zen Kaku Gothic New', sans-serif; background: #f0f0f0; }

  .qr-toolbar {
    padding: 16px 20px; background: #0a2342; color: #fff;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
  }
  .qr-toolbar h2 { font-size: 16px; font-weight: 700; }
  .qr-toolbar .btnrow { display: flex; gap: 8px; }
  .qr-toolbar .btn {
    padding: 8px 18px; border-radius: 8px; font-size: 14px; font-weight: 700;
    text-decoration: none; cursor: pointer; border: none;
  }
  .qr-toolbar .btn-pri { background: #59adaf; color: #0a2342; }
  .qr-toolbar .btn-sec { background: rgba(255,255,255,.15); color: #fff; }
  .qr-note {
    padding: 10px 20px; background: #fff7e0; color: #8a6d00;
    font-size: 13px; border-bottom: 1px solid #f0e0b0;
  }

  .qr-sheet {
    display: flex; flex-wrap: wrap; gap: 0;
    background: #fff; width: 89mm;    /* L判 短辺 */
    min-height: 127mm;                /* L判 長辺 */
    margin: 16px auto; padding: 4mm;
    box-shadow: 0 2px 12px rgba(0,0,0,.1);
    align-content: flex-start;
    justify-content: center;
  }
  .qr-card {
    width: 30mm; height: 30mm; padding: 2.5mm;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    border: 0.2mm dashed #ccc; gap: 1mm;
  }
  .qimg { width: 20mm; height: 20mm; }
  .qimg img, .qimg canvas {
    width: 20mm !important; height: 20mm !important; display: block;
  }
  .qr-label {
    font-size: 2.1mm; font-weight: 700; color: #0a2342;
    text-align: center; line-height: 1.1;
    white-space: nowrap;
  }

  @media print {
    @page { size: 89mm 127mm; margin: 4mm; }  /* L判 */
    body { background: #fff; }
    .qr-toolbar, .qr-note { display: none !important; }
    .qr-sheet {
      width: auto; min-height: auto; margin: 0; padding: 0;
      box-shadow: none; gap: 0;
    }
    .qr-card { border: 0.2mm dashed #ddd; page-break-inside: avoid; }
  }
</style>
</head><body>
<div class="qr-toolbar">
  <h2><?= e(EVENT_TITLE) ?> — QRカード（<?= count($slots) ?>枚）</h2>
  <div class="btnrow">
    <a class="btn btn-sec" href="admin.php">管理に戻る</a>
    <button class="btn btn-pri" onclick="window.print()">印刷する</button>
  </div>
</div>
<div class="qr-note">
  各QRカードは30mm角（QR本体20mm＋余白＋イベント名）です。L判シール（89×127mm）に等倍で印刷してください。コンビニのシールプリントで用紙を「L判シール」、倍率を「等倍/実際のサイズ」にしてください。1枚に8枚（横2×縦4）並びます。
</div>

<div class="qr-sheet">
  <?php foreach ($slots as $id => $s):
    $url = $base . '/view.php?id=' . $id;
  ?>
  <div class="qr-card">
    <div class="qimg" data-url="<?= e($url) ?>"></div>
    <div class="qr-label"><?= e(EVENT_TITLE) ?></div>
  </div>
  <?php endforeach; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.querySelectorAll('.qimg').forEach(function(el){
  new QRCode(el, {
    text: el.dataset.url,
    width: 200, height: 200,
    correctLevel: QRCode.CorrectLevel.M
  });
});
</script>
</body></html>
