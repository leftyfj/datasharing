<?php
//関数読み込み
require_once('config.php');
require_once('functions.php');
ini_set('display_errors',0);
error_reporting(0);
session_start();

$user = $_SESSION['USER'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  header('Location: '.SITE_URL.'login.php');
  exit;
} else {
  // CSRF対策↓
  checkToken();

  // $name = h($_POST['name']);
  // $address = h($_POST['address']);
  $id = h($_POST['amend']);

  //データベースに接続する
  $pdo = connectDb();

  if($id =='') {
    // データベース（itemテーブル）に新規登録する
    $sql = "INSERT INTO data (title, company, producer, director, starring, prize_check, times, year, record, created_at, created_by, updated_at) VALUES (:title, :company, :producer, :director, :starring, :prize_check, :times, :year, :record, now(), :created_by, now())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':title', h($_POST['title']));
    $stmt->bindValue(':company', h($_POST['company']));
    $stmt->bindValue(':producer', h($_POST['producer']));
    $stmt->bindValue(':director', h($_POST['director']));
    $stmt->bindValue(':starring', h($_POST['starring']));
    $stmt->bindValue(':prize_check', h($_POST['prize_check']));
    $stmt->bindValue(':times', intval(h($_POST['times'])));
    $stmt->bindValue(':year', intval(h($_POST['company'])));
    $stmt->bindValue(':record', h($_POST['record']));
    $stmt->bindValue(':created_by', $user['id']);
    $stmt->execute();

    $message_complete = "登録しました。";
  } else {

    // データを変更する
    // $sql = "UPDATE `item` SET `name`= :name, `address`= :address, `updated_at` =now() WHERE id= :id";
    $sql = "UPDATE data (title, company, producer, director, starring, prize_check, times, year, record, created_at, updated_at) VALUES (:title, :company, :producer, :director, :starring, :prize_check, :times, :year, :record, now(), now()) WHERE id= :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':title', h($_POST['title']));
    $stmt->bindValue(':company', h($_POST['company']));
    $stmt->bindValue(':producer', h($_POST['producer']));
    $stmt->bindValue(':director', h($_POST['director']));
    $stmt->bindValue(':starring', h($_POST['starring']));
    $stmt->bindValue(':prize_check', h($_POST['prize_check']));
    $stmt->bindValue(':times', intval(h($_POST['times'])));
    $stmt->bindValue(':year', intval(h($_POST['company'])));
    $stmt->bindValue(':record', h($_POST['record']));
    $stmt->bindValue(':id', $id);
    $stmt->execute();

    $message_complete = "修正しました。";
  }
  
  unset($pdo);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="../css/bootstrap.css">
  <link rel="stylesheet" href="../css/sanitize.css">
  <link rel="stylesheet" href="../css/style.css">
</head>
<!-- Google Web Font -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:400,700|Open+Sans:400,700&display=swap" rel="stylesheet">
<!-- Icon  Place your kit's code here -->
 <script src="https://kit.fontawesome.com/d7931251a6.js" crossorigin="anonymous"></script>
  <title><?php echo SITE_TITEL; ?></title>
</head>
<body  style="padding-top:70px;">
    <header>
    <nav class="nav navbar fixed-top navbar-expand-lg navbar-dark bg-dark text-white ">
		<div class="container ">
      <h1><a href="index.php" class="navbar-brand">
        データ共有システム
      </a></h1>
      <button class="navbar-toggler" data-toggle="collapse" data-target="#menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div id="menu" class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto"> <!--ml-autoを入れるとメニューが右寄せされる -->
          <li class="nav-item ml-4"><a href="index.php" class="nav-link text-white">一覧</a></li>
          <li class="nav-item ml-4"><a href="data_edit.php" class="nav-link text-white">登録・編集</a></li>
          <li class="nav-item ml-4"><a href="data_upload.php" class="nav-link text-white">一括登録</a></li>
          <li class="nav-item ml-4"><a href="user_admin" class="nav-link text-white">ユーザー管理</a></li>
          <li class="nav-item ml-4"><a href="logout.php" class="nav-link text-white">ログアウト</a></li>
        </ul>
      </div>
		</div>

  </nav>
  </header>
  <main>
    <div class="container bg-light p-4">
      <div class="alert text-center alert-success mx-auto w-50 pb-3"><p class="font-weight-bold"><?php echo $message_complete; ?></p></div>
    </div> <!--end container-->
  </main>
  <footer>
  </footer>

<!-- <script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.bundle.js"></script> -->
  <!-- ここから下記３行が抜けていた汗 -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

</body>
