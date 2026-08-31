<?php
// ※ファイルのMIMEタイプ検証・サイズ制限は lib.php の assign_slot() 内で実施
require __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    echo json_encode(['ok' => false, 'msg' => 'ログインが必要です。']);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !check_csrf()) {
    echo json_encode(['ok' => false, 'msg' => '不正なリクエストです。ページを再読み込みしてください。']);
    exit;
}

[$ok, $msg, $id] = assign_slot(
    (string)($_POST['id'] ?? ''),
    trim((string)($_POST['title'] ?? '')),
    !empty($_POST['published']),
    $_FILES['htmlfile'] ?? null,
    trim((string)($_POST['prompt'] ?? ''))
);

echo json_encode([
    'ok'   => $ok,
    'msg'  => $msg,
    'id'   => $id,
    'view' => $ok ? (base_url() . '/view.php?id=' . $id) : '',
], JSON_UNESCAPED_UNICODE);
