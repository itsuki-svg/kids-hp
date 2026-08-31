<?php
// save_workshop.php - workshop専用の保存API（ルート配置・WAF回避）
// ログイン不要・CSRF必須・HTMLとpromptはBase64で受信

require __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'POSTのみ受け付けます。']);
    exit;
}

if (!check_csrf()) {
    echo json_encode(['ok' => false, 'msg' => '不正なリクエストです。ページを再読み込みしてください。']);
    exit;
}

// Base64エンコードされたHTMLを受信してデコード
$htmlBase64 = $_POST['html_b64'] ?? '';
if (empty($htmlBase64)) {
    echo json_encode(['ok' => false, 'msg' => 'HTMLデータがありません。']);
    exit;
}
$htmlContent = base64_decode($htmlBase64, true);
if ($htmlContent === false) {
    echo json_encode(['ok' => false, 'msg' => 'HTMLのデコードに失敗しました。']);
    exit;
}

// サイズチェック
if (strlen($htmlContent) > MAX_UPLOAD_BYTES) {
    echo json_encode(['ok' => false, 'msg' => 'ファイルが大きすぎます（上限 ' . round(MAX_UPLOAD_BYTES / 1024 / 1024, 1) . 'MB）。']);
    exit;
}

// プロンプトをBase64デコード（あれば）
$prompt = '';
if (!empty($_POST['prompt_b64'])) {
    $decoded = base64_decode($_POST['prompt_b64'], true);
    if ($decoded !== false) $prompt = $decoded;
}

$id = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['id'] ?? '');
if (!$id) {
    echo json_encode(['ok' => false, 'msg' => 'IDが不正です。']);
    exit;
}

$slots = load_slots();
if (!isset($slots[$id])) {
    echo json_encode(['ok' => false, 'msg' => "ID「{$id}」は存在しません。QRコードが正しいか確認してください。"]);
    exit;
}

// pages/に直接書き込む
if (!is_dir(PAGES_DIR)) @mkdir(PAGES_DIR, 0775, true);
$dest = PAGES_DIR . '/slot-' . $id . '.html';
if (file_put_contents($dest, $htmlContent) === false) {
    echo json_encode(['ok' => false, 'msg' => 'ファイルの保存に失敗しました（pages/の書き込み権限を確認）。']);
    exit;
}

// slots.jsonを更新
$slot = $slots[$id];
$slot['title']     = trim((string)($_POST['title'] ?? ''));
$slot['file']      = 'slot-' . $id . '.html';
$slot['published'] = !empty($_POST['published']);
$slot['updated']   = date('Y-m-d H:i');
if ($prompt !== '') $slot['prompt'] = $prompt;
$slots[$id] = $slot;

if (!save_slots($slots)) {
    echo json_encode(['ok' => false, 'msg' => 'スロット情報の保存に失敗しました（data/の書き込み権限を確認）。']);
    exit;
}

$site_base = BASE_URL !== '' ? rtrim(BASE_URL, '/') : rtrim(dirname(base_url()), '/');
echo json_encode([
    'ok'   => true,
    'msg'  => "ID「{$id}」に保存しました。",
    'id'   => $id,
    'view' => $site_base . '/view.php?id=' . $id,
], JSON_UNESCAPED_UNICODE);
