<?php
require dirname(__DIR__) . '/lib.php';
header('Content-Type: application/json; charset=utf-8');

$id = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['id'] ?? '');
if (!$id) {
    echo json_encode(['ok' => false, 'msg' => 'IDが不正です']);
    exit;
}

$slots = load_slots();
$slot  = $slots[$id] ?? null;

if (!$slot) {
    echo json_encode(['ok' => false, 'msg' => 'このQRコードは登録されていません。スタッフに声をかけてください。']);
    exit;
}

$hasFile = !empty($slot['file']) && is_file(PAGES_DIR . '/' . $slot['file']);

if ($hasFile) {
    echo json_encode([
        'ok'      => false,
        'used'    => true,
        'msg'     => 'このQRコードはすでに使われています。スタッフに声をかけてください。',
        'title'   => $slot['title'],
        'updated' => $slot['updated'],
    ]);
    exit;
}

echo json_encode([
    'ok'   => true,
    'used' => false,
    'id'   => $id,
    'msg'  => 'QRコードを確認しました！',
]);
