<?php require __DIR__ . '/lib.php';
if (!is_logged_in()) { header('Location: admin.php'); exit; }

$sessionId = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['id'] ?? '');
if (!$sessionId) {
    header('Location: admin.php');
    exit;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>参加者モニター | <?= e(EVENT_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=4">
<style>
.mon-wrap { max-width: 900px; margin: 0 auto; padding: 30px 20px 80px; }
.mon-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--line); }
.mon-head h1 { font-size: 20px; margin: 0; font-weight: 800; color: var(--navy); }
.mon-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
.live-dot { width: 8px; height: 8px; border-radius: 50%; background: #e74c3c; animation: blink 1s step-end infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }

.step-bar { display: flex; gap: 0; margin-bottom: 24px; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow); }
.step-item { flex: 1; padding: 10px 4px; text-align: center; font-size: 11px; background: #dce8f8; color: var(--navy); border-right: 1px solid #c0d4ee; transition: background .3s; }
.step-item:last-child { border-right: none; }
.step-item.done   { background: var(--teal); color: var(--navy); }
.step-item.active { background: var(--navy); color: #fff; font-weight: bold; }
.step-num { font-size: 14px; display: block; }

.panel { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 16px; overflow: hidden; }
.panel-head { background: #e6eeee; padding: 10px 16px; font-size: 13px; font-weight: 700; color: var(--navy); border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; }
.panel-body { padding: 16px; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
.info-item { }
.info-label { font-size: 11px; color: var(--ink-soft); font-weight: 700; letter-spacing: .06em; margin-bottom: 2px; }
.info-value { font-size: 14px; color: var(--navy); font-weight: 700; }
.form-table { width: 100%; border-collapse: collapse; }
.form-table tr { border-bottom: 1px solid var(--line); }
.form-table tr:last-child { border-bottom: none; }
.form-table td { padding: 8px 6px; font-size: 13px; }
.form-table td:first-child { color: var(--ink-soft); font-weight: 700; width: 160px; white-space: nowrap; font-size: 12px; }
.form-table td:last-child { color: var(--navy); }
.prompt-box { font-family: monospace; font-size: 12px; color: var(--ink); background: #f8fafa; border: 1px solid var(--line); border-radius: 8px; padding: 12px; white-space: pre-wrap; word-break: break-all; max-height: 300px; overflow-y: auto; line-height: 1.7; }
.code-box { font-family: monospace; font-size: 11px; color: #AAFFCC; background: #1C1C2E; border-radius: 8px; padding: 12px; white-space: pre-wrap; word-break: break-all; max-height: 360px; overflow-y: auto; line-height: 1.8; }
.stat-num { font-size: 28px; font-weight: 900; color: var(--navy); }
.stat-label { font-size: 12px; color: var(--ink-soft); margin-top: 2px; }
.stats-row { display: flex; gap: 16px; flex-wrap: wrap; }
.stat-box { background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 14px 18px; }
.updated-at { font-size: 11px; color: var(--ink-soft); }
.empty-msg { color: var(--ink-soft); font-size: 13px; font-style: italic; }
</style>
</head>
<body>
<div class="masthead"><div class="masthead-inner"><img src="assets/logo.png" alt="Logo"></div></div>

<div class="mon-wrap">
  <div class="mon-head">
    <div>
      <h1 id="mon-title">参加者モニター</h1>
      <div style="font-size:12px;color:var(--ink-soft);margin-top:4px;">セッションID: <?= e($sessionId) ?></div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <span class="mon-badge" style="background:#fdecea;color:#c0392b;">
        <span class="live-dot"></span> LIVE
      </span>
      <span class="updated-at" id="updated-at"></span>
      <a class="btn btn-sec btn-mini" href="admin.php">← 管理に戻る</a>
    </div>
  </div>

  <!-- ステップバー -->
  <div class="step-bar">
    <div class="step-item" id="s1"><span class="step-num">①</span>QRスキャン</div>
    <div class="step-item" id="s2"><span class="step-num">②</span>難易度選択</div>
    <div class="step-item" id="s3"><span class="step-num">③</span>入力中</div>
    <div class="step-item" id="s4"><span class="step-num">④</span>AI生成中</div>
    <div class="step-item" id="s5"><span class="step-num">⑤</span>確認・修正</div>
    <div class="step-item" id="s6"><span class="step-num">⑥</span>完成</div>
  </div>

  <!-- 基本情報 -->
  <div class="panel">
    <div class="panel-head">👤 基本情報</div>
    <div class="panel-body">
      <div class="info-grid">
        <div class="info-item"><div class="info-label">ニックネーム</div><div class="info-value" id="info-nickname">—</div></div>
        <div class="info-item"><div class="info-label">難易度</div><div class="info-value" id="info-level">—</div></div>
        <div class="info-item"><div class="info-label">スロットID</div><div class="info-value" id="info-slot">—</div></div>
        <div class="info-item"><div class="info-label">現在のステップ</div><div class="info-value" id="info-step">—</div></div>
        <div class="info-item"><div class="info-label">最終更新</div><div class="info-value" id="info-updated">—</div></div>
      </div>
    </div>
  </div>

  <!-- フォーム入力内容 -->
  <div class="panel" id="panel-form" style="display:none;">
    <div class="panel-head">📝 フォーム入力内容</div>
    <div class="panel-body">
      <table class="form-table" id="form-table"></table>
    </div>
  </div>

  <!-- 送信プロンプト -->
  <div class="panel" id="panel-prompt" style="display:none;">
    <div class="panel-head">
      📤 送信プロンプト
      <button onclick="copyPrompt()" class="btn btn-sec btn-mini">コピー</button>
    </div>
    <div class="panel-body">
      <div class="prompt-box" id="prompt-box"></div>
    </div>
  </div>

  <!-- コード生成 -->
  <div class="panel" id="panel-code" style="display:none;">
    <div class="panel-head">
      ⚡ 生成中のコード
      <div class="stats-row">
        <div class="stat-box" style="padding:6px 12px;">
          <div class="stat-num" id="code-chars">0</div>
          <div class="stat-label">生成文字数</div>
        </div>
      </div>
    </div>
    <div class="panel-body" style="padding:0;">
      <div class="code-box" id="code-box"></div>
    </div>
  </div>
</div>

<script>
const SESSION_ID = '<?= e($sessionId) ?>';
const PROGRESS_URL = '<?= rtrim(base_url(), '/') ?>/progress.php';

const FORM_LABELS = {
  nickname:'ニックネーム', sitename:'サイト名', catchcopy:'キャッチコピー',
  concept:'コンセプト', target:'ターゲット', target_need:'課題と期待',
  sitemap:'構成・サイトマップ', design_dir:'デザイン方向性', colors:'カラー',
  font_ref:'フォント・参考', avoid:'避ける書き方', must:'Must（必須）',
  message:'特別な指示・こだわり', title:'タイトル', theme:'テーマ',
  design:'デザインイメージ', contents:'コンテンツ', features:'機能・演出',
};

async function fetchData() {
  try {
    const res  = await fetch(PROGRESS_URL);
    const list = await res.json();
    const p = list.find(x => x.session_id === SESSION_ID);
    if (!p) return;

    // タイトル
    document.getElementById('mon-title').textContent =
      (p.nickname || '名前未入力') + ' のモニター';

    // ステップバー
    const step = parseInt(p.step) || 0;
    for (let i = 1; i <= 6; i++) {
      const el = document.getElementById('s' + i);
      el.classList.remove('done', 'active');
      if (i < step) el.classList.add('done');
      if (i === step) el.classList.add('active');
    }

    // 基本情報
    document.getElementById('info-nickname').textContent = p.nickname || '未入力';
    document.getElementById('info-level').textContent    = p.level_label || '—';
    document.getElementById('info-slot').textContent     = p.slot_id || '—';
    document.getElementById('info-step').textContent     = p.step_label || '—';
    document.getElementById('info-updated').textContent  = p.updated || '—';
    document.getElementById('updated-at').textContent    = '最終更新: ' + new Date().toLocaleTimeString('ja-JP');

    // フォーム入力
    if (p.form_data && typeof p.form_data === 'object') {
      const rows = Object.entries(p.form_data)
        .filter(([k, v]) => v && !k.endsWith('_free'))
        .map(([k, v]) => {
          const label = FORM_LABELS[k] || k;
          const val   = Array.isArray(v) ? v.join('・') : String(v);
          return `<tr><td>${label}</td><td>${val.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</td></tr>`;
        }).join('');
      if (rows) {
        document.getElementById('form-table').innerHTML = rows;
        document.getElementById('panel-form').style.display = '';
      }
    }

    // プロンプト
    if (p.prompt) {
      document.getElementById('prompt-box').textContent = p.prompt;
      document.getElementById('panel-prompt').style.display = '';
    }

    // コード生成
    if (p.code_snippet) {
      document.getElementById('code-box').textContent  = p.code_snippet;
      document.getElementById('code-chars').textContent = (p.code_total || 0).toLocaleString();
      document.getElementById('panel-code').style.display = '';
      // 最下部にスクロール
      const cb = document.getElementById('code-box');
      cb.scrollTop = cb.scrollHeight;
    }

  } catch(e) {
    console.error(e);
  }
}

function copyPrompt() {
  const text = document.getElementById('prompt-box').textContent;
  navigator.clipboard.writeText(text).then(() => {
    event.target.textContent = 'コピー済！';
    setTimeout(() => event.target.textContent = 'コピー', 1500);
  });
}

fetchData();
setInterval(fetchData, 3000); // 3秒ごとに更新
</script>
</body>
</html>
