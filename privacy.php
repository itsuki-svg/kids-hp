<?php require __DIR__ . '/lib.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>プライバシーポリシー | <?= e(EVENT_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=4">
</head>
<body>
<div class="masthead"><div class="masthead-inner"><img src="assets/logo.png" alt="Logo"></div></div>

<div class="legal-wrap">
  <h1>プライバシーポリシー</h1>
  <p class="legal-date">制定日：2026年7月18日</p>

  <p>Example Inc.（以下「当社」）は、「<?= e(EVENT_TITLE) ?>」（以下「本サービス」）における個人情報の取り扱いについて、以下のとおりプライバシーポリシーを定めます。</p>

  <h2>第1条（取得する情報）</h2>
  <p>本サービスでは、以下の情報を取得する場合があります。</p>
  <ul>
    <li>参加者が作成したホームページのHTMLファイル（作品データ）</li>
    <li>作品に付与されたタイトル（ニックネーム等）</li>
    <li>アクセスログ（IPアドレス・ブラウザ情報・アクセス日時）</li>
  </ul>
  <p>本サービスは、氏名・住所・電話番号・メールアドレス等の個人を直接特定できる情報を収集しません。作品にこれらの情報を含めないよう、スタッフおよび保護者の方がご確認ください。</p>

  <h2>第2条（情報の利用目的）</h2>
  <p>取得した情報は以下の目的で利用します。</p>
  <ul>
    <li>本サービス（作品の展示・公開）の提供</li>
    <li>サービスの運営・改善</li>
    <li>不正利用の防止およびセキュリティ対応</li>
    <li>イベントの記録・広報（作品の写真撮影等）</li>
  </ul>

  <h2>第3条（第三者への提供）</h2>
  <p>当社は、以下の場合を除き、取得した情報を第三者に提供しません。</p>
  <ul>
    <li>本人（保護者含む）の同意がある場合</li>
    <li>法令に基づき開示が必要な場合</li>
    <li>人の生命・身体・財産の保護のために必要な場合</li>
  </ul>

  <h2>第4条（Cookieおよびアクセス解析）</h2>
  <p>本サービスでは、セッション管理のためにCookieを使用します。Cookieには個人を特定する情報は含まれません。アクセス解析ツールは現時点では導入していません。</p>

  <h2>第5条（作品データの保管・削除）</h2>
  <p>作品データ（HTMLファイル）はイベント期間中、当社のサーバーに保管されます。イベント終了後は、運営の判断により削除する場合があります。削除をご希望の場合は下記お問い合わせ先までご連絡ください。</p>

  <h2>第6条（外部サービスの利用）</h2>
  <p>本サービスでは、以下の外部サービスを利用しています。各サービスのプライバシーポリシーも合わせてご確認ください。</p>
  <ul>
    <li><strong>Anthropic Claude API</strong>（ホームページ生成AI）— <a href="https://www.anthropic.com/privacy" target="_blank" rel="noopener">Anthropic プライバシーポリシー</a></li>
    <li><strong>Google Fonts</strong>（フォント配信）— <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google プライバシーポリシー</a></li>
    <li><strong>レンタルサーバー</strong>（サーバー）— ご利用のホスティング事業者のプライバシーポリシーをご確認ください</li>
  </ul>

  <h2>第7条（未成年者の個人情報）</h2>
  <p>本サービスは主に未成年者を対象としています。保護者の方は、お子様の作品に個人情報が含まれていないかご確認のうえ、ご参加ください。</p>

  <h2>第8条（安全管理措置）</h2>
  <p>当社は、取得した情報への不正アクセス・紛失・破損・改ざんを防止するため、HTTPS通信・アクセス制限等の適切な安全管理措置を講じます。</p>

  <h2>第9条（ポリシーの変更）</h2>
  <p>当社は必要に応じて本ポリシーを変更することがあります。変更後のポリシーは本ページに掲載した時点で効力を生じます。</p>

  <div class="legal-contact">
    <strong>個人情報に関するお問い合わせ先</strong><br>
    <?= COMPANY_NAME ?><br>
    Webサイト：<a href="<?= COMPANY_URL ?>" target="_blank" rel="noopener"><?= COMPANY_URL ?></a><br>
    お問い合わせ：Webサイトのお問い合わせフォームよりご連絡ください。<br>
    <small style="color:var(--ink-soft);">※ お問い合わせへの回答には数日お時間をいただく場合があります。</small>
  </div>

  <a class="back-link" href="index.php">← ギャラリーに戻る</a>
</div>
</body>
</html>
