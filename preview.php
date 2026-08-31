<?php require __DIR__ . '/lib.php';
if (!is_logged_in()) { header('Location: admin.php'); exit; }

$id = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['id'] ?? '');
if (!$id) { header('Location: admin.php'); exit; }

$slots = load_slots();
$slot  = $slots[$id] ?? null;
$hasFile = $slot && !empty($slot['file']) && is_file(PAGES_DIR . '/' . $slot['file']);
$htmlContent = $hasFile ? file_get_contents(PAGES_DIR . '/' . $slot['file']) : '';
?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>コードプレビュー | <?= e(EVENT_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=4">
<style>
.pv-wrap { max-width: 1200px; margin: 0 auto; padding: 24px 16px 60px; }
.pv-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--line); }
.pv-head h1 { font-size: 18px; margin: 0; font-weight: 800; color: var(--navy); }
.pv-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; height: calc(100vh - 160px); }
.pv-panel { display: flex; flex-direction: column; border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); }
.pv-panel-head { background: #e6eeee; padding: 10px 16px; font-size: 13px; font-weight: 700; color: var(--navy); border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
.pv-preview { flex: 1; border: none; background: #fff; width: 100%; }
.pv-code { flex: 1; font-family: 'Courier New', monospace; font-size: 11px; line-height: 1.7; background: #1C1C2E; color: #fff; padding: 14px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; margin: 0; }
.char-count { font-size: 11px; color: var(--ink-soft); font-weight: 400; }
@media (max-width: 768px) {
  .pv-layout { grid-template-columns: 1fr; height: auto; }
  .pv-panel { height: 60vh; }
}
</style>
</head>
<body>
<div class="masthead"><div class="masthead-inner"><img src="assets/logo.png" alt="Logo"></div></div>

<div class="pv-wrap">
  <div class="pv-head">
    <div>
      <h1>コードプレビュー — ID: <?= e($id) ?></h1>
      <?php if ($slot): ?>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:3px;">
          <?= e($slot['title'] ?: '無題') ?> &nbsp;／&nbsp;
          <?= $slot['published'] ? '<span style="color:#1f7a45;font-weight:700;">公開中</span>' : '<span style="color:#888;">非公開</span>' ?>
          &nbsp;／&nbsp; 更新: <?= e($slot['updated'] ?: '未更新') ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="btnrow">
      <?php if ($hasFile): ?>
        <a class="btn btn-pri btn-mini" href="view.php?id=<?= e($id) ?>" target="_blank">ページを見る</a>
        <a class="btn btn-line btn-mini" href="data:text/html;charset=utf-8,<?= rawurlencode($htmlContent) ?>" download="slot-<?= e($id) ?>.html">ダウンロード</a>
      <?php endif; ?>
      <a class="btn btn-sec btn-mini" href="admin.php">← 管理に戻る</a>
    </div>
  </div>

  <?php if (!$hasFile): ?>
    <div class="empty">
      <div class="big">まだ作成されていません</div>
      <p>このIDのHTMLファイルはまだ保存されていません。</p>
    </div>
  <?php else: ?>
    <div class="pv-layout">
      <!-- 左: プレビュー -->
      <div class="pv-panel">
        <div class="pv-panel-head">
          👀 プレビュー
          <button onclick="reloadPreview()" class="btn btn-sec btn-mini">更新</button>
        </div>
        <iframe id="preview-iframe" class="pv-preview"
          srcdoc="<?= htmlspecialchars($htmlContent, ENT_QUOTES) ?>"
          sandbox="allow-scripts allow-same-origin"></iframe>
      </div>

      <!-- 右: コード -->
      <div class="pv-panel">
        <div class="pv-panel-head">
          📄 HTMLコード
          <div style="display:flex;gap:8px;align-items:center;">
            <span class="char-count"><?= number_format(strlen($htmlContent)) ?>文字</span>
            <button onclick="copyCode()" class="btn btn-sec btn-mini" id="copy-btn">コピー</button>
          </div>
        </div>
        <pre class="pv-code" id="code-pre"><?= htmlspecialchars($htmlContent, ENT_QUOTES) ?></pre>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
const rawHtml = <?= json_encode($htmlContent) ?>;

function reloadPreview() {
  document.getElementById('preview-iframe').srcdoc = rawHtml;
}

function copyCode() {
  navigator.clipboard.writeText(rawHtml).then(() => {
    const btn = document.getElementById('copy-btn');
    btn.textContent = 'コピー済！';
    setTimeout(() => btn.textContent = 'コピー', 1500);
  });
}
</script>
</body>
</html>
