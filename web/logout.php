<?php
require_once('config.php');
require_once('functions.php');
session_start();

// 〇logout機能でやること
// ・セッション内のデータ削除
// ・クッキーの無効化
// ・セッションの破棄

//セッション内のデータ削除
$_SESSION['USER'] = array();

//クッキーの無効化
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-86400, '/dev/contentsmaker/web/');
}

//セッションの破棄
session_destroy();

//ログイン情報のクッキーを破棄
$random_key = $_COOKIE['CONTENTS'];
$pdo = connectDb();

$stmt = $pdo->prepare("DELETE FROM `auto_login` WHERE `c_key`= :c_key");
$stmt->bindValue(':c_key', $random_key);
$flag = $stmt->execute();

unset($pdo);

setcookie('CONTENTS', '', time()-86400);
setcookie('EMAIL', '', time()-86400);
setcookie('PASSWORD', '', time()-86400);
header('Location:'.SITE_URL.'login.php');

?>
