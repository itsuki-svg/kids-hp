<?php
/* ============================================================
 *  設定ファイル  ―  ここだけ書き換えれば動きます
 * ============================================================ */

// 【1】管理画面のパスワード
//   .htaccess の SetEnv KIDSHP_ADMIN_PASSWORD で設定した値を使用します。
//   取得できない場合はフォールバック値を使います（本番では.htaccessで必ず設定してください）
const ADMIN_PASSWORD = ''; // 使用しない（下記getenvで取得）
function get_admin_password(): string {
    $pw = getenv('KIDSHP_ADMIN_PASSWORD');
    return ($pw !== false && $pw !== '') ? $pw : 'change_me';
}

// 【2】事前に発行するQR（スロット）の枚数
const SLOT_COUNT = 30;

// 【3】イベント名（ギャラリーの見出しに表示）
const EVENT_TITLE = 'みんなのホームページ展';

// 【4】サブタイトル
const EVENT_SUBTITLE = 'スマホでQRを読みこむと、作品ページが見られるよ';

// 【5】ベースURL（空のままなら自動判定）
const BASE_URL = '';

// 【6】運営会社URL
const COMPANY_URL = 'https://example.com';
const COMPANY_NAME = 'Your Company Name';

// 【7】アップロードできるHTMLの最大サイズ（バイト）。既定 3MB
const MAX_UPLOAD_BYTES = 3 * 1024 * 1024;
