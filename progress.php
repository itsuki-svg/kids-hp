<?php
// api/progress.php - 進捗・フォーム入力・コード生成のリアルタイム共有

require __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

const PROGRESS_FILE = __DIR__ . '/data/progress.json';

function load_progress(): array {
    if (!is_file(PROGRESS_FILE)) return [];
    $data = json_decode(file_get_contents(PROGRESS_FILE), true);
    return is_array($data) ? $data : [];
}

function save_progress(array $data): void {
    file_put_contents(PROGRESS_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// GET: 進捗一覧を返す（admin用）
if ($method === 'GET') {
    $progress = load_progress();
    $now = time();

    // 30分以内のセッションのみ
    $active = array_filter($progress, fn($p) => ($now - ($p['ts'] ?? 0)) < 1800);

    // 同じslot_idがある場合は最新のもの1件だけ残す
    $bySlot = [];
    $noSlot = [];
    foreach ($active as $p) {
        $sid = $p['slot_id'] ?? '';
        if ($sid) {
            if (!isset($bySlot[$sid]) || $p['ts'] > $bySlot[$sid]['ts']) {
                $bySlot[$sid] = $p;
            }
        } else {
            $noSlot[] = $p;
        }
    }
    $result = array_merge(array_values($bySlot), $noSlot);
    // 更新時刻降順にソート
    usort($result, fn($a, $b) => $b['ts'] - $a['ts']);

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

// POST: 進捗を記録
if ($method === 'POST') {
    $rawBody = file_get_contents('php://input');
    $raw = json_decode($rawBody, true) ?: [];
    // Base64エンコードされたデータをデコード（WAF回避）
    if (isset($raw['data_b64'])) {
        $decoded = base64_decode($raw['data_b64'], true);
        $input = $decoded !== false ? (json_decode($decoded, true) ?: []) : [];
    } else {
        $input = $raw;
    }

    // デバッグ：受信内容をログに記録
    @file_put_contents(__DIR__ . '/data/progress_debug.log',
        date('H:i:s') . ' raw_keys=' . implode(',', array_keys($raw)) .
        ' has_b64=' . (isset($raw['data_b64']) ? 'Y' : 'N') .
        ' input_session=' . ($input['session_id'] ?? 'NONE') .
        ' input_step=' . ($input['step'] ?? 'NONE') . "\n",
        FILE_APPEND);

    $sessionId  = preg_replace('/[^a-zA-Z0-9]/', '', $input['session_id'] ?? '');
    $slotId     = preg_replace('/[^a-zA-Z0-9]/', '', $input['slot_id'] ?? '');
    $nickname   = htmlspecialchars(substr((string)($input['nickname'] ?? ''), 0, 30), ENT_QUOTES);
    $step       = (int)($input['step'] ?? 0);
    $stepLabel  = htmlspecialchars(substr((string)($input['step_label'] ?? ''), 0, 40), ENT_QUOTES);
    $level      = (int)($input['level'] ?? 0);

    // フォーム入力データ（プロンプト）
    $formData   = $input['form_data'] ?? null;   // {q1: '...', q2: '...'} の連想配列
    $prompt     = substr((string)($input['prompt'] ?? ''), 0, 3000);

    // コード生成スニペット
    $codeSnippet = substr((string)($input['code_snippet'] ?? ''), 0, 1000);
    $codeTotal   = (int)($input['code_total_chars'] ?? 0);

    if (!$sessionId) {
        echo json_encode(['ok' => false, 'msg' => 'session_id required']);
        exit;
    }

    $progress = load_progress();
    $existing = $progress[$sessionId] ?? [];

    // stepは送信値をそのまま使用（クライアント側で管理）
    $newStep      = $step;
    $newStepLabel = $stepLabel;

    $progress[$sessionId] = array_merge($existing, [
        'session_id'   => $sessionId,
        'slot_id'      => $slotId ?: ($existing['slot_id'] ?? ''),
        'nickname'     => $nickname ?: ($existing['nickname'] ?? '未入力'),
        'step'         => $newStep,
        'step_label'   => $newStepLabel,
        'level'        => $level ?: ($existing['level'] ?? 0),
        'level_label'  => ['', '初級', '中級', '上級'][$level] ?? ($existing['level_label'] ?? ''),
        'ts'           => time(),
        'updated'      => date('H:i:s'),
        'form_data'    => $formData  !== null ? $formData  : ($existing['form_data']    ?? null),
        'prompt'       => $prompt    !== ''   ? $prompt    : ($existing['prompt']       ?? ''),
        'code_snippet' => $codeSnippet !== '' ? $codeSnippet : ($existing['code_snippet'] ?? ''),
        'code_total'   => $codeTotal  > 0     ? $codeTotal   : ($existing['code_total']   ?? 0),
    ]);

    save_progress($progress);
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false, 'msg' => 'invalid method']);
