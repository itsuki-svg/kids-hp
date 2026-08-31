<?php require __DIR__ . '/lib.php';

// スロットを確保
ensure_slots();

$flash = null;

if (($_GET['action'] ?? '') === 'logout') {
    $_SESSION['kidshp_admin'] = false;
    header('Location: admin.php'); exit;
}

if (($_POST['action'] ?? '') === 'login') {
    if (hash_equals(get_admin_password(), (string)($_POST['password'] ?? ''))) {
        $_SESSION['kidshp_admin'] = true;
        header('Location: admin.php'); exit;
    }
    $flash = ['type' => 'err', 'msg' => 'パスワードが違います。'];
}

if (!is_logged_in()) {
?><!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>管理ログイン</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=4">
</head><body>
<div class="login-box">
  <h1>管理ログイン</h1>
  <p><?= e(EVENT_TITLE) ?></p>
  <?php if ($flash): ?><div class="flash <?= $flash['type'] ?>"><?= e($flash['msg']) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="action" value="login">
    <input type="password" name="password" placeholder="パスワード" autofocus>
    <button class="btn btn-pri" type="submit">ログイン</button>
  </form>
</div>
</body></html>
<?php exit;
}

/* ---- 保存 ---- */
// ※ファイルのMIMEタイプ検証・サイズ制限は lib.php の assign_slot() 内で実施
if (($_POST['action'] ?? '') === 'save' && check_csrf()) {
    [$ok, $msg] = assign_slot(
        (string)($_POST['id'] ?? ''),
        trim((string)($_POST['title'] ?? '')),
        !empty($_POST['published']),
        $_FILES['htmlfile'] ?? null
    );
    $flash = ['type' => $ok ? 'ok' : 'err', 'msg' => $msg];
}

/* ---- クリア ---- */
if (($_POST['action'] ?? '') === 'clear' && check_csrf()) {
    $id    = preg_replace('/[^a-zA-Z0-9]/', '', (string)($_POST['id'] ?? ''));
    $slots = load_slots();
    if (isset($slots[$id])) {
        if (!empty($slots[$id]['file'])) @unlink(PAGES_DIR . '/' . $slots[$id]['file']);
        $slots[$id] = ['title' => '', 'file' => '', 'published' => false, 'updated' => ''];
        save_slots($slots);
        $flash = ['type' => 'ok', 'msg' => "ID「{$id}」をクリアしました。"];
    }
}

$slots    = load_slots();
$pubCount = count(published_slots());
$no = 1;
?><!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>管理画面 | <?= e(EVENT_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=4">
</head><body>
<div class="admin-wrap">
  <div class="admin-head">
    <h1>管理画面</h1>
    <div class="btnrow">
      <a class="btn btn-pri" href="assign.php">スキャンで割り当て</a>
      <a class="btn btn-line" href="qrcards.php" target="_blank">QRカードを印刷</a>
      <a class="btn btn-sec" href="index.php" target="_blank">公開ページを見る</a>
      <a class="btn btn-sec" href="admin.php?action=logout">ログアウト</a>
    </div>
  </div>

  <!-- リアルタイム進捗パネル -->
  <div id="progress-panel" style="background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:24px;overflow:hidden;">
    <div style="background:var(--navy);color:#fff;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;">
      <span style="font-weight:800;font-size:15px;">🔴 ライブ進捗モニター</span>
      <span id="progress-updated" style="font-size:12px;opacity:.7;"></span>
    </div>
    <div id="progress-list" style="padding:14px 18px;min-height:60px;">
      <p style="color:var(--ink-soft);font-size:13px;margin:0;">参加者の進捗を待っています...</p>
    </div>
  </div>

  <?php if ($flash): ?><div class="flash <?= $flash['type'] ?>"><?= e($flash['msg']) ?></div><?php endif; ?>

  <p style="color:var(--ink-soft);font-size:13px;margin:0 0 16px">
    QR枠：全 <?= count($slots) ?> 枚　／　公開中：<strong><?= $pubCount ?></strong> 作品<br>
    IDはランダム文字列です。QRカード印刷ページで番号とIDを確認できます。
  </p>

  <table class="slot-table">
    <thead><tr>
      <th style="width:40px">No.</th>
      <th style="width:140px">ID</th>
      <th>タイトル / ファイル</th>
      <th style="width:88px">状態</th>
      <th style="width:200px">操作</th>
    </tr></thead>
    <tbody>
    <?php foreach ($slots as $id => $s):
      $hasFile = $s['file'] && is_file(PAGES_DIR . '/' . $s['file']);
      $isPub   = !empty($s['published']) && $hasFile;
      $noStr   = str_pad((string)$no, 2, '0', STR_PAD_LEFT);
    ?>
      <tr>
        <td style="font-weight:700;color:var(--ink-soft)">#<?= $noStr ?></td>
        <td><code style="font-size:12px;color:var(--teal-deep)"><?= e($id) ?></code></td>
        <td>
          <form class="inline-form" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= e($id) ?>">
            <input type="text" name="title" value="<?= e($s['title']) ?>" placeholder="タイトル（例：たろうのゲームページ）">
            <div class="r">
              <input type="file" name="htmlfile" accept=".html,.htm">
              <?php if ($hasFile): ?>
                <span style="font-size:12px;color:#1f7a45">✓ 登録済み<?php if ($s['updated']): ?>（<?= e($s['updated']) ?>）<?php endif; ?></span>
              <?php else: ?>
                <span style="font-size:12px;color:var(--ink-soft)">未登録</span>
              <?php endif; ?>
            </div>
            <label class="r" style="font-size:13px;font-weight:700">
              <input type="checkbox" name="published" value="1" <?= !empty($s['published']) ? 'checked' : '' ?>>
              公開する
            </label>
            <div class="r">
              <button class="btn btn-pri btn-mini" type="submit">保存</button>
              <?php if ($hasFile): ?>
                <a class="btn btn-sec btn-mini" href="preview.php?id=<?= e($id) ?>" target="_blank">確認</a>
              <?php endif; ?>
              <?php if (!empty($s['prompt'])): ?>
                <button type="button" class="btn btn-sec btn-mini" onclick="showPrompt(<?= htmlspecialchars(json_encode($s['prompt']), ENT_QUOTES) ?>)">プロンプト</button>
              <?php endif; ?>
            </div>
          </form>
        </td>
        <td>
          <?php if ($isPub): ?><span class="tag on">公開中</span>
          <?php elseif ($hasFile): ?><span class="tag off">非公開</span>
          <?php else: ?><span class="tag off">空き</span><?php endif; ?>
        </td>
        <td>
          <?php if ($hasFile): ?>
          <form method="post" onsubmit="return confirm('ID「<?= e($id) ?>」をクリアします。よろしいですか？');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="clear">
            <input type="hidden" name="id" value="<?= e($id) ?>">
            <button class="btn btn-danger btn-mini" type="submit">クリア</button>
          </form>
          <?php else: ?><span style="color:var(--ink-soft);font-size:12px">—</span><?php endif; ?>
        </td>
      </tr>
    <?php $no++; endforeach; ?>
    </tbody>
  </table>
</div>

<!-- プロンプト確認モーダル -->
<script>
// ── リアルタイム進捗ポーリング ──
const STEP_COLORS = ['#aaa','#2E86C1','#1A5276','#D35400','#1E8449','#8E44AD','#1a7a4a'];

async function fetchProgress() {
  try {
    const res  = await fetch('<?= rtrim(BASE_URL, "/") ?>/progress.php');
    const list = await res.json();
    const el   = document.getElementById('progress-list');
    document.getElementById('progress-updated').textContent = '最終更新: ' + new Date().toLocaleTimeString('ja-JP');

    if (!Array.isArray(list) || !list.length) {
      el.innerHTML = '<p style="color:#8a98a4;font-size:13px;margin:0;">現在アクティブな参加者はいません</p>';
      return;
    }

    const STEP_LABELS = ['','QRスキャン完了','難易度選択','フォーム入力中','AI生成中','確認・修正中','保存完了'];
    let html = '';
    for (const p of list) {
      const step  = parseInt(p.step) || 0;
      const color = STEP_COLORS[step] || '#aaa';
      const pct   = Math.round((step / 6) * 100);
      const name  = p.nickname || '名前未入力';
      const label = p.step_label || STEP_LABELS[step] || '';
      const lvl   = p.level_label || '';
      const sid   = p.slot_id || '';

      let formRows = '';
      if (p.form_data && typeof p.form_data === 'object') {
        for (const [k, v] of Object.entries(p.form_data)) {
          if (!v || k.endsWith('_checks') || k.endsWith('_free')) continue;
          const val = Array.isArray(v) ? v.join('・') : String(v).slice(0, 80);
          formRows += `<tr><td style="color:#8a98a4;font-size:11px;padding:2px 8px 2px 0;white-space:nowrap;">${k}</td><td style="font-size:11px;color:#1a1a2e;">${val}</td></tr>`;
        }
      }
      const formHtml = formRows ? `<div style="margin-top:8px;"><div style="font-size:11px;font-weight:700;color:#555;margin-bottom:3px;">📝 フォーム入力</div><table style="width:100%;border-collapse:collapse;">${formRows}</table></div>` : '';

      const prompt = p.prompt || '';
      const promptLabel = step <= 3 ? '✏️ 入力中のプロンプト' : '📤 送信プロンプト';
      const promptHtml = prompt ? `<div style="margin-top:8px;"><div style="font-size:11px;font-weight:700;color:#555;margin-bottom:3px;">${promptLabel}</div><pre style="font-size:10px;color:#333;background:#f5f5f5;border-radius:4px;padding:6px;margin:0;max-height:80px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;">${prompt.slice(0,400).replace(/</g,'&lt;').replace(/>/g,'&gt;')}</pre></div>` : '';

      const code = p.code_snippet || '';
      const codeHtml = (code && step === 4) ? `<div style="margin-top:8px;"><div style="font-size:11px;font-weight:700;color:#555;margin-bottom:3px;">⚡ 生成中のコード（${(p.code_total||0).toLocaleString()}文字）</div><pre style="font-size:10px;color:#AAFFCC;background:#1C1C2E;border-radius:4px;padding:6px;margin:0;max-height:80px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;">${code.slice(-300).replace(/</g,'&lt;').replace(/>/g,'&gt;')}</pre></div>` : '';

      html += `<div style="padding:12px 0;border-bottom:1px solid var(--line);">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
          <div style="flex-shrink:0;width:36px;height:36px;border-radius:50%;background:${color};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;">${step}</div>
          <div style="flex:1;min-width:140px;">
            <div style="font-weight:700;font-size:14px;color:var(--navy);">${name}${lvl?`<span style="font-size:11px;font-weight:500;background:#e8eef8;color:#2E5FA3;padding:1px 7px;border-radius:20px;margin-left:6px;">${lvl}</span>`:''}${sid?`<span style="font-size:11px;color:var(--ink-soft);margin-left:4px;">ID:${sid}</span>`:''}</div>
            <div style="margin-top:5px;background:#e8eef8;border-radius:4px;height:5px;overflow:hidden;"><div style="height:5px;background:${color};width:${pct}%;transition:width .5s;"></div></div>
          </div>
          <div style="text-align:right;flex-shrink:0;">
            <div style="font-size:12px;font-weight:700;color:${color};">${label}</div>
            <div style="font-size:11px;color:var(--ink-soft);">${p.updated||''}</div>
            <a href="monitor.php?id=${p.session_id}" target="_blank" style="font-size:11px;color:var(--teal-deep);text-decoration:none;font-weight:700;">詳細 →</a>
          </div>
        </div>
        ${formHtml}${promptHtml}${codeHtml}
      </div>`;
    }
    el.innerHTML = html;
  } catch(e) {
    document.getElementById('progress-list').innerHTML = `<p style="color:#c0392b;font-size:13px;">取得エラー: ${e.message}</p>`;
  }
}

fetchProgress();
setInterval(fetchProgress, 5000);
</script>

<!-- プロンプト確認モーダル -->
<div id="prompt-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:14px;padding:28px;max-width:680px;width:90%;max-height:80vh;display:flex;flex-direction:column;gap:14px;box-shadow:0 20px 60px rgba(0,0,0,.25);">
    <div style="display:flex;align-items:center;justify-content:space-between;">
      <h2 style="font-size:17px;font-weight:800;color:var(--navy);margin:0;">送信されたプロンプト</h2>
      <button onclick="closePrompt()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--ink-soft);line-height:1;padding:0 4px;">×</button>
    </div>
    <textarea id="prompt-text" readonly style="flex:1;min-height:320px;padding:14px;border:1px solid var(--line);border-radius:10px;font-size:13px;font-family:monospace;line-height:1.7;resize:none;background:#f8fafa;color:var(--ink);"></textarea>
    <div style="display:flex;gap:8px;justify-content:flex-end;">
      <button id="copy-btn" onclick="copyPrompt()" class="btn btn-line btn-mini">コピー</button>
      <button onclick="closePrompt()" class="btn btn-sec btn-mini">閉じる</button>
    </div>
  </div>
</div>
<script>
function showPrompt(text) {
  document.getElementById('prompt-text').value = text;
  document.getElementById('prompt-modal').style.display = 'flex';
}
function closePrompt() {
  document.getElementById('prompt-modal').style.display = 'none';
}
function copyPrompt() {
  const ta = document.getElementById('prompt-text');
  ta.select();
  document.execCommand('copy');
  const btn = document.getElementById('copy-btn');
  btn.textContent = 'コピーしました！';
  setTimeout(() => btn.textContent = 'コピー', 1500);
}
document.getElementById('prompt-modal').addEventListener('click', function(e) {
  if (e.target === this) closePrompt();
});
</script>
</body></html>
