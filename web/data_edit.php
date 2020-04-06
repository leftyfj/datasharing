<?php
//関数読み込み
require_once('config.php');
require_once('functions.php');
ini_set('display_errors',0);
error_reporting(0);
session_start();

$user = $_SESSION['USER'];

if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}

$former_url = $_SERVER['HTTP_REFERER'];

if(strpos($former_url, SITE_URL.'index.php') == 0) {

  $id = $_POST['movie'];
  //データベースに接続する
  $pdo = connectDb();
  //sql文 $idのデータをSELECTする
  $sql = "SELECT * FROM `data` WHERE `id`=:id";
  //データベースからデータを取得する
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':id',$id,PDO::PARAM_INT);
  $movie = $stmt->execute();
  $movie = $stmt->fetch();

  //データベースへの接続を解除する
  unset($pdo);
  
  $_SESSION['USER'] = $user;
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
  <title><?php echo SITE_TITEL; ?> | 登録・編集</title>
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
          <li class="nav-item ml-4"><a href="user_admin.php" class="nav-link text-white">ユーザー管理</a></li>
          <li class="nav-item ml-4"><a href="logout.php" class="nav-link text-white">ログアウト</a></li>
        </ul>
      </div>
		</div>

  </nav>
  </header>
  <main>
    <div class="container pc-only bg-light p-4">
      <h2><caption><i class="fas fa-edit" style="color:orange;"></i>&nbsp;データ登録・編集</caption></h2>
      <form action="data_check.php" method="post">
        <div class="form-group mb-4">
          <label for="rank">順位</label>
          <input type="text" name="rank" value="<?php echo $movie['rank'];?>"class="form-control form-control">
          <input type="hidden" name="amend" value="<?php echo $movie['id'];?>">
        </div>
        <div class="form-group mb-4">
          <label for="title_ja">作品名</label>
          <input type="text" name="title_ja" value="<?php echo $movie['title_ja'];?>"class="form-control form-control">
        </div>
        <div class="form-group mb-4">
          <label for="title_en">原題</label>
          <input type="text" name="title_en" value="<?php echo $movie['title_en'];?>"class="form-control form-control">
        </div>
        <div class="form-group mb-4">
          <label for="year">年</label>
          <input type="number" name="year" value="<?php echo $movie['year'];?>" class="form-control form-control">
        </div>
        <div class="form-group mb-4">
          <label for="director">監督</label>
          <input type="text" name="director" value="<?php echo $movie['director'];?>" class="form-control form-control">
        </div>
        <div class="form-group mb-4">
          <label for="producer">制作者</label>
          <input type="text" name="producer" value="<?php echo $movie['producer'];?>" class="form-control form-control">
        </div>
        <div class="form-group mb-4">
          <label for="starring">出演</label>
          <input type="text" name="starring" value="<?php echo $movie['starring'];?>" class="form-control form-control">
        </div>
        <div class="form-group mb-4">
          <label for="prize_check"">受賞</label>
          <input type="text" name="prize" value="<?php echo $movie['prize'];?>" class="form-control form-control">
        </div>
        <div class="btn-group-vertical">
          <button type="submit" class="btn btn-info text-white m-3">内容確認</button>
        </div>
      </form>
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
