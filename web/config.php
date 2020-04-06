<?php

//警告メッセージを表示させない
error_reporting(E_ALL & ~E_NOTICE);

//ローカルホスト
define('HOST', 'localhost');
define('USER', 'root');
define('PASS', 'hisa59');
define('DB', 'oscar');
define('SITE_URL', 'http://localhost/dev/datasharing/web/');
   
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
    define('SITE_TITEL', 'DataSharing_OSCAR');
//ペーネーション
    define('PAGE_COUNT', 10);


?>