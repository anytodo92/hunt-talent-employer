# 【求人企業】ログイン、TOP
## feature/registration

# 【求人企業】パスワードリマインダー
## feature/password-reminder

# 【求人企業】共通ヘッダー/フッター
## feature/common-header-footer

# 【求人企業】共通ヘッダー/フッター
## feature/common-header-footer

# 【求人企業】求人一覧/求人QA
## feature/offer-info-qanda

# inCulエージェントの概要

## サーバー環境
    PHP7.4
    MySQL5.7, MariaDB10
    
## ライブラリ
    Laravel8.35.1 (vue.js)
    Bootstrap4
    jQuery3.5.1
    vue.js v3

# 環境構築

## データベース
migrationを実行してテーブルを生成する。

    php artisan migrate
    
一般ユーザーと管理者ユーザーを生成する。

    php artisan db:seed

# バッチ処理

    php artisan schedule:work

# ソース説明

## Helper

app/Helpers/Helper.php 追加

app/Providers/AppServiceProvider.php に以下のコードを追加する。

    // Custom Helper functions
    $file = app_path('Helpers/Helper.php');
    if (file_exists($file)) {
        require_once($file);
    }

## Constants & Enum

コンスタントはグラスとして定義

    app/Constants.php
    
Enumはconfigとして定義 (config/constants.php)

    ...
    
    // ユーザータイプ
    'user_types' => [
        \App\Constants::USER_TYPE_COMPANY   => '求人企業',
        \App\Constants::USER_TYPE_RECRUIT   => '人材紹介',
        \App\Constants::USER_TYPE_OUTSOURCE => '業務委託SES',
        \App\Constants::USER_TYPE_ADMIN     => '運営企業',
    ],
    
    ...

Helperに「g_num」という関数がある。

    // 数値から表示文字列を取得する
    g_num('user_types', Constants::USER_TYPE_COMPANY);

    // リスト表示のため全ての項目を取得する
    $userTypes = g_num('user_types');
    foreach ($userTypes as $key => $val) {
        ...
    }
