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

  //データベースへの接続を解除する
  unset($pdo);

  header('Location: '.SITE_URL.'index.php');

}

?>


