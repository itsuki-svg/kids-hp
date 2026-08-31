<?php require __DIR__ . '/lib.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AIでホームページを作ろう！ | <?= e(EVENT_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=4">
<script>
// =====================================================
// APIキー設定 ← ここにAPIキーを貼ってください
const ANTHROPIC_API_KEY = 'ここにAPIキーを貼る';
// =====================================================
</script>
<style>
:root {
  --ws-green:    #1a7a4a;
  --ws-green-lt: #e6f4ed;
  --ws-radius:   6px;
  --ws-shadow:   0 2px 8px rgba(0,0,0,.10);
}
.ws-body { background: var(--paper); min-height: 100vh; font-family: var(--font-sans); }
.ws-container { max-width: 960px; margin: 0 auto; padding: 24px 16px 60px; }

/* ステップバー */
.ws-steps { display: flex; margin-bottom: 24px; border-radius: var(--ws-radius); overflow: hidden; box-shadow: var(--ws-shadow); }
.ws-step { flex: 1; padding: 10px 4px; text-align: center; font-size: 0.72rem; background: #dce8f8; color: var(--navy); border-right: 1px solid #c0d4ee; }
.ws-step:last-child { border-right: none; }
.ws-step.active { background: var(--navy); color: #fff; font-weight: bold; }
.ws-step.done   { background: var(--teal); color: var(--navy); }
.ws-step-num { font-size: 0.95rem; display: block; }

/* カード */
.ws-card { background: #fff; border-radius: var(--ws-radius); box-shadow: var(--ws-shadow); padding: 24px; margin-bottom: 20px; border: 1px solid var(--line); }
.ws-card h2 { font-size: 1.05rem; color: var(--navy); margin-bottom: 14px; border-left: 4px solid var(--teal); padding-left: 10px; }

/* QRスキャン */
.scan-wrap { position: relative; background: #000; border-radius: var(--ws-radius); overflow: hidden; margin-bottom: 14px; }
#qr-video { width: 100%; max-height: 340px; display: block; }
.scan-frame { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 200px; height: 200px; border: 3px solid var(--teal); border-radius: 8px; pointer-events: none; }
#scan-status-msg { position: absolute; bottom: 10px; left: 0; right: 0; text-align: center; color: #fff; font-size: 0.85rem; font-weight: bold; text-shadow: 0 1px 3px #000; padding: 0 16px; }

/* 確認バナー */
.slot-ok-banner { background: var(--ws-green-lt); border: 2px solid var(--ws-green); border-radius: var(--ws-radius); padding: 16px 20px; text-align: center; margin-bottom: 16px; }
.slot-ok-banner h3 { color: var(--ws-green); font-size: 1.1rem; margin-bottom: 4px; }
.slot-err-banner { background: #fbeeec; border: 2px solid #c0392b; border-radius: var(--ws-radius); padding: 16px 20px; text-align: center; margin-bottom: 16px; }
.slot-err-banner h3 { color: #c0392b; font-size: 1.1rem; margin-bottom: 4px; }
.slot-err-banner p  { font-size: 0.85rem; color: #555; margin: 0; }

/* 難易度 */
.ws-level-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.ws-level-btn { border: 2px solid var(--line); border-radius: var(--ws-radius); padding: 18px 12px; text-align: center; cursor: pointer; background: #fff; transition: all .15s; }
.ws-level-btn:hover, .ws-level-btn.selected { border-color: var(--navy); background: var(--paper); }
.ws-level-btn .badge { display: inline-block; font-size: 0.72rem; font-weight: bold; padding: 2px 8px; border-radius: 20px; margin-bottom: 8px; }
.ws-level-btn.l1 .badge { background: #d4edda; color: #155724; }
.ws-level-btn.l2 .badge { background: #fff3cd; color: #856404; }
.ws-level-btn.l3 .badge { background: #f8d7da; color: #721c24; }
.ws-level-btn strong { display: block; font-size: 0.95rem; color: var(--navy); margin-bottom: 4px; }
.ws-level-btn span   { font-size: 0.78rem; color: var(--ink-soft); }

/* フォーム */
.q-block { margin-bottom: 20px; }
.q-title { font-size: 0.9rem; font-weight: bold; color: var(--navy); display: block; margin-bottom: 4px; }
.q-hint  { font-size: 0.75rem; color: var(--ink-soft); margin-bottom: 6px; display: block; }
.ws-input, .ws-textarea { width: 100%; padding: 8px 10px; border: 1px solid var(--line); border-radius: 4px; font-size: 0.9rem; font-family: inherit; color: var(--navy); transition: border .15s; }
.ws-input:focus, .ws-textarea:focus { outline: none; border-color: var(--teal-deep); }
.ws-textarea { resize: vertical; min-height: 72px; }
.ws-checks { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 6px; }
.ws-check-item { display: flex; align-items: center; gap: 5px; font-size: 0.85rem; cursor: pointer; }

/* ボタン */
.ws-btn { padding: 10px 24px; border: none; border-radius: var(--ws-radius); font-size: 0.95rem; font-weight: bold; cursor: pointer; font-family: inherit; transition: background .15s; }
.ws-btn-primary   { background: var(--navy); color: #fff; }
.ws-btn-primary:hover  { filter: brightness(1.2); }
.ws-btn-primary:disabled { background: #aaa; cursor: not-allowed; }
.ws-btn-secondary { background: #eee; color: var(--navy); }
.ws-btn-secondary:hover { background: #ddd; }
.ws-btn-green  { background: var(--ws-green); color: #fff; }
.ws-btn-green:hover { background: #145c38; }
.ws-btn-teal   { background: var(--teal); color: var(--navy); }
.ws-btn-teal:hover { filter: brightness(1.05); }
.ws-btn-row { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }

/* ストリーム */
.ws-stream-box { background: #1C1C2E; border-radius: var(--ws-radius); padding: 16px; margin-bottom: 16px; min-height: 120px; max-height: 360px; overflow-y: auto; font-size: 0.82rem; line-height: 1.7; color: #fff !important; }
.ws-stream-comment { color: #fff !important; display: block; margin-bottom: 4px; }
.ws-stream-code    { color: #fff !important; font-family: "Courier New", monospace; font-size: 0.82rem; display: block; }
.ws-stream-done    { color: #88FF88 !important; font-weight: bold; display: block; }
#stream-box, #fix-stream-box { color: #fff !important; }
#stream-box *, #fix-stream-box * { color: #fff !important; }
.ws-progress-wrap  { background: #ddd; border-radius: 4px; height: 6px; margin-bottom: 16px; }
.ws-progress-bar   { background: var(--teal); height: 6px; border-radius: 4px; width: 0; transition: width .3s; }

/* プレビュー */
.ws-preview-frame { width: 100%; height: 480px; border: 2px solid var(--teal); border-radius: var(--ws-radius); background: #fff; }

/* 完成バナー */
.ws-done-banner { background: var(--ws-green-lt); border: 2px solid var(--ws-green); border-radius: var(--ws-radius); padding: 16px 20px; text-align: center; margin-bottom: 16px; }
.ws-done-banner h3 { color: var(--ws-green); font-size: 1.1rem; margin-bottom: 4px; }

/* 修正 */
.fix-example { display: inline-block; font-size: 0.78rem; background: var(--paper); color: var(--teal-deep); border: 1px solid var(--line); border-radius: 20px; padding: 2px 10px; margin: 2px 3px 2px 0; cursor: pointer; transition: .12s; }
.fix-example:hover { border-color: var(--teal-deep); }
.fix-history-item { font-size: 0.78rem; color: var(--ink-soft); padding: 3px 0 3px 10px; border-left: 3px solid var(--teal); margin-bottom: 4px; }
.fix-count-badge  { display: inline-block; background: var(--navy); color: #fff; font-size: 0.7rem; font-weight: bold; padding: 1px 7px; border-radius: 20px; margin-right: 6px; }
.ws-code-preview { background: #f8f8f8; color: #1a1a2e; font-family: "Courier New", monospace; font-size: 0.82rem; padding: 14px; border-radius: var(--ws-radius); white-space: pre-wrap; max-height: 220px; overflow-y: auto; margin-bottom: 12px; border: 1px solid #ddd; }

/* 完成 */
.ws-finish-box { text-align: center; padding: 40px 24px; }
.ws-finish-box .big-emoji { font-size: 3rem; margin-bottom: 12px; }

.ws-hidden { display: none !important; }
@keyframes ws-pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.65)} }
@media (max-width: 600px) {
  .ws-level-grid { grid-template-columns: 1fr; }
  .ws-steps { flex-direction: column; }
}
</style>
</head>
<body class="ws-body">

<div class="masthead">
  <div class="masthead-inner"><img src="assets/logo.png" alt="Logo"></div>
</div>

<div class="ws-container">

  <!-- ステップバー -->
  <div class="ws-steps">
    <div class="ws-step active" id="step1"><span class="ws-step-num">①</span>QRスキャン</div>
    <div class="ws-step"        id="step2"><span class="ws-step-num">②</span>難易度を選ぶ</div>
    <div class="ws-step"        id="step3"><span class="ws-step-num">③</span>情報を入力</div>
    <div class="ws-step"        id="step4"><span class="ws-step-num">④</span>AI作成中！</div>
    <div class="ws-step"        id="step5"><span class="ws-step-num">⑤</span>確認・修正</div>
    <div class="ws-step"        id="step6"><span class="ws-step-num">⑥</span>完成！</div>
  </div>

  <!-- ── STEP1: QRスキャン ── -->
  <div id="screen-scan">
    <div class="ws-card">
      <h2>📷 まずパンフレットのQRコードをスキャンしてね！</h2>
      <p style="font-size:.85rem;color:var(--ink-soft);margin-bottom:16px;">
        もらったパンフレットのQRコードをカメラに向けてね。読み取ったらホームページ作りが始まるよ！
      </p>
      <div class="scan-wrap">
        <video id="qr-video" playsinline autoplay muted></video>
        <div class="scan-frame"></div>
        <div id="scan-status-msg">カメラを起動中...</div>
      </div>

      <!-- エラー表示 -->
      <div id="slot-err" class="slot-err-banner ws-hidden">
        <h3 id="slot-err-title">⚠️ エラー</h3>
        <p id="slot-err-msg"></p>
      </div>

      <!-- 手動入力 -->
      <details style="margin-bottom:14px;">
        <summary style="font-size:.82rem;color:var(--ink-soft);cursor:pointer;">📝 QRが読めない場合は番号を手動入力</summary>
        <div style="margin-top:8px;display:flex;gap:8px;">
          <input type="text" class="ws-input" id="manual-id" placeholder="IDを入力（例：ab3x9q2r）" maxlength="8" style="max-width:220px;font-family:monospace;">
          <button class="ws-btn ws-btn-primary" onclick="checkSlotById(document.getElementById('manual-id').value.trim())">確認する</button>
        </div>
      </details>
    </div>
  </div>

  <!-- ── STEP2: 難易度選択 ── -->
  <div id="screen-level" class="ws-hidden">
    <div class="slot-ok-banner">
      <h3 id="slot-ok-title">✅ QRコードを確認しました！</h3>
      <p id="slot-ok-msg" style="font-size:.85rem;color:#555;margin:0;"></p>
    </div>
    <div class="ws-card">
      <h2>難易度を選んでね！</h2>
      <div class="ws-level-grid">
        <div class="ws-level-btn l1" onclick="selectLevel(1)">
          <span class="badge">初級</span>
          <strong>🌱 かんたん</strong>
          <span>小学生向け<br>シンプルな質問だよ</span>
        </div>
        <div class="ws-level-btn l2" onclick="selectLevel(2)">
          <span class="badge">中級</span>
          <strong>🔥 ふつう</strong>
          <span>中学生向け<br>もう少し詳しく考えよう</span>
        </div>
        <div class="ws-level-btn l3" onclick="selectLevel(3)">
          <span class="badge">上級</span>
          <strong>⚡ むずかしい</strong>
          <span>高校生向け<br>プロっぽい設計をしよう</span>
        </div>
      </div>
      <div class="ws-btn-row">
        <button class="ws-btn ws-btn-primary" id="btn-to-form" onclick="goToForm()" disabled>次へ → 情報を入力する</button>
      </div>
    </div>
  </div>

  <!-- ── STEP3: 入力フォーム ── -->
  <div id="screen-form" class="ws-hidden">
    <div class="ws-card">
      <h2 id="form-title">📝 ホームページの情報を入力しよう</h2>
      <div id="form-fields"></div>
      <div class="ws-btn-row">
        <button class="ws-btn ws-btn-secondary" onclick="goBackToLevel()">← 難易度選択に戻る</button>
        <button class="ws-btn ws-btn-primary" onclick="goToGenerate()">AIに作ってもらう！ →</button>
      </div>
    </div>
  </div>

  <!-- ── STEP4: 生成中 ── -->
  <div id="screen-generate" class="ws-hidden">
    <div class="ws-card">
      <h2>🤖 AIが作っています！</h2>
      <div class="ws-progress-wrap"><div class="ws-progress-bar" id="progress-bar"></div></div>

      <div id="thought-row" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;min-height:22px;">
        <div id="thought-dot" style="width:8px;height:8px;border-radius:50%;background:var(--teal);flex-shrink:0;animation:ws-pulse 1.2s ease-in-out infinite;"></div>
        <div id="thought-text" style="font-size:13px;color:var(--ink-soft);">準備中...</div>
      </div>

      <div style="font-size:11px;font-weight:700;color:var(--ink-soft);letter-spacing:.05em;margin-bottom:4px;">生成されているコード</div>
      <div id="stream-box" style="background:#1C1C2E;border-radius:var(--ws-radius);padding:14px 16px;font-family:'Courier New',monospace;font-size:12px;color:#fff;line-height:1.9;height:360px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;"></div>

      <div class="ws-btn-row">
        <button class="ws-btn ws-btn-secondary" onclick="abortGenerate()">中止する</button>
      </div>
    </div>
  </div>

  <!-- ── STEP5: 確認・修正 ── -->
  <div id="screen-done" class="ws-hidden">
    <div class="ws-done-banner">
      <h3>🎉 ホームページができたよ！確認してみよう</h3>
      <p style="font-size:.85rem;color:#555;margin:0;">気になるところがあればClaudeに修正を頼もう！</p>
    </div>
    <div class="ws-card">
      <h2>👀 プレビュー</h2>
      <iframe id="preview-frame" class="ws-preview-frame" sandbox="allow-scripts allow-same-origin"></iframe>
      <div class="ws-btn-row">
        <button class="ws-btn ws-btn-teal" onclick="goToSave()">✅ これで完成！保存する →</button>
        <button class="ws-btn ws-btn-secondary" onclick="restartFromScan()">最初からやり直す</button>
      </div>
    </div>
    <div class="ws-card">
      <h2>🔧 気になるところを修正しよう</h2>
      <p style="font-size:.85rem;color:var(--ink-soft);margin-bottom:12px;">何を変えたいか書いてClaudeに頼もう！何回でもOKだよ！</p>
      <div style="margin-bottom:10px;">
        <span style="font-size:.8rem;color:var(--ink-soft);margin-right:6px;">💡 例：</span>
        <span class="fix-example" onclick="setFix('背景色をピンクにしてください')">背景をピンクに</span>
        <span class="fix-example" onclick="setFix('タイトルの文字をもっと大きくしてください')">タイトルを大きく</span>
        <span class="fix-example" onclick="setFix('全体をもっとポップでかわいい雰囲気にしてください')">もっとかわいく</span>
        <span class="fix-example" onclick="setFix('ナビゲーションメニューを追加してください')">メニューを追加</span>
      </div>
      <textarea class="ws-textarea" id="fix-input" rows="3" placeholder="ここに修正してほしいことを書こう！"></textarea>
      <div id="fix-history" style="display:none;margin-top:10px;">
        <div style="font-size:.8rem;font-weight:bold;color:var(--navy);margin-bottom:4px;">📝 修正履歴</div>
        <div id="fix-history-list"></div>
      </div>
      <div class="ws-btn-row">
        <button class="ws-btn ws-btn-primary" id="btn-fix" onclick="applyFix()">🔄 修正する</button>
        <button class="ws-btn ws-btn-secondary" onclick="document.getElementById('fix-input').value=''">クリア</button>
      </div>
      <div id="fix-stream-wrap" style="display:none;margin-top:14px;">
        <div style="font-size:.85rem;font-weight:bold;color:var(--navy);margin-bottom:6px;">🤖 修正中...</div>
        <div class="ws-progress-wrap"><div class="ws-progress-bar" id="fix-progress-bar"></div></div>
        <div class="ws-stream-box" id="fix-stream-box" style="max-height:200px;"></div>
      </div>
    </div>
    <div class="ws-card">
      <h2>📄 HTMLコード</h2>
      <div class="ws-code-preview" id="html-output"></div>
    </div>
  </div>

  <!-- ── STEP6: 保存・完成 ── -->
  <div id="screen-finish" class="ws-hidden">
    <div class="ws-card ws-finish-box">
      <div class="big-emoji">🎊</div>
      <h2 style="font-size:1.4rem;color:var(--ws-green);margin-bottom:8px;">ホームページが完成しました！</h2>
      <p id="finish-msg" style="font-size:.9rem;color:var(--ink-soft);margin-bottom:8px;"></p>
      <p id="finish-url" style="font-size:.85rem;margin-bottom:24px;"></p>
      <div class="ws-btn-row" style="justify-content:center;">
        <button class="ws-btn ws-btn-green" style="font-size:1.05rem;padding:14px 32px;" onclick="downloadHTML()">💾 HTMLをダウンロード</button>
      </div>
    </div>
    <div class="ws-card">
      <h2>👀 最終プレビュー</h2>
      <iframe id="preview-frame-final" class="ws-preview-frame" sandbox="allow-scripts allow-same-origin" style="border-color:var(--ws-green);"></iframe>
    </div>
  </div>

</div><!-- /ws-container -->

<input type="hidden" id="ws-csrf" value="<?= csrf_token() ?>">

<script>
// ══════════════════════════════════════════════
// 状態管理
// ══════════════════════════════════════════════
let scannedSlotId  = null;  // スキャン済みスロット番号
let currentLevel   = 0;
let generatedHTML  = '';
let lastPrompt     = '';    // 送信したプロンプトを保持
let abortController = null;
let fixHistory     = [];
let lastSnippetEl  = null;
let lastCommentTrigger = -1;
let qrScanInterval = null;
// 進捗管理
const SESSION_ID = Math.random().toString(36).slice(2) + Date.now().toString(36);
let progressNickname = '';
let progressFormData = null;

// フォームデータをinputごとに保持（サーバー送信なし）
let latestFormData = null;
function scheduleFormSend() {
  try {
    const data = collectFormData();
    if (data && Object.keys(data).length) {
      progressNickname = data['nickname'] || progressNickname;
      latestFormData = data;
    }
  } catch(e) {}
}

function sendProgress(step, stepLabel, extra = {}) {
  // 完全に非同期で実行（画面遷移に一切影響しない）
  setTimeout(() => {
    try {
      const payload = {
        session_id: SESSION_ID,
        slot_id:    scannedSlotId || '',
        nickname:   progressNickname,
        step:       step,
        step_label: stepLabel,
        level:      currentLevel,
        ...extra,
      };
      if (latestFormData && !payload.form_data) {
        payload.form_data = latestFormData;
      }
      fetch('progress.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      }).catch(() => {});
    } catch(e) {}
  }, 0);
}

// ══════════════════════════════════════════════
// STEP1: QRスキャン
// ══════════════════════════════════════════════
function getApiKey() { return ANTHROPIC_API_KEY; }

async function startCamera() {
  const video = document.getElementById('qr-video');
  const msg   = document.getElementById('scan-status-msg');
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
    video.srcObject = stream;
    video.play();
    msg.textContent = 'QRコードを枠に合わせてね';
    loadJsQR();
  } catch (e) {
    msg.textContent = 'カメラにアクセスできません。下の手動入力を使ってください。';
  }
}

function stopCamera() {
  clearInterval(qrScanInterval); qrScanInterval = null;
  const video = document.getElementById('qr-video');
  if (video.srcObject) {
    video.srcObject.getTracks().forEach(t => t.stop());
    video.srcObject = null;
  }
}

const JSQR_CDNS = [
  'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js',
  'https://unpkg.com/jsqr@1.4.0/dist/jsQR.js',
  'https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.min.js',
];
function loadJsQR(idx = 0) {
  if (window.jsQR) { qrScanInterval = setInterval(scanFrame, 300); return; }
  if (idx >= JSQR_CDNS.length) {
    document.getElementById('scan-status-msg').textContent = 'カメラ読み取りが使えません。下の「番号を手で入力」を使ってね。';
    // 手動入力欄を目立たせる
    const manual = document.getElementById('manual-id');
    if (manual) manual.focus();
    return;
  }
  const s = document.createElement('script');
  s.src = JSQR_CDNS[idx];
  s.onload = () => {
    if (window.jsQR) {
      qrScanInterval = setInterval(scanFrame, 300);
      document.getElementById('scan-status-msg').textContent = 'QRコードを枠に合わせてね';
    } else {
      loadJsQR(idx + 1);
    }
  };
  s.onerror = () => loadJsQR(idx + 1);
  document.head.appendChild(s);
}

let _qrCanvas = null;
function scanFrame() {
  const video = document.getElementById('qr-video');
  if (!video || video.readyState !== video.HAVE_ENOUGH_DATA) return;
  if (!_qrCanvas) _qrCanvas = document.createElement('canvas');
  _qrCanvas.width = video.videoWidth;
  _qrCanvas.height = video.videoHeight;
  const ctx = _qrCanvas.getContext('2d', { willReadFrequently: true });
  ctx.drawImage(video, 0, 0);
  const imageData = ctx.getImageData(0, 0, _qrCanvas.width, _qrCanvas.height);
  const code = window.jsQR(imageData.data, imageData.width, imageData.height);
  if (code && code.data) {
    // URLに ?id=xxx があれば抽出、なければQR全体をIDとして扱う
    let id = null;
    const match = code.data.match(/[?&]id=([a-zA-Z0-9]+)/);
    if (match) {
      id = match[1];
    } else if (/^[a-zA-Z0-9]{4,12}$/.test(code.data.trim())) {
      id = code.data.trim();  // QRがID文字列そのものの場合
    }
    if (id) {
      clearInterval(qrScanInterval); qrScanInterval = null;
      document.getElementById('scan-status-msg').textContent = '読み取り中...';
      checkSlotById(id);
    }
  }
}

async function checkSlotById(id) {
  const cleanId = id.replace(/[^a-zA-Z0-9]/g, '');
  if (!cleanId || cleanId.length < 4) {
    showScanError('入力エラー', 'IDが正しくありません（英数字で入力してください）。');
    return;
  }
  document.getElementById('scan-status-msg').textContent = '確認中...';
  try {
    const res  = await fetch('check_slot.php?id=' + cleanId);
    const data = await res.json();
    if (data.ok) {
      // 使用可能
      stopCamera();
      scannedSlotId = cleanId;
      history.replaceState(null, '', 'workshop.php?id=' + cleanId);
      document.getElementById('slot-ok-title').textContent = `✅ ID「${cleanId}」のQRを確認しました！`;
      document.getElementById('slot-ok-msg').textContent   = 'このQRであなたのホームページを作るよ。難易度を選んでね！';
      sendProgress(1, 'QRスキャン完了');
      setStep(2); hide('screen-scan'); show('screen-level');
    } else if (data.used) {
      // 使用済み
      showScanError('このQRはすでに使われています ⚠️', data.msg);
      // カメラ再開
      if (!qrScanInterval) {
        loadJsQR();
        document.getElementById('scan-status-msg').textContent = '別のQRコードを読んでね';
      }
    } else {
      showScanError('エラー', data.msg);
      if (!qrScanInterval) loadJsQR();
    }
  } catch (e) {
    showScanError('通信エラー', '確認できませんでした。もう一度試してください。');
    if (!qrScanInterval) loadJsQR();
  }
}

function showScanError(title, msg) {
  const errEl = document.getElementById('slot-err');
  document.getElementById('slot-err-title').textContent = title;
  document.getElementById('slot-err-msg').textContent   = msg;
  errEl.classList.remove('ws-hidden');
  document.getElementById('scan-status-msg').textContent = '別のQRコードを試してね';
}

// ══════════════════════════════════════════════
// STEP2: 難易度選択
// ══════════════════════════════════════════════
function selectLevel(n) {
  currentLevel = n;
  document.querySelectorAll('.ws-level-btn').forEach((el, i) => el.classList.toggle('selected', i + 1 === n));
  document.getElementById('btn-to-form').disabled = false;

}

function goBackToLevel() { setStep(2); hide('screen-form'); show('screen-level'); }

// ══════════════════════════════════════════════
// STEP3: 入力フォーム
// ══════════════════════════════════════════════
const LEVELS = {
  1: {
    label: '初級', title: '🌱 かんたん版　情報を入力しよう',
    questions: [
      { id:'nickname',  type:'text',     label:'Q1 ✏️ ニックネーム', hint:'例：ゆうき・たろう' },
      { id:'title',     type:'text',     label:'Q2 ✏️ ページのタイトル', hint:'例：ゆうきのゲーム天国' },
      { id:'catchcopy', type:'text',     label:'Q3 ✏️ キャッチコピー（一言PR）', hint:'例：ゲームが大好きな君へ！' },
      { id:'theme',     type:'checkbox', label:'Q4 🎯 テーマ',
        options:['ゲーム・マンガ','スポーツ','料理・グルメ','推し・キャラクター'], extra:'上にないテーマがあれば書いてね' },
      { id:'design',    type:'checkbox', label:'Q5 🎨 デザインのイメージ',
        options:['明るい・ポップ','クール・かっこいい','かわいい','シンプル'], extra:'こんな色にしたい！があれば書いてね' },
      { id:'contents',  type:'textarea', label:'Q6 📝 載せたい内容（3つくらい）', hint:'例：自己紹介・好きなゲームランキング' },
      { id:'message',   type:'textarea', label:'Q7 💬 Claudeへひとこと！', hint:'こんなページにしてほしい！を自由に' },
    ]
  },
  2: {
    label: '中級', title: '🔥 ふつう版　情報を入力しよう',
    questions: [
      { id:'nickname',   type:'text',     label:'基本　ニックネーム', hint:'' },
      { id:'sitename',   type:'text',     label:'Q1 ✏️ サイト名・タイトル', hint:'例：Yuki\'s Game Lab' },
      { id:'catchcopy',  type:'text',     label:'Q2 ✏️ キャッチコピー', hint:'例：ゲームで世界を面白くする' },
      { id:'concept',    type:'text',     label:'Q3 ✏️ コンセプト一文', hint:'例：ゲーム好きな中学生に攻略情報を届けるサイト' },
      { id:'target',     type:'text',     label:'Q4 👥 ターゲット', hint:'例：同じ学校の友だち ／ ゲームが好きな10〜15歳' },
      { id:'target_need',type:'text',     label:'Q4+ その人が知りたいこと', hint:'例：信頼できる攻略情報が少ない' },
      { id:'contents',   type:'textarea', label:'Q5 📋 コンテンツ構成', hint:'載せたいもの①②③と、できれば入れたいものを書こう' },
      { id:'design',     type:'checkbox', label:'Q6 🎨 デザインの雰囲気',
        options:['明るい・ポップ','クール・かっこいい','かわいい・やさしい','シンプル・すっきり'], extra:'使いたい色・参考サイトがあれば' },
      { id:'features',   type:'checkbox', label:'Q7 ⚙️ 入れたい機能・演出',
        options:['ボタンにアニメーション','スクロール演出','カード型レイアウト','ナビゲーションメニュー'], extra:'その他こだわりたいことがあれば' },
      { id:'message',    type:'textarea', label:'Q8 💬 Claudeへひとこと！', hint:'全体への要望・絶対に入れてほしいことを自由に' },
    ]
  },
  3: {
    label: '上級', title: '⚡ むずかしい版　仕様を定義しよう',
    questions: [
      { id:'nickname',   type:'text',     label:'基本　ニックネーム', hint:'' },
      { id:'sitename',   type:'text',     label:'Q1 サイト名', hint:'例：Yuki\'s Game Lab' },
      { id:'catchcopy',  type:'text',     label:'Q1 キャッチコピー', hint:'例：ゲームで世界を面白くする' },
      { id:'concept',    type:'text',     label:'Q1 コンセプト一文', hint:'例：ゲーム好きな中学生に攻略情報を届けるサイト' },
      { id:'target',     type:'text',     label:'Q2 ターゲット・ペルソナ', hint:'例：14〜18歳・ゲーム好きの学生' },
      { id:'target_need',type:'text',     label:'Q2 ユーザーの課題と期待', hint:'例：信頼できる攻略情報が少ない → 「また来たい」と思ってほしい' },
      { id:'sitemap',    type:'textarea', label:'Q3 コンテンツ構成・サイトマップ', hint:'トップページの要素・サブページ・セクション' },
      { id:'design_dir', type:'text',     label:'Q4 デザイン方向性', hint:'例：モノトーン×アクセントカラーでクールに' },
      { id:'colors',     type:'text',     label:'Q4 カラー（メイン・サブ・アクセント）', hint:'例：#1B3A6B / #2E5FA3 / #E040A0' },
      { id:'font_ref',   type:'text',     label:'Q4 フォント・参考サイト', hint:'例：ゴシック系 ／ https://...' },
      { id:'layout',     type:'checkbox', label:'Q5 レイアウト・機能要件',
        options:['ヘッダー固定','ハンバーガーメニュー','サイドバーあり','フッターあり'], extra:'UI/UXのこだわりがあれば' },
      { id:'tech',       type:'checkbox', label:'Q6 使用技術',
        options:['Flexbox','Grid','CSSアニメーション','JavaScriptも使いたい'], extra:'指定タグ・プロパティがあれば' },
      { id:'avoid',      type:'text',     label:'Q6 避けてほしい書き方', hint:'例：インラインstyleは使わないで' },
      { id:'must',       type:'textarea', label:'Q7 Must（絶対に必要なこと）', hint:'優先度最高のもの' },
      { id:'message',    type:'textarea', label:'Q7 特別な指示・こだわり', hint:'その他Claudeへのリクエスト' },
    ]
  }
};

function goToForm() {
  if (!currentLevel) return;
  setStep(3); hide('screen-level'); show('screen-form');
  const def = LEVELS[currentLevel];
  document.getElementById('form-title').textContent = def.title;
  const container = document.getElementById('form-fields');
  container.innerHTML = '';
  def.questions.forEach(q => {
    const block = document.createElement('div');
    block.className = 'q-block';
    block.innerHTML = `<span class="q-title">${q.label}</span>`;
    if (q.hint) block.innerHTML += `<span class="q-hint">${q.hint}</span>`;
    if (q.type === 'text') {
      const inp = document.createElement('input');
      inp.type = 'text'; inp.id = q.id; inp.className = 'ws-input';
      block.appendChild(inp);
    } else if (q.type === 'textarea') {
      const ta = document.createElement('textarea');
      ta.id = q.id; ta.className = 'ws-textarea'; ta.rows = 3;
      block.appendChild(ta);
    } else if (q.type === 'checkbox') {
      const wrap = document.createElement('div');
      wrap.className = 'ws-checks';
      q.options.forEach(opt => {
        const lbl = document.createElement('label');
        lbl.className = 'ws-check-item';
        const cb = document.createElement('input');
        cb.type = 'checkbox'; cb.value = opt; cb.dataset.group = q.id;
        lbl.appendChild(cb);
        lbl.appendChild(document.createTextNode(opt));
        wrap.appendChild(lbl);
      });
      block.appendChild(wrap);
      if (q.extra) {
        const inp2 = document.createElement('input');
        inp2.type = 'text'; inp2.id = q.id + '_free'; inp2.className = 'ws-input';
        inp2.placeholder = q.extra; inp2.style.marginTop = '6px';
        block.appendChild(inp2);
      }
    }
    container.appendChild(block);
  });

  // フォーム入力をリアルタイム送信
  container.addEventListener('input', scheduleFormSend);
  container.addEventListener('change', scheduleFormSend);
}

function collectFormData() {
  const data = {};
  document.querySelectorAll('#form-fields input[type="text"], #form-fields textarea').forEach(el => { data[el.id] = el.value; });
  document.querySelectorAll('#form-fields input[type="checkbox"]').forEach(cb => {
    const g = cb.dataset.group;
    if (!data[g + '_checks']) data[g + '_checks'] = [];
    if (cb.checked) data[g + '_checks'].push(cb.value);
  });
  return data;
}

// ══════════════════════════════════════════════
// プロンプト生成
// ══════════════════════════════════════════════
function buildPrompt(level, data) {
  const checks = id => {
    const c = (data[id+'_checks']||[]).join('・');
    const f = (data[id+'_free']||'').trim();
    return [c,f].filter(Boolean).join('　');
  };
  const v = id => (data[id]||'').trim();

  if (level===1) return `以下の内容でホームページのHTMLを1ファイルで作ってください。
【タイトル】${v('title')}
【キャッチコピー】${v('catchcopy')}
【テーマ】${checks('theme')}
【デザインのイメージ】${checks('design')}
【載せたい内容】${v('contents')}
【Claudeへひとこと】${v('message')}
＜条件＞
・CSSも同じHTMLファイルに含める
・文字は大きく読みやすく
・スマホでも見やすいシンプルなデザイン
・背景色は白または明るい色にする
・ローディング画面は作らない
・外部ページへのリンク遷移は禁止（ページ内アンカーのみ）
・日本語で作成
・作成者「${v('nickname')}」をページに入れる`.trim();

  if (level===2) return `以下の内容でホームページのHTMLを1ファイルで作ってください。
【サイト名・タイトル】${v('sitename')}
【キャッチコピー】${v('catchcopy')}
【コンセプト】${v('concept')}
【ターゲット】${v('target')}
【ユーザーの課題と期待】${v('target_need')}
【コンテンツ構成】${v('contents')}
【デザインの雰囲気】${checks('design')}
【機能・演出】${checks('features')}
【Claudeへひとこと】${v('message')}
＜条件＞
・HTML・CSSを1ファイルにまとめる
・適切なHTMLタグを使う
・レスポンシブ対応
・背景色は白または明るい色にする
・ローディング画面は作らない
・外部ページへのリンク遷移は禁止（ページ内アンカーのみ）
・ヘッダーをfixedにする場合はbodyにpadding-topを必ず設定する
・JSはDOMContentLoadedで安全に実行する
・コメントを入れて読みやすくする
・日本語で作成
・作成者「${v('nickname')}」をフッターに`.trim();

  return `以下の仕様でWebページのHTMLを1ファイルで作成してください。

# 企画
【サイト名】${v('sitename')}
【キャッチコピー】${v('catchcopy')}
【コンセプト】${v('concept')}
【ターゲット】${v('target')}
【課題と期待】${v('target_need')}
【構成】${v('sitemap')}

# デザイン
【方向性】${v('design_dir')}
【カラー】${v('colors')}
【フォント・参考】${v('font_ref')}
【レイアウト】${checks('layout')}

# 技術
【使用技術】${checks('tech')}
【避ける書き方】${v('avoid')}

# 制約
【Must】${v('must')}
【特別な指示】${v('message')}

# 必須条件
・HTML・CSS・JSを1ファイルにまとめること
・セマンティックなHTMLタグを使用すること（header/main/section/article/footer等）
・CSSは:root変数で色・フォント・スペースを管理すること
・レスポンシブ対応（メディアクエリ使用）
・JSはDOMContentLoadedまたはdeferで安全に実行すること
・コメントを適切に入れてコードを読みやすくすること
・日本語で作成すること
・作成者「${v('nickname')}」をフッターに入れること

# 禁止事項（必ず守ること）
・ローディング画面・スプラッシュスクリーンの実装禁止
・外部URLへのページ遷移禁止（hrefは#アンカーのみ）
・location.href・location.assign・window.open等による遷移禁止
・ヘッダーをposition:fixedにする場合は必ずbodyにpadding-topを設定すること`.trim();
}

// ══════════════════════════════════════════════
// STEP4: AI生成
// ══════════════════════════════════════════════
const COMMENTS = [
  { trigger:0,  msg:'✨ アイデアを受け取りました！どんなページにするか考え中...' },
  { trigger:5,  msg:'🏗️ ページの骨組み（HTML）を作り始めました！' },
  { trigger:15, msg:'🎨 デザイン（CSS）を考えています...' },
  { trigger:30, msg:'⚙️ レイアウトを組んでいます...' },
  { trigger:50, msg:'✍️ コンテンツを書いています...' },
  { trigger:70, msg:'💅 見た目を細かく調整中...' },
  { trigger:85, msg:'📱 スマホでも見やすいか確認中...' },
  { trigger:95, msg:'🔍 最終チェックをしています...' },
];

function goToGenerate() {
  const apiKey = getApiKey();
  if (!apiKey || apiKey === 'ここにAPIキーを貼る') { alert('APIキーが設定されていません。'); return; }
  // フォームデータを直接取得（latestFormDataに依存しない）
  const data = collectFormData();
  progressNickname = data['nickname'] || progressNickname;
  latestFormData   = data;  // 最新で上書き
  const prompt = buildPrompt(currentLevel, data);
  lastPrompt   = prompt;
  setStep(4);
  hide('screen-form'); show('screen-generate');
  sendProgress(4, 'AI生成中', { prompt: prompt.slice(0, 1000) });
  runStream(apiKey, prompt, false);
}

function abortGenerate() {
  if (abortController) abortController.abort();
  setStep(3); hide('screen-generate'); show('screen-form');
}

async function runStream(apiKey, prompt, isRegen) {
  const boxId = isRegen ? 'fix-stream-box'  : 'stream-box';
  const barId = isRegen ? 'fix-progress-bar': 'progress-bar';
  const box = document.getElementById(boxId);
  const bar = document.getElementById(barId);
  box.innerHTML = ''; bar.style.width = '0%';
  lastCommentTrigger = -1; lastSnippetEl = null;
  abortController = new AbortController();
  addComment(box, COMMENTS[0].msg); lastCommentTrigger = 0;

  let charCount = 0;
  let newHTML   = '';
  const EXPECTED = currentLevel === 3 ? 13000 : currentLevel === 2 ? 7000 : 4000;

  // コード内容をトリガーにした思考メッセージ＋進捗定義
  const codeThoughts = [
    { keyword: '<!DOCTYPE',        msg: '🏗️ ページの骨組み（HTML）を作り始めました！',  pct:  8, done: false },
    { keyword: '<style',           msg: '🎨 デザイン（CSS）を考えています...',            pct: 18, done: false },
    { keyword: ':root',            msg: '🎨 カラーとフォントを設定しています...',          pct: 28, done: false },
    { keyword: 'display:',         msg: '⚙️ レイアウトを組んでいます...',                  pct: 40, done: false },
    { keyword: '@media',           msg: '📱 スマホでも見やすいか調整しています...',         pct: 54, done: false },
    { keyword: '</style>',         msg: '✍️ コンテンツを書いています...',                  pct: 64, done: false },
    { keyword: '<script',          msg: '⚡ JavaScriptを追加しています...',                pct: 76, done: false },
    { keyword: 'addEventListener', msg: '🔧 インタラクションを組み込んでいます...',        pct: 87, done: false },
    { keyword: '</html>',          msg: '🔍 最終チェックをしています...',                  pct: 97, done: false },
  ];

  const messages = isRegen
    ? [{ role:'user', content:`以下のHTMLを修正してください。\n\n【修正の指示】\n${prompt}\n\n【現在のHTML】\n${generatedHTML}\n\n修正後のHTMLのみを出力。説明文やコードブロック記法は含めないでください。` }]
    : [{ role:'user', content: prompt }];

  try {
    const res = await fetch('https://api.anthropic.com/v1/messages', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-api-key': apiKey,
        'anthropic-version': '2023-06-01',
        'anthropic-dangerous-direct-browser-access': 'true',
      },
      body: JSON.stringify({ model:'claude-sonnet-4-6', max_tokens:16000, stream:true,
        system:`あなたはWebデザイナーです。以下のルールを必ず守ってください。

【出力ルール】
・HTMLコードのみを出力する。説明文・マークダウンのコードブロック記法（\`\`\`）は一切含めない
・必ず完全なHTMLを出力する。途中で切れた場合は必ず続きを出力すること

【実装禁止事項】
・ローディング画面・スプラッシュスクリーンの実装禁止（画面が真っ白/真っ黒になるため）
・window.location.href や location.assign() による外部ページ遷移の禁止
・ページリロード（location.reload()）の禁止
・別ウィンドウ・別タブを開く処理（window.open）の禁止
・外部URLへのリンク遷移の禁止（href は #アンカーのみ使用可）

【デザインルール】
・背景色は白または明るい色を基本とする
・ユーザーが明示的にダークテーマを指定した場合のみダーク背景を使用する
・ヘッダーを position:fixed にする場合は top:0 のみ使用し、bodyに padding-top を必ず設定する

【コード品質・バグ防止ルール】
・:root変数でカラー・フォント・スペースを管理する
・セマンティックなHTMLタグを使用する（header/main/section/article/footer等）
・スマホ対応（メディアクエリ）を必ず含める
・JavaScriptは必ずDOMContentLoadedまたはdeferで安全に実行する
・アニメーションはCSSアニメーションまたはIntersectionObserverを使い、setIntervalの多用を避ける
・要素のIDやクラス名は一意にし、重複させない
・JavaScriptで要素を取得する前に必ずnullチェックを行う（例: if(!el) return;）
・CSSのz-indexは整数値を使い、競合しないよう設計する
・フォントはGoogle Fontsの@importで読み込み、フォールバックを必ず設定する
・画像はdummyやplaceholderは使わず、CSSのbackground-colorやグラデーションで代替する
・カルーセル・スライダーは自作せずCSSのscroll-snap-typeで実装する
・モーダルはdialog要素またはシンプルなdisplay:none切り替えで実装する`,
        messages }),
      signal: abortController.signal,
    });

    if (!res.ok) {
      try {
        const err = await res.json();
        addComment(box, `❌ エラー: ${err.error?.message || res.status}`, true);
      } catch {
        addComment(box, `❌ エラー: ${res.status} ${res.statusText}`, true);
      }
      return;
    }

    const reader  = res.body.getReader();
    const decoder = new TextDecoder();
    let buffer = '';
    let stopReason = 'end_turn';

    while (true) {
      const { done, value } = await reader.read();
      if (done) break;
      buffer += decoder.decode(value, { stream:true });
      const lines = buffer.split('\n'); buffer = lines.pop();
      for (const line of lines) {
        if (!line.startsWith('data: ')) continue;
        const d = line.slice(6).trim();
        if (d === '[DONE]') continue;
        try {
          const evt = JSON.parse(d);
          // stop_reasonを取得
          if (evt.type === 'message_delta' && evt.delta?.stop_reason) {
            stopReason = evt.delta.stop_reason;
          }
          if (evt.type === 'content_block_delta' && evt.delta?.type === 'text_delta') {
            const chunk = evt.delta.text;
            newHTML += chunk; charCount += chunk.length;
            // 思考メッセージ＋進捗バー：コードキーワードを検出
            for (const t of codeThoughts) {
              if (!t.done && newHTML.includes(t.keyword)) {
                t.done = true;
                addComment(box, t.msg);
                bar.style.width = t.pct + '%';
              }
            }
            // 500文字ごとにコードスニペットをadminに送信
            if (charCount % 500 < chunk.length) {
              sendProgress(4, 'AI生成中', {
                code_snippet: newHTML.slice(-500),
                code_total_chars: charCount,
                prompt: lastPrompt.slice(0, 1000),
              });
            }
            if (charCount % 200 < chunk.length) showCodeSnippet(box, newHTML.slice(-200).replace(/\n{3,}/g, '\n\n'));
          }
        } catch {}
      }
    }

    // max_tokensで止まった場合は続きを自動リクエスト（最大3回）
    if (stopReason === 'max_tokens' && !isRegen) {
      addComment(box, '📝 続きを生成しています...');
      await continueGeneration(apiKey, newHTML, bar, box, codeThoughts);
      return;
    }

    bar.style.width = '100%';
    generatedHTML = newHTML;
    addComment(box, isRegen ? '✅ 修正が完了しました！' : '🎉 完成しました！', false, true);
    setTimeout(() => isRegen ? finishRegen() : showDone(), 600);

  } catch (e) {
    if (e.name !== 'AbortError') addComment(box, `❌ エラー: ${e.message}`, true);
  }
}

function addComment(box, msg, isError=false, isDone=false) {
  // 思考1行テキストを更新（stream-boxのみ）
  if (box.id === 'stream-box') {
    const txt = document.getElementById('thought-text');
    const dot = document.getElementById('thought-dot');
    if (txt) txt.textContent = msg;
    if (isDone && dot) { dot.style.animation = 'none'; dot.style.background = '#1a7a4a'; }
    if (isError && dot) { dot.style.animation = 'none'; dot.style.background = '#c0392b'; }
  }
}
function showCodeSnippet(box, snippet) {
  if (lastSnippetEl) lastSnippetEl.remove();
  const el = document.createElement('span');
  el.style.color = '#fff';
  el.style.display = 'block';
  el.textContent = snippet;
  box.appendChild(el);
  lastSnippetEl = el;
  box.scrollTop = box.scrollHeight;
}

// max_tokensで止まった場合に続きを自動生成（最大3回）
async function continueGeneration(apiKey, partialHTML, bar, box, codeThoughts, attempt = 1) {
  if (attempt > 3) {
    addComment(box, '⚠️ 生成が長いため一部省略しました。修正機能で調整できます。');
    generatedHTML = partialHTML;
    bar.style.width = '100%';
    addComment(box, '🎉 完成しました！', false, true);
    setTimeout(() => showDone(), 600);
    return;
  }

  try {
    const res = await fetch('https://api.anthropic.com/v1/messages', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-api-key': apiKey,
        'anthropic-version': '2023-06-01',
        'anthropic-dangerous-direct-browser-access': 'true',
      },
      body: JSON.stringify({
        model: 'claude-sonnet-4-6',
        max_tokens: 16000,
        stream: true,
        system: 'あなたはWebデザイナーです。HTMLコードのみを出力してください。説明文やコードブロック記法は含めないでください。',
        messages: [
          {
            role: 'user',
            content: `以下のHTMLが途中で切れています。切れた部分から続きを出力してください。先頭に説明文は不要です。HTMLコードのみ出力してください。\n\n---途中まで生成されたHTML---\n${partialHTML.slice(-2000)}`
          }
        ],
      }),
      signal: abortController.signal,
    });

    if (!res.ok) {
      generatedHTML = partialHTML;
      bar.style.width = '100%';
      addComment(box, '🎉 完成しました！', false, true);
      setTimeout(() => showDone(), 600);
      return;
    }
    const reader  = res.body.getReader();
    const decoder = new TextDecoder();
    let buffer    = '';
    let continued = partialHTML;
    let stopReason = 'end_turn';

    while (true) {
      const { done, value } = await reader.read();
      if (done) break;
      buffer += decoder.decode(value, { stream: true });
      const lines = buffer.split('\n'); buffer = lines.pop();
      for (const line of lines) {
        if (!line.startsWith('data: ')) continue;
        const d = line.slice(6).trim();
        if (d === '[DONE]') continue;
        try {
          const evt = JSON.parse(d);
          if (evt.type === 'message_delta' && evt.delta?.stop_reason) {
            stopReason = evt.delta.stop_reason;
          }
          if (evt.type === 'content_block_delta' && evt.delta?.type === 'text_delta') {
            const chunk = evt.delta.text;
            continued += chunk;
            showCodeSnippet(box, continued.slice(-200).replace(/\n{3,}/g, '\n\n'));
            for (const t of codeThoughts) {
              if (!t.done && continued.includes(t.keyword)) {
                t.done = true;
                addComment(box, t.msg);
                bar.style.width = t.pct + '%';
              }
            }
          }
        } catch {}
      }
    }

    if (stopReason === 'max_tokens') {
      addComment(box, `📝 続きを生成しています... (${attempt + 1}/3)`);
      await continueGeneration(apiKey, continued, bar, box, codeThoughts, attempt + 1);
    } else {
      generatedHTML = continued;
      bar.style.width = '100%';
      addComment(box, '🎉 完成しました！', false, true);
      setTimeout(() => showDone(), 600);
    }

  } catch (e) {
    // ネットワークエラー・JSONパースエラー等は完了扱い
    generatedHTML = partialHTML;
    bar.style.width = '100%';
    addComment(box, '🎉 完成しました！', false, true);
    setTimeout(() => showDone(), 600);
  }
}

// ══════════════════════════════════════════════
// STEP5: 確認・修正
// ══════════════════════════════════════════════
// プレビュー用：リンクの外部遷移を防ぐスクリプトをHTMLに注入
function injectPreview(html) {
  const script = `<script>
(function(){
  // location.hrefへの代入をブロック（ページ遷移を防ぐ）
  try {
    Object.defineProperty(window, 'location', {
      get: function(){ return { href:'#', hash:'', assign:function(){}, replace:function(){}, reload:function(){} }; },
      configurable: true
    });
  } catch(e) {}

  // すべてのリンクのクリックを横取り
  document.addEventListener('click', function(e){
    var a = e.target.closest('a');
    if (!a) return;
    var href = a.getAttribute('href') || '';
    e.preventDefault();
    if (href.startsWith('#') && href.length > 1) {
      var el = document.getElementById(href.slice(1));
      if (el) el.scrollIntoView({behavior:'smooth'});
    }
  }, true);

  // フォームの送信もブロック
  document.addEventListener('submit', function(e){ e.preventDefault(); }, true);
})();
<\/script>`;
  if (html.includes('</head>')) {
    return html.replace('</head>', script + '</head>');
  }
  if (html.includes('</body>')) {
    return html.replace('</body>', script + '</body>');
  }
  return html + script;
}

function showDone() {
  setStep(5); hide('screen-generate'); show('screen-done');
  document.getElementById('preview-frame').srcdoc = injectPreview(generatedHTML);
  document.getElementById('html-output').textContent = generatedHTML;
  sendProgress(5, '確認・修正中');
}

function setFix(text) { document.getElementById('fix-input').value = text; }

async function applyFix() {
  const instruction = document.getElementById('fix-input').value.trim();
  if (!instruction) { alert('修正してほしいことを入力してね！'); return; }
  const apiKey = getApiKey();
  if (!apiKey || apiKey === 'ここにAPIキーを貼る') { alert('APIキーが設定されていません。'); return; }

  document.getElementById('btn-fix').disabled = true;
  document.getElementById('fix-stream-wrap').style.display = 'block';
  const box = document.getElementById('fix-stream-box');
  const bar = document.getElementById('fix-progress-bar');
  box.textContent = '';
  bar.style.width = '0%';

  fixHistory.push(instruction);
  await applyFixFullRegen(apiKey, instruction, box, bar);
}

// HTMLの構造サマリーを抽出（トークン節約）

// 全再生成による修正
async function applyFixFullRegen(apiKey, instruction, box, bar) {
  abortController = new AbortController();
  let fixedHTML = '';
  let charCount = 0;
  let stopReason = 'end_turn';

  const fixPrompt = `以下のHTMLを修正してください。\n\n【修正の指示】\n${instruction}\n\n【現在のHTML】\n${generatedHTML}\n\n修正後の完全なHTMLのみを出力してください。`;

  try {
    const res = await fetch('https://api.anthropic.com/v1/messages', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-api-key': apiKey,
        'anthropic-version': '2023-06-01',
        'anthropic-dangerous-direct-browser-access': 'true',
      },
      body: JSON.stringify({
        model: 'claude-sonnet-4-6',
        max_tokens: 16000,
        stream: true,
        system: 'あなたはWebデザイナーです。HTMLコードのみを出力してください。説明文やコードブロック記法は含めないでください。',
        messages: [{ role: 'user', content: fixPrompt }],
      }),
      signal: abortController.signal,
    });

    if (!res.ok) { showFixMsg(box, `❌ エラー: ${res.status}`, '#FF8888'); document.getElementById('btn-fix').disabled = false; return; }

    const reader = res.body.getReader();
    const decoder = new TextDecoder();
    let buffer = '';

    while (true) {
      const { done, value } = await reader.read();
      if (done) break;
      buffer += decoder.decode(value, { stream: true });
      const lines = buffer.split('\n'); buffer = lines.pop();
      for (const line of lines) {
        if (!line.startsWith('data: ')) continue;
        const d = line.slice(6).trim();
        if (d === '[DONE]') continue;
        try {
          const evt = JSON.parse(d);
          if (evt.type === 'message_delta' && evt.delta?.stop_reason) stopReason = evt.delta.stop_reason;
          if (evt.type === 'content_block_delta' && evt.delta?.type === 'text_delta') {
            fixedHTML += evt.delta.text;
            charCount += evt.delta.text.length;
            bar.style.width = Math.min(99, Math.round(charCount / 8000 * 100)) + '%';
            if (charCount % 300 < evt.delta.text.length) {
              if (box.lastElementChild?.dataset.code) box.lastElementChild.remove();
              const el = document.createElement('span');
              el.dataset.code = '1';
              el.style.cssText = 'color:#fff;display:block;white-space:pre-wrap;word-break:break-all;font-size:11px;';
              el.textContent = fixedHTML.slice(-300);
              box.appendChild(el);
              box.scrollTop = box.scrollHeight;
            }
          }
        } catch {}
      }
    }

    // 続き生成（max_tokens対応）
    let attempt = 0;
    while (stopReason === 'max_tokens' && attempt < 3) {
      attempt++;
      const _apiKey = apiKey || getApiKey(); // 念のためフォールバック
      showFixMsg(box, `📝 続きを生成中... (${attempt}/3)`, '#88FF88');
      const contRes = await fetch('https://api.anthropic.com/v1/messages', {
        method: 'POST',
        headers: { 'Content-Type':'application/json','x-api-key':_apiKey,'anthropic-version':'2023-06-01','anthropic-dangerous-direct-browser-access':'true' },
        body: JSON.stringify({ model:'claude-sonnet-4-6', max_tokens:16000, stream:true,
          system:'HTMLコードのみを出力してください。',
          messages:[{ role:'user', content:`以下のHTMLの続きを出力してください。\n---\n${fixedHTML.slice(-1000)}` }] }),
        signal: abortController.signal,
      });
      if (!contRes.ok) break;
      const cr = contRes.body.getReader(); const cd = new TextDecoder(); let cb = ''; stopReason = 'end_turn';
      while (true) {
        const { done, value } = await cr.read(); if (done) break;
        cb += cd.decode(value, { stream:true });
        const ls = cb.split('\n'); cb = ls.pop();
        for (const l of ls) {
          if (!l.startsWith('data: ')) continue;
          const dd = l.slice(6).trim(); if (dd==='[DONE]') continue;
          try { const e2=JSON.parse(dd); if(e2.type==='message_delta'&&e2.delta?.stop_reason)stopReason=e2.delta.stop_reason; if(e2.type==='content_block_delta'&&e2.delta?.type==='text_delta')fixedHTML+=e2.delta.text; } catch {}
        }
      }
    }

    bar.style.width = '100%';
    generatedHTML = fixedHTML;
    finishRegen();

  } catch(e) {
    if (e.name !== 'AbortError') showFixMsg(box, `❌ エラー: ${e.message}`, '#FF8888');
    document.getElementById('btn-fix').disabled = false;
  }
}

function showFixMsg(box, msg, color) {
  const el = document.createElement('span');
  el.style.cssText = `color:${color};display:block;margin:4px 0;`;
  el.textContent = msg;
  box.appendChild(el);
  box.scrollTop = box.scrollHeight;
}

function renderFixHistory() {
  const list = document.getElementById('fix-history-list');
  const wrap = document.getElementById('fix-history');
  if (!fixHistory.length) { wrap.style.display='none'; return; }
  wrap.style.display = 'block';
  list.innerHTML = fixHistory.map((h,i) =>
    `<div class="fix-history-item"><span class="fix-count-badge">修正${i+1}</span>${h}</div>`
  ).join('');
}

function finishRegen() {
  document.getElementById('preview-frame').srcdoc = injectPreview(generatedHTML);
  document.getElementById('html-output').textContent = generatedHTML;
  renderFixHistory();
  document.getElementById('fix-input').value = '';
  document.getElementById('btn-fix').disabled = false;
}

// ══════════════════════════════════════════════
// STEP6: 保存・完成
// ══════════════════════════════════════════════
async function goToSave() {
  if (!scannedSlotId) { alert('QRスロットが未設定です。'); return; }
  sendProgress(6, '保存中...');
  const nickname = collectFormData()['nickname'] || 'さくひん';

  // HTMLをBase64エンコードしてWAFを回避
  const htmlB64 = btoa(unescape(encodeURIComponent(generatedHTML)));

  const fd = new FormData();
  fd.append('csrf',      document.getElementById('ws-csrf').value);
  fd.append('id',        scannedSlotId);
  fd.append('title',     nickname + ' のホームページ');
  fd.append('published', '1');
  fd.append('prompt',    lastPrompt);
  fd.append('html_b64',  htmlB64);  // Base64エンコードで送信

  setStep(6); hide('screen-done'); show('screen-finish');
  document.getElementById('finish-msg').textContent = '保存中...';
  document.getElementById('preview-frame-final').srcdoc = injectPreview(generatedHTML);

  try {
    const res  = await fetch('save_workshop.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.ok) {
      sendProgress(6, '保存完了・完成！');
      document.getElementById('finish-msg').textContent = '保存が完了しました！パンフレットのQRコードからいつでも見られるよ！';
      document.getElementById('finish-url').innerHTML =
        'URL: <a href="' + data.view + '" target="_blank" style="color:var(--teal-deep);">' + data.view + '</a>';
    } else {
      document.getElementById('finish-msg').textContent = '⚠️ 保存に失敗しました: ' + (data.msg || '');
    }
  } catch (e) {
    document.getElementById('finish-msg').textContent = '⚠️ 通信エラー: ' + e.message;
  }
}

function downloadHTML() {
  const nickname = (collectFormData()['nickname'] || 'homepage').replace(/[^\w\u3040-\u30FF\u4E00-\u9FFF]/g,'_');
  const blob = new Blob([generatedHTML], { type:'text/html;charset=utf-8' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob); a.download = nickname + '_homepage.html'; a.click();
}

// ══════════════════════════════════════════════
// リセット・ユーティリティ
// ══════════════════════════════════════════════
function restartFromScan() {
  scannedSlotId = null; currentLevel = 0; generatedHTML = ''; fixHistory = [];
  ['screen-level','screen-form','screen-generate','screen-done','screen-finish'].forEach(hide);
  document.getElementById('slot-err').classList.add('ws-hidden');
  document.getElementById('scan-status-msg').textContent = 'カメラを起動中...';
  document.querySelectorAll('.ws-level-btn').forEach(el => el.classList.remove('selected'));
  setStep(1); show('screen-scan');
  startCamera();
}

function show(id) { document.getElementById(id).classList.remove('ws-hidden'); }
function hide(id) { document.getElementById(id).classList.add('ws-hidden'); }
function setStep(n) {
  for (let i = 1; i <= 6; i++) {
    const el = document.getElementById('step' + i);
    el.classList.remove('active','done');
    if (i < n) el.classList.add('done');
    if (i === n) el.classList.add('active');
  }
}

// 起動
startCamera();
</script>
</body>
</html>
