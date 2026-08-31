# みんなのホームページ展

子供たちが AI でホームページを作り、QR コードで公開するワークショップシステムです。データベース不要で PHP + JSON ファイルだけで動作します。

## Features

- **AI ホームページ生成** — Claude API で子供の回答から HTML ページを自動生成
- **難易度選択** — 初級 / 中級 / 上級で質問内容と生成結果を調整
- **QR スキャン保存** — 作ったページをカメラで QR コードを読み取って即座に保存・公開
- **公開ギャラリー** — 公開済み作品の一覧表示
- **QR カード印刷** — 管理画面から参加人数分の QR カードを一括印刷
- **管理画面** — スロットの公開/非公開切り替え、手動割当
- **修正機能** — 子供が自分の言葉で修正指示を出し、AI が反映
- **データベース不要** — JSON ファイルでスロット管理、HTML ファイルで作品保存

## Data Storage (DB-less Design)

データベースを使わず、`data/slots.json` でスロット管理（id, title, status, assigned_at）を行い、PHP の `file_get_contents` / `file_put_contents` で読み書きします。Claude API が生成した HTML は `pages/{slot_id}.html` にそのまま保存し、`view.php?id={slot_id}` で iframe 表示します。QR カードのリンク先は `view.php?id=ab3x9q2r` のように各スロットの作品ページに直結します。

## Screen Flow

ワークショップ画面 (workshop.php) で子供が難易度を選択し、AI の質問に3〜5問回答すると、Claude API が HTML ページを SSE ストリーミングで生成します。プレビューを確認後、修正が必要なら修正指示を入力して AI が再生成。完成したら「QR で保存」でカメラを起動し、パンフレットの QR をスキャンして `api_assign.php` 経由でスロットに HTML を保存・公開します。管理画面 (admin.php) ではスロット一覧、QR カード一括印刷、タイトル編集、手動割当が可能です。公開ギャラリー (index.php) で全作品を一覧表示し、来場者は QR から個別作品 (view.php) を閲覧します。

## API Endpoints

| Method | Path | 認証 | 概要 |
|--------|------|------|------|
| `POST` | `/workshop.php` (fetch) | — | Claude API で HTML 生成 (SSE) |
| `POST` | `/api_assign.php` | — | QR スキャンでスロットに HTML を割当 |
| `GET` | `/api/check_slot.php` | — | スロットの空き状況確認 |
| `POST` | `/admin.php` | Admin | スロット管理（公開/非公開/タイトル変更） |
| `POST` | `/assign.php` | Admin | 手動スロット割当 |
| `GET` | `/progress.php` | Admin | 生成進捗取得 |

## Security

| 項目 | 実装 |
|------|------|
| 管理者認証 | 環境変数 `KIDSHP_ADMIN_PASSWORD` (`hash_equals` で定数時間比較) |
| ファイルサイズ | `MAX_UPLOAD_BYTES` で HTML サイズ制限 (3MB) |
| 生成 HTML | Claude に XSS 要素を含めない指示（script タグ禁止） |
| QR 割当 | スロット ID の存在確認 + 二重割当防止 |

## Tech Stack

| 項目 | 内容 |
|------|------|
| Backend | PHP 8.x |
| Storage | JSON ファイル + HTML ファイル（DB 不要） |
| AI | Claude API (Anthropic) — SSE ストリーミング |
| QR | ブラウザカメラでの QR 読取（HTTPS 必須） |
| Auth | パスワード認証（管理画面のみ） |

## Directory Structure

```
kids-hp/
├── config.php          # 設定（config.example.php を参照）
├── lib.php             # 共通関数
├── index.php           # 公開ギャラリー
├── view.php            # 個別作品表示（QR のリンク先）
├── workshop.php        # こども向け AI ホームページ作成アプリ
├── admin.php           # 管理画面
├── assign.php          # QR スキャンでスロット割当（スタッフ用）
├── api_assign.php      # 割当 API
├── qrcards.php         # QR カード一括印刷
├── assets/
│   ├── logo.png
│   └── style.css
├── data/
│   └── slots.json      # スロット管理データ（自動生成）
└── pages/              # 生成された HTML ファイル（自動生成）
```

## Setup

1. `config.example.php` を `config.php` にコピーし、管理画面パスワード等を設定（`.htaccess` の `SetEnv` 推奨）
2. `workshop.php` の先頭で Claude API キーを設定
3. PHP が動くサーバーに配置（HTTPS 必須）
4. `data/` と `pages/` のパーミッションを 705 に設定

## 当日の流れ

1. 管理画面から QR カードを印刷してパンフレットに貼付
2. 子供が `workshop.php` で質問に答え、AI がホームページを生成
3. プレビュー確認後「QR で保存」→ パンフの QR をスキャンして自動保存
4. 来場者がパンフの QR をスキャンして作品を閲覧
