<?php require __DIR__ . '/lib.php';
$slots = published_slots();
$base  = base_url();
?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(EVENT_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=4">
</head>
<body>
<div class="masthead"><div class="masthead-inner"><img src="assets/logo.png" alt="Logo"></div></div>
<div class="wrap">
  <header class="hero">
    <p class="kicker">Exhibition</p>
    <h1><?= e(EVENT_TITLE) ?></h1>
    <?php if ($slots): ?><div class="meta">公開中の作品　<b><?= count($slots) ?></b> 点</div><?php endif; ?>
  </header>

  <?php if (!$slots): ?>
    <div class="empty">
      <div class="big">準備中です</div>
      <p>まだ公開された作品はありません。もうしばらくお待ちください。</p>
    </div>
  <?php else: ?>
    <main class="grid">
      <?php $no = 1; foreach ($slots as $id => $s):
        $hue   = slot_hue($id);
        $title = $s['title'] !== '' ? $s['title'] : ('作品 No.' . $no);
        $noStr = str_pad((string)$no, 2, '0', STR_PAD_LEFT);
      ?>
      <div class="card">
        <div class="accent" style="background:hsl(<?= $hue ?> 40% 56%)"></div>
        <div class="body">
          <a class="block" href="view.php?id=<?= e($id) ?>">
            <span class="num">NO. <?= e($noStr) ?></span>
            <div class="title"><?= e($title) ?></div>
            <div class="spacer"></div>
            <span class="go">作品を見る <span class="arw">→</span></span>
          </a>
        </div>
      </div>
      <?php $no++; endforeach; ?>
    </main>
  <?php endif; ?>

  <footer class="footer">
    <div class="footer-links">
      <a href="terms.php">利用規約</a>
      <span class="footer-sep">|</span>
      <a href="privacy.php">プライバシーポリシー</a>
      <span class="footer-sep">|</span>
      <a href="<?= COMPANY_URL ?>" target="_blank" rel="noopener">運営会社（<?= COMPANY_NAME ?>）</a>
    </div>
    <div class="footer-copy">
      <?= e(EVENT_TITLE) ?> &nbsp;／&nbsp; Powered by Claude &nbsp;／&nbsp;
      &copy; <?= date('Y') ?> <?= COMPANY_NAME ?>
    </div>
  </footer>
</div>
</body>
</html>
