<?php
require_once('config.php');
require_once('functions.php');
session_start();

// 〇logout機能でやること
// ・セッション内のデータ削除
// ・クッキーの無効化
// ・セッションの破棄

$user_id = $_SESSION['USER']['id'];
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

//セッションIDとLogin時刻をリセットする

$sql = "UPDATE `user` SET session_id = :session_id, login_at = :login_at, updated_at = now() WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':session_id',NULL);
$stmt->bindValue(':login_at',NULL);
$stmt->bindValue(':id', $user_id);
$stmt->execute();

//操作ログを登録する
$sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
$stmt_log = $pdo->prepare($sql_log);
$stmt_log->bindValue(':user_id',$user_id);
$stmt_log->bindValue(':action', $action_array['user_logout']."【ユーザーID:".$user_id."】");
$stmt_log->execute();

unset($pdo);

setcookie('CONTENTS', '', time()-86400);
setcookie('EMAIL', '', time()-86400);
setcookie('PASSWORD', '', time()-86400);
header('Location:'.SITE_URL.'login.php');

?>
