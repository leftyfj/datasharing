<?php
//関数読み込み
require_once('config.php');
require_once('functions.php');
ini_set('display_errors',0);
error_reporting(0);
session_start();

if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}

$user = $_SESSION['USER'];
$former_url = $_SERVER['HTTP_REFERER'];

if(strpos($former_url, SITE_URL.'index.php') == 0) {

  $id_to_delete = $_GET['id'];
  //データベースに接続する
  $pdo = connectDb();
  //sql文 $idのデータをSELECTする
  $sql = "DELETE FROM `data` WHERE `id`=:id";
  //データベースからデータを取得する
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':id',$id_to_delete,PDO::PARAM_INT);
  $flag = $stmt->execute();

  //操作ログを登録する
  $sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
  $stmt_log = $pdo->prepare($sql_log);
  $stmt_log->bindValue(':user_id',$user['id']);
  $stmt_log->bindValue(':action', $action_array['delete_data']."【".$id_to_delete."】");
  $stmt_log->execute();

  //データベースへの接続を解除する
  unset($pdo);

  header('Location: '.SITE_URL.'index.php');

}

?>


