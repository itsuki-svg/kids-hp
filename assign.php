<?php require __DIR__ . '/lib.php';
require_login();
?><!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>スキャンで割り当て | <?= e(EVENT_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=4">
</head><body>
<div class="admin-wrap">
  <div class="admin-head">
    <h1>スキャンで割り当て</h1>
    <div class="btnrow">
      <a class="btn btn-sec" href="admin.php">← 管理一覧へ</a>
    </div>
  </div>

  <p style="color:var(--ink-soft);font-size:13px;margin:0 0 18px">
    ① QRカードをカメラにかざす → ② 子供のHTMLファイルを右の枠にドロップ → ③「割り当てる」<br>
    ※カメラを使うにはHTTPS（さくらの https:// アドレス）でアクセスしてください。
  </p>

  <div class="assign-grid">
    <!-- 左：スキャナ -->
    <div class="panel">
      <div class="panel-h">① QRカードを読み取る</div>
      <div id="reader"></div>
      <div class="cam-row">
        <select id="camSel" class="cam-sel"></select>
        <button id="rescan" class="btn btn-sec btn-mini" style="display:none">別のカードを読む</button>
      </div>
      <div id="camMsg" class="cam-msg"></div>
    </div>

    <!-- 右：ファイル＆割当 -->
    <div class="panel">
      <div class="panel-h">② ファイルをドロップ</div>
      <div id="drop" class="drop">
        <div class="drop-ic">📄</div>
        <div class="drop-t">ここにHTMLファイルをドラッグ&ドロップ</div>
        <div class="drop-s">またはクリックして選択</div>
        <div id="fileName" class="file-name"></div>
      </div>
      <input type="file" id="htmlfile" accept=".html,.htm" hidden>

      <label class="fld">タイトル（名前・作品名）
        <input type="text" id="title" placeholder="例：たろうのゲームページ">
      </label>
      <label class="chk"><input type="checkbox" id="published" checked> 公開する</label>

      <button id="assignBtn" class="btn btn-pri assign-btn" disabled>③ 割り当てる</button>
    </div>
  </div>

  <!-- ステータスバー -->
  <div class="statusbar">
    <div class="st"><span class="st-l">読み取り中の枠</span><span id="stSlot" class="st-v none">未スキャン</span></div>
    <div class="st"><span class="st-l">選んだファイル</span><span id="stFile" class="st-v none">なし</span></div>
  </div>

  <div id="result" class="result"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
const CSRF = <?= json_encode(csrf_token()) ?>;
let selectedSlot = null;
let currentFile  = null;
let qr = null;
let scanning = false;

const $ = id => document.getElementById(id);
const camMsg = $('camMsg');

/* QRテキストから ?id=数字 を取り出す */
function parseId(text){
  try { const u = new URL(text); const v = u.searchParams.get('id'); if (v && /^[a-zA-Z0-9]+$/.test(v)) return v; } catch(e){}
  const m = String(text).match(/[?&]id=([a-zA-Z0-9]+)/);
  return m ? m[1] : null;
}

function refreshStatus(){
  const slotEl = $('stSlot'), fileEl = $('stFile');
  if (selectedSlot){ slotEl.textContent = 'No.' + selectedSlot; slotEl.classList.remove('none'); }
  else { slotEl.textContent = '未スキャン'; slotEl.classList.add('none'); }
  if (currentFile){ fileEl.textContent = currentFile.name; fileEl.classList.remove('none'); }
  else { fileEl.textContent = 'なし'; fileEl.classList.add('none'); }
  $('assignBtn').disabled = !(selectedSlot && currentFile);
}

/* ===== スキャナ ===== */
function onScanSuccess(text){
  const id = parseId(text);
  if (!id){ camMsg.textContent = '⚠️ このシステムのQRではありません'; camMsg.className='cam-msg err'; return; }
  selectedSlot = id;
  camMsg.textContent = '✅ No.' + id + ' を読み取りました';
  camMsg.className = 'cam-msg ok';
  if (qr && scanning){ qr.pause(true); scanning = false; }
  $('rescan').style.display = '';
  refreshStatus();
}

function startCamera(camId){
  if (!qr) qr = new Html5Qrcode('reader');
  const cfg = { fps:10, qrbox:{width:220,height:220} };
  qr.start(camId, cfg, onScanSuccess, ()=>{})
    .then(()=>{ scanning = true; camMsg.textContent=''; camMsg.className='cam-msg'; })
    .catch(err=>{ camMsg.textContent='カメラを開始できません: '+err; camMsg.className='cam-msg err'; });
}

function switchCamera(camId){
  const go = ()=> startCamera(camId);
  if (qr && scanning){ qr.stop().then(go).catch(go); } else { go(); }
}

Html5Qrcode.getCameras().then(cams=>{
  if (!cams || !cams.length){ camMsg.textContent='カメラが見つかりません'; camMsg.className='cam-msg err'; return; }
  const sel = $('camSel');
  cams.forEach((c,i)=>{ const o=document.createElement('option'); o.value=c.id; o.textContent=c.label||('カメラ'+(i+1)); sel.appendChild(o); });
  // 背面カメラ優先
  let pick = cams.find(c=>/back|rear|environment|背面/i.test(c.label||''));
  pick = pick || cams[cams.length-1];
  sel.value = pick.id;
  sel.addEventListener('change', ()=> switchCamera(sel.value));
  startCamera(pick.id);
}).catch(err=>{ camMsg.textContent='カメラ権限を許可してください: '+err; camMsg.className='cam-msg err'; });

$('rescan').addEventListener('click', ()=>{
  $('rescan').style.display='none';
  selectedSlot = null; refreshStatus();
  camMsg.textContent=''; camMsg.className='cam-msg';
  if (qr){ if (scanning){ qr.resume(); } else { qr.resume(); scanning = true; } }
});

/* ===== ドラッグ&ドロップ ===== */
const drop = $('drop'), fileInput = $('htmlfile');
function setFile(f){
  const ext = (f.name.split('.').pop()||'').toLowerCase();
  if (ext!=='html' && ext!=='htm'){ alert('HTMLファイル（.html / .htm）を選んでください'); return; }
  currentFile = f;
  $('fileName').textContent = '📄 ' + f.name;
  if (!$('title').value.trim()) $('title').value = f.name.replace(/\.(html?|HTM?L?)$/i,'');
  refreshStatus();
}
['dragenter','dragover'].forEach(ev=> drop.addEventListener(ev, e=>{ e.preventDefault(); drop.classList.add('over'); }));
['dragleave','drop'].forEach(ev=> drop.addEventListener(ev, e=>{ e.preventDefault(); drop.classList.remove('over'); }));
drop.addEventListener('drop', e=>{ const fs=e.dataTransfer.files; if (fs && fs.length) setFile(fs[0]); });
drop.addEventListener('click', ()=> fileInput.click());
fileInput.addEventListener('change', ()=>{ if (fileInput.files.length) setFile(fileInput.files[0]); });

/* ===== 割り当て送信 ===== */
$('assignBtn').addEventListener('click', async ()=>{
  if (!selectedSlot || !currentFile) return;
  const btn = $('assignBtn'); btn.disabled = true; btn.textContent = '送信中…';
  const fd = new FormData();
  fd.append('csrf', CSRF);
  fd.append('id', selectedSlot);
  fd.append('title', $('title').value.trim());
  if ($('published').checked) fd.append('published','1');
  fd.append('htmlfile', currentFile);
  try{
    const r = await fetch('api_assign.php', { method:'POST', body:fd });
    const j = await r.json();
    showResult(j);
    if (j.ok) resetForNext();
  }catch(err){
    showResult({ok:false, msg:'通信エラー: '+err});
  }
  btn.textContent = '③ 割り当てる';
  refreshStatus();
});

function showResult(j){
  const el = $('result');
  if (j.ok){
    el.className = 'result ok';
    el.innerHTML = '<strong>'+j.msg+'</strong> '
      + '<a class="btn btn-sec btn-mini" href="'+j.view+'" target="_blank">確認する</a>';
  } else {
    el.className = 'result err';
    el.textContent = '⚠️ ' + (j.msg||'失敗しました');
  }
}

/* 次の子のために初期化（スキャナは再開） */
function resetForNext(){
  currentFile = null;
  $('fileName').textContent = '';
  $('title').value = '';
  fileInput.value = '';
  selectedSlot = null;
  $('rescan').style.display = 'none';
  camMsg.textContent = ''; camMsg.className = 'cam-msg';
  if (qr){ try{ qr.resume(); }catch(e){} scanning = true; }
  refreshStatus();
}

refreshStatus();
</script>
</body></html>
