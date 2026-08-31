<?php require __DIR__ . '/lib.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>利用規約 | <?= e(EVENT_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=4">
</head>
<body>
<div class="masthead"><div class="masthead-inner"><img src="assets/logo.png" alt="Logo"></div></div>

<div class="legal-wrap">
  <h1>利用規約</h1>
  <p class="legal-date">制定日：2026年7月18日</p>

  <p>本利用規約（以下「本規約」）は、Example Inc.（以下「当社」）が運営する「<?= e(EVENT_TITLE) ?>」（以下「本サービス」）の利用条件を定めるものです。本サービスをご利用になる方は、本規約に同意したものとみなします。</p>

  <h2>第1条（目的）</h2>
  <p>本サービスは、サンプルイベント Vol.3 において、参加者（主に未成年者）が作成したホームページ作品を展示・公開するために提供されます。</p>

  <h2>第2条（利用資格）</h2>
  <p>本サービスは、イベント参加者およびその保護者・同伴者を対象としています。未成年者が利用する場合は、保護者の同意を得たうえで利用してください。</p>

  <h2>第3条（禁止事項）</h2>
  <p>利用者は、以下の行為を行ってはなりません。</p>
  <ul>
    <li>他者の権利（著作権・肖像権・プライバシー権等）を侵害するコンテンツの投稿</li>
    <li>公序良俗に反するコンテンツの投稿</li>
    <li>第三者を誹謗中傷する行為</li>
    <li>個人情報（氏名・住所・電話番号・学校名等）を含むコンテンツの掲載</li>
    <li>本サービスの運営を妨害する行為</li>
    <li>不正アクセスその他の違法行為</li>
    <li>営業・宣伝・勧誘を目的とした利用</li>
  </ul>

  <h2>第4条（コンテンツの管理）</h2>
  <p>投稿されたホームページ作品の著作権は作成者（参加者）に帰属します。ただし、当社はイベント記録・広報目的に限り、作品を撮影・掲載する場合があります。当社は、禁止事項に該当すると判断したコンテンツを予告なく削除できるものとします。</p>

  <h2>第5条（公開・非公開）</h2>
  <p>作品の公開・非公開はイベントスタッフが管理します。QRコードのURLを知っている方はどなたでも作品を閲覧できます。個人情報を含むコンテンツは掲載しないよう保護者の方もご確認ください。</p>

  <h2>第6条（免責事項）</h2>
  <p>当社は、本サービスに関して以下について責任を負いません。</p>
  <ul>
    <li>利用者が投稿したコンテンツの内容</li>
    <li>システム障害・通信障害によるサービスの中断</li>
    <li>本サービスの利用または利用不能によって生じた損害</li>
    <li>第三者との間で生じたトラブル</li>
  </ul>

  <h2>第7条（サービスの変更・終了）</h2>
  <p>当社は、利用者への事前通知なく本サービスの内容を変更し、または提供を終了することがあります。イベント終了後は、作品データを削除する場合があります。</p>

  <h2>第8条（規約の変更）</h2>
  <p>当社は必要に応じて本規約を変更することがあります。変更後の規約は本ページに掲載した時点で効力を生じます。</p>

  <h2>第9条（準拠法・管轄）</h2>
  <p>本規約は日本法に準拠します。本サービスに関する紛争は、東京地方裁判所を第一審の専属的合意管轄裁判所とします。</p>

  <div class="legal-contact">
    <strong>運営者情報</strong><br>
    <?= COMPANY_NAME ?><br>
    Webサイト：<a href="<?= COMPANY_URL ?>" target="_blank" rel="noopener"><?= COMPANY_URL ?></a><br>
    お問い合わせ：Webサイトのお問い合わせフォームよりご連絡ください。
  </div>

  <a class="back-link" href="index.php">← ギャラリーに戻る</a>
</div>
</body>
</html>
