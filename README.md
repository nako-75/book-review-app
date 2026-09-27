# BookShelf 書籍管理アプリ. 


## 概要. 
Laravel10とDocker(Laravel Sail)を使用した書籍管理・共有アプリケーションです。

お気に入りの書籍登録やレビュー投稿、読書プランの管理など、

読書を楽しむためのさまざまな機能を備えています。

## 主な機能. 
* ユーザー認証: Laravel Fortifyを用いた安全なユーザー登録・ログイン機能

* 書籍管理: 書籍の登録・編集・削除、およびISBNを利用したGoogle Books API連携機能

* ユーザーエンゲージメント:
    * お気に入り機能
    * レビュー投稿・評価・いいね機能
    * ジャンル別管理
    * ランキング表示
    * 読書プラン機能
    * 通知・レポート機能

* API機能: Laravel Sanctumによるトークン認証、JSON形式のAPIおよびAPI Resourceの実装

## 技術スタック. 
* 言語: PHP 8.1
* フレームワーク: Laravel 10.
* データベース: MySQL 8.4
* 開発環境: Docker, Laravel Sail, phpMyAdmin, Composer, Laravel Pint

## 作成者.  
矢田奈緒子

## 開発環境URL.  
http://localhost 

## 環境構築手順. 
Docker および Laravel Sail を使用したローカル開発環境の構築手順です。

### 1. リポジトリのクローン & ディレクトリ移動.
```
git clone git@github.com:nako-75/book-review-app.git

cd book-review-app
```
### 2. 環境変数ファイルの作成と編集. 

.env.example をコピーして .env ファイルを作成します。
```
cp .env.example .env
```
作成した .env ファイルを開き、アプリケーションの設定および

データベース接続設定や外部APIキーを環境に合わせて適切に設定してください。

### データベース接続設定. 
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### Google Books API 連携用キー
```
GOOGLE_BOOKS_API_KEY=ここに連携キーを入力してください。
```

Composer依存パッケージのインストール
Docker環境（PHPコンテナ）を使用して依存パッケージをインストールします。

```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer install
```

### 3. Laravel Sail の起動.  
```
./vendor/bin/sail up -d
```
Tip: 以降のコマンドで毎回 ./vendor/bin/sail と入力するのが煩わしい場合は、シェルにエイリアスを通しておくと便利です。 alias sail='./vendor/bin/sail'
以下エイリアス設定を前提として記載します。

### 4. アプリケーションキーの生成 & マイグレーションの実行.  
コンテナが起動したら、アプリケーションキーの生成とデータベースのマイグレーション（およびシーディング）を実行します。
```
sail artisan key:generate
sail artisan migrate:fresh --seed
```

### 5. フロントエンドの依存関係インストール & ビルド.  
```
sail npm install
sail npm run dev
```
注意: アプリケーションの画面確認や開発中は、このコマンドを起動したままにしておいてください。

ブラウザで http://localhost にアクセスし、アプリケーションが正常に動作していることを確認してください。


## API エンドポイント一覧 (/api/v1/). 
Laravel Sanctum によるトークン認証が必要なエンドポイントを含みます。

| メソッド | エンドポイント | 説明 | 認証 |
| :--- | :--- | :--- | :---: |
| `GET` | `/api/v1/books` | 書籍一覧の取得 | 不要 |
| `POST` | `/api/v1/books` | 書籍の新規登録 | 要 (Sanctum) |
| `GET` | `/api/v1/books/{id}` | 特定の書籍詳細の取得 | 不要 |
| `PUT`/`PATCH` | `/api/v1/books/{id}` | 書籍情報の更新 | 要 (Sanctum) |
| `DELETE` | `/api/v1/books/{id}` | 書籍の削除 | 要 (Sanctum) |
| `GET` | `/api/v1/genres` | ジャンル一覧の取得 | 不要 |
| `POST` | `/api/v1/books/{id}/reviews` | レビューの投稿 | 要 (Sanctum) |
| `POST` | `/api/v1/books/{id}/favorite` | お気に入り（いいね）の登録・解除 | 要 (Sanctum) |

## データベース設計（主要テーブル）. 
* users: ユーザー情報（名前、メールアドレス、パスワード等）
* books: 書籍情報（ユーザーID、タイトル、著者、ISBN、出版日、説明、画像URL等）
* genres: ジャンル情報
* reviews:レビュー情報
* favorites:お気に入り情報
* reading_plans:読書計画
* notifications:通知関連(Laravel標準機能を使用（自動生成）)

## 🧪 テスト環境の構築と実行. 
本プロジェクトでは、フィーチャーテスト用にテスト用のデータベース（`mysql_test`）を使用したテスト環境を構築できます。

### 1. テスト用データベースの作成
まず、MySQLコンテナにログインします。
```
sail mysql
```
MySQLのプロンプトが表示されたら、テスト用のデータベースを手動で作成します。
```
CREATE DATABASE demo_test;
EXIT;
```

### 2. テスト用環境変数ファイルの作成. 
ルートディレクトリにある .env ファイルをコピーして、.env.testing を作成します。
```
cp .env .env.testing
```

### 3. .env.testing の設定内容の変更. 
作成した .env.testing ファイルを開き、以下の内容に書き換えて保存します。
```
APP_ENV=testing
APP_KEY=
DB_CONNECTION=mysql_test
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=testing_demo
DB_USERNAME=sail
DB_PASSWORD=password
```

### 4. テスト用アプリケーションキーの生成. 
```
sail artisan key:generate --env=testing
```

### 5. キャッシュのクリアとテスト用マイグレーションの実行. 
```
sail artisan config:clear
sail artisan migrate --env=testing
```

### 6. テストの実行. 
準備が完了したら、以下のコマンドでテストを実行します。
```
sail artisan test
```

### ⏰ バックグラウンド処理（リマインダー・期限切れ自動更新）の動作確認方法 
本アプリケーションでは、
読書計画のステータス自動更新および通知送信を
Laravelのタスクスケジュールで実装しています。
ローカル環境で動作確認を行う場合は、
別のターミナルを開いて以下のコマンドを実行してください。
``` 
./vendor/bin/sail artisan schedule:work 
```

##　ER図
![ER図](https://github.com/user-attachments/assets/5c6d3d07-9ac7-46ac-a428-7547121314b8)

## 備考. 
* 読書計画の新規作成時、「現在進行中」との重複のみバリデーション設定しています。

（完了や期限切れのものを再度読むこともあるので）

* 通知のバッジ処理は毎日20時に設定しています。
