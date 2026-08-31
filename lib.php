<?php
/* ============================================================
 *  共通関数ライブラリ
 * ============================================================ */
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ===== CSRF ===== */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
function csrf_token(): string { return $_SESSION['csrf']; }
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) . '">';
}
function check_csrf(): bool {
    return isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

const DATA_FILE = __DIR__ . '/data/slots.json';
const PAGES_DIR = __DIR__ . '/pages';

/* HTMLエスケープ */
function e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* ベースURL（末尾スラッシュなし） */
function base_url(): string {
    if (BASE_URL !== '') return rtrim(BASE_URL, '/');
    $scheme = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $scheme . '://' . $host . $dir;
}

/* ランダムID生成（8文字、紛らわしい文字を除外） */
function generate_slot_id(): string {
    $chars = 'abcdefghjkmnpqrstuvwxyz23456789';
    $id = '';
    for ($i = 0; $i < 8; $i++) {
        $id .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $id;
}

/* 全スロットを読み込む */
function load_slots(): array {
    if (is_file(DATA_FILE)) {
        $json    = file_get_contents(DATA_FILE);
        $decoded = json_decode($json, true);
        if (is_array($decoded)) return $decoded;
    }
    return [];
}

/* スロットを保存（排他ロック付き） */
function save_slots(array $slots): bool {
    if (!is_dir(dirname(DATA_FILE))) {
        @mkdir(dirname(DATA_FILE), 0775, true);
    }
    $json = json_encode($slots, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $fp = @fopen(DATA_FILE, 'c+');
    if (!$fp) return false;
    $ok = false;
    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0); rewind($fp);
        fwrite($fp, $json); fflush($fp);
        flock($fp, LOCK_UN); $ok = true;
    }
    fclose($fp);
    return $ok;
}

/* スロットを新規発行（SLOT_COUNT枚になるまで生成） */
function ensure_slots(): void {
    $slots = load_slots();
    $current = count($slots);
    if ($current >= SLOT_COUNT) return;
    for ($i = $current; $i < SLOT_COUNT; $i++) {
        do { $id = generate_slot_id(); } while (isset($slots[$id]));
        $slots[$id] = [
            'title'     => '',
            'file'      => '',
            'published' => false,
            'updated'   => '',
        ];
    }
    save_slots($slots);
}

/* 公開中のスロットだけ返す */
function published_slots(): array {
    $out = [];
    foreach (load_slots() as $id => $s) {
        if (!empty($s['published']) && !empty($s['file']) && is_file(PAGES_DIR . '/' . $s['file'])) {
            $out[$id] = $s;
        }
    }
    return $out;
}

/* ===== 認証 ===== */
function is_logged_in(): bool { return !empty($_SESSION['kidshp_admin']); }
function require_login(): void {
    if (!is_logged_in()) { header('Location: admin.php'); exit; }
}

/* idからカード色（見た目用） */
function slot_hue(string $id): int {
    $hash = crc32($id);
    return abs($hash) % 360;
}

/* ===== 割当処理 =====
 * 戻り値: [bool $ok, string $msg, string $id]
 */
function assign_slot(string $idRaw, string $title, bool $published, ?array $file, string $prompt = ''): array {
    // IDの検証（ランダム文字列 or 数字）
    $id = preg_replace('/[^a-zA-Z0-9]/', '', $idRaw);
    if (strlen($id) < 1) {
        return [false, "IDが不正です。", ''];
    }

    $slots = load_slots();
    if (!isset($slots[$id])) {
        return [false, "ID「{$id}」は存在しません。QRコードが正しいか確認してください。", $id];
    }

    $slot              = $slots[$id];
    $slot['title']     = $title;
    $slot['published'] = $published;

    // ファイルアップロード
    if ($file && !empty($file['name']) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    // ── ファイルサイズチェック
        if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
            return [false, "ID「{$id}」：ファイルが大きすぎます（上限 " . round(MAX_UPLOAD_BYTES / 1024 / 1024, 1) . "MB）。", $id];
        }
        if (($file['size'] ?? 0) === 0) {
            return [false, "ID「{$id}」：ファイルが空です。", $id];
        }

        // ── 拡張子チェック
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['html', 'htm'], true)) {
            return [false, "ID「{$id}」：HTMLファイル（.html / .htm）を選んでください。", $id];
        }

        // ── MIMEタイプ検証（finfo）
        if (function_exists('finfo_open')) {
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            $allowedMime = ['text/html', 'text/plain', 'application/xhtml+xml'];
            if (!in_array($mimeType, $allowedMime, true)) {
                return [false, "ID「{$id}」：HTMLファイルではありません（検出されたタイプ: {$mimeType}）。", $id];
            }
        }

        // ── 保存先
        if (!is_dir(PAGES_DIR)) @mkdir(PAGES_DIR, 0775, true);
        $dest = 'slot-' . $id . '.html';
        if (!move_uploaded_file($file['tmp_name'], PAGES_DIR . '/' . $dest)) {
            return [false, "ID「{$id}」：保存に失敗（pages/ の書き込み権限を確認）。", $id];
        }
        $slot['file'] = $dest;
    }

    if (empty($slot['file'])) {
        return [false, "ID「{$id}」：ファイルがありません。HTMLを指定してください。", $id];
    }

    $slot['updated'] = date('Y-m-d H:i');
    if ($prompt !== '') $slot['prompt'] = $prompt;
    $slots[$id]      = $slot;
    if (!save_slots($slots)) {
        return [false, "ID「{$id}」：保存に失敗（data/ の書き込み権限を確認）。", $id];
    }
    return [true, "ID「{$id}」に割り当てました。", $id];
}
