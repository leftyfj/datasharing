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
$recNo = getVersionNo();
$former_url = $_SERVER['HTTP_REFERER'];

if(strpos($former_url, SITE_URL.'index.php') == 0) {
  // CSRF対策↓
  //setToken();
  
  $id_to_edit = $_GET['id'];
  //データベースに接続する
  $pdo = connectDb();
  //sql文 $idのデータをSELECTする
  $sql = "SELECT * FROM `data` WHERE `id`=:id";
  //データベースからデータを取得する
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':id',$id_to_edit,PDO::PARAM_INT);
  $movie = $stmt->execute();
  $movie = $stmt->fetch();

  //データベースへの接続を解除する
  unset($pdo);

  $_SESSION['UPDATE'] = $id_to_edit;
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
  <title><?php echo h(SITE_TITEL); ?> | <?php echo h($recNo); ?></title>
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
          <li class="nav-item ml-4"><a href="personal_setting.php" class="nav-link text-white">個人設定</a></li>
          <li class="nav-item ml-4"><a href="admin.php" class="nav-link text-white">管理</a></li>
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
          <label for="ref">Ref.No.</label>
          <input type="text" name="ref" value="<?php echo h($movie['ref']);?>"class="form-control form-control">
          <input type="hidden" name="amend" value="<?php echo h($movie['id']);?>">
        </div>
        <div class="form-group mb-4">
          <label for="title">タイトル<span class="required">必須</span></label>
          <input type="text" name="title" value="<?php echo h($movie['title']);?>"class="form-control form-control">
        </div>
        <div class="form-group mb-4">
          <label for="year">年</label>
          <input type="text" name="year" value="<?php echo h($movie['year']);?>" class="form-control form-control">
        </div>
           <div class="form-group mb-4">
          <label for="genre">ジャンル</label>
          <input type="text" name="genre" value="<?php echo h($movie['genre']);?>"class="form-control form-control">
        </div>
           <div class="form-group mb-4">
          <label for="duration">公開期間</label>
          <input type="text" name="duration" value="<?php echo h($movie['duration']);?>"class="form-control form-control">
        </div>
        <div class="form-group mb-4">
          <label for="director">監督</label>
          <input type="text" name="director" value="<?php echo h($movie['director']);?>" class="form-control form-control">
        </div>
        <div class="form-group mb-4">
          <label for="writer">脚本</label>
          <input type="text" name="writer" value="<?php echo h($movie['writer']);?>" class="form-control form-control">
        </div>
           <div class="form-group mb-4">
          <label for="production">制作</label>
          <input type="text" name="production" value="<?php echo h($movie['production']);?>"class="form-control form-control">
        </div>
        <div class="form-group mb-4">
          <label for="actors">出演</label>
          <input type="text" name="actors" value="<?php echo h($movie['actors']);?>" class="form-control form-control">
        </div>
        <!-- <div class="form-group mb-4">
          <label for="description">内容</label>
          <input type="text" name="description" value="<?php //echo h($movie['description']);?>" class="form-control form-control">
        </div> -->
        <div class="form-group mb-4">
          <label for="description">内容</label>
          <!-- <input type="textarea" name="description" value="<?php echo h($movie['description']);?>" class="form-control form-control"> -->
          <textarea name="description" rows="3" class="form-control form-control"><?php echo h($movie['description']);?></textarea>
        </div>
        <div>
          <!-- <input type="hidden" name="token" value="<?php //echo h($_SESSION['sstoken']); ?>" /> -->
          <input type="hidden" name="confirm" value="confirm">
          <button type="submit" class="btn btn-info text-white mb-2">内容確認</button><br>
          <input type="button" class="btn btn-secondary text-white" onclick="history.back()" value="戻る">
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
