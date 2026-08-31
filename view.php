<?php require __DIR__ . '/lib.php';

$id    = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['id'] ?? '');
$slots = load_slots();
$slot  = $slots[$id] ?? null;

$hasFile = $slot && !empty($slot['file']) && is_file(PAGES_DIR . '/' . $slot['file']);

if ($hasFile) {
    $title   = $slot['title'] !== '' ? $slot['title'] : ('作品 #' . $id);
    $file    = PAGES_DIR . '/' . $slot['file'];
    $content = file_get_contents($file);

    // 作品HTMLの <head> 内にビューバー用CSSを注入し、bodyにpaddingを追加
    $bar_css = '
<style id="app-viewbar-css">
#app-viewbar{
  position:fixed !important;top:0 !important;left:0 !important;right:0 !important;
  height:52px !important;z-index:2147483647 !important;
  background:#59adaf !important;display:flex !important;align-items:center;
  gap:12px;padding:0 14px;
  box-shadow:0 2px 10px rgba(10,35,66,.18);font-family:sans-serif;
}
#app-viewbar img{height:28px;width:auto;display:block}
#app-viewbar .vb-back{
  text-decoration:none;font-weight:700;color:#0a2342;font-size:13px;
  background:rgba(255,255,255,.92);border-radius:7px;padding:6px 12px;
  white-space:nowrap;flex-shrink:0;
}
#app-viewbar .vb-back:hover{background:#fff}
#app-viewbar .vb-title{
  font-weight:700;font-size:14px;color:#fff;
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
/* bodyのpaddingをリセット系CSSより確実に上書き */
html body,body{padding-top:52px !important;margin-top:0 !important;}
/* 作品内の固定ヘッダーをビューバーの下に下げる */
body > header[style*="fixed"],
header{top:52px !important;}
</style>';

    $bar_html = '
<div id="app-viewbar">
  <a href="index.php"><img src="' . base_url() . '/assets/logo.png" alt="Logo"></a>'
  . (!empty($slot['published']) ? '<a class="vb-back" href="index.php">← 一覧</a>' : '')
  . '<div class="vb-title">' . e($title) . '</div>
</div>';

    // <head> が存在すればCSSをheadの末尾に注入、なければ先頭に追加
    if (stripos($content, '</head>') !== false) {
        $content = str_ireplace('</head>', $bar_css . '</head>', $content);
    } else {
        $content = $bar_css . $content;
    }

    // <body> タグの直後にバーのHTMLを注入（bodyタグにもstyle属性で確実にpadding指定）
    if (preg_match('/<body([^>]*)>/i', $content, $m)) {
        // 既存のstyle属性があれば追記、なければ追加
        $bodyTag = $m[0];
        if (stripos($bodyTag, 'style=') !== false) {
            $newTag = preg_replace('/style=["\']([^"\']*)["\']/', 'style="$1;padding-top:52px!important;"', $bodyTag);
        } else {
            $newTag = str_ireplace('>', ' style="padding-top:52px!important;">', $bodyTag);
        }
        $content = str_replace($bodyTag, $newTag . $bar_html, $content);
    } else {
        $content = $bar_html . $content;
    }

    header('Content-Type: text/html; charset=UTF-8');
    header('X-Frame-Options: SAMEORIGIN');
    echo $content;

} else {
?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>準備中</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=4">
</head>
<body>
  <div class="masthead"><div class="masthead-inner"><img src="assets/logo.png" alt="Logo"></div></div>
  <div class="standby">
    <div class="ic">Coming soon</div>
    <h2>準備中です</h2>
    <p>このQRの作品は、まだ作成されていません。<br>ワークショップでホームページを作ってから見てね！</p>
    <a href="index.php">公開中の作品を見る</a>
  </div>
</body></html>
<?php } ?>
