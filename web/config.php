<?php

//警告メッセージを表示させない
error_reporting(E_ALL & ~E_NOTICE);

//ローカルホスト
define('HOST', 'localhost');
define('USER', 'root');
define('PASS', 'hisa59');
define('DB', 'oscar');
define('SITE_URL', 'http://localhost/dev/datasharing/web/');
//modal.jsのsiteUrlを書き直すこと

    // データベース情報

  //さくらインターネット
    // define('HOST', 'mysql734.db.sakura.ne.jp');
    // define('USER', 'castleglengarry');
    // define('PASS', 'fuk8190168');
    // define('DB', 'castleglengarry_oscar');
    // define('SITE_URL', 'http://castleglengarry.sakura.ne.jp/datasharing/web/');
    
    // define('IMAGES_DIR', __DIR__.'/uploads');
    // define('THUMBNAIL_DIR', __DIR__.'/uploads/thumbnail');

//管理者メールアドレス
    define('ADMIN_EMAIL', 'gwall59@gmail.com');

//サイトtitle
    define('SITE_TITEL', 'DataSharing');
//ペーネーション
    define('PAGE_COUNT', 10);

//操作アクション
$action_array = array(
    'new_data' => 'データ新規登録',
    'amend_data' => 'データ修正',
    'delete_data' => 'データ削除',
    'download_data' => 'データダウンロード',
    'upload_collectively' => 'データ一括登録',
    'user_login' => 'ユーザーログイン',
    'user_logout' => 'ユーザーログアウト',
    'delete_user' => 'ユーザー登録削除',
    'amend_user' => 'ユーザー情報修正',
    'new_user' => 'ユーザー新規登録',
    'new_version' => 'バージョン変更_内容登録',
    'amend_version' => 'バージョン変更履歴修正',
    'delete_version' => 'バージョン履歴削除'
)
?>