<?php
//関数読み込み
require_once('config.php');
require_once('functions.php');
ini_set('display_errors',0);
error_reporting(0);
session_start();

$user = $_SESSION['USER'];

$amend = h($_POST['amend']);

setToken();

$title = $_POST['title'] == "" ? '未入力': $_POST['title'];
$company = $_POST['company'] == "" ? '未入力': $_POST['company'];
$producer = $_POST['producer'] == "" ? '未入力': $_POST['producer'];
$director = $_POST['director'] == "" ? '未入力': $_POST['director'];
$starring = $_POST['starring'] == "" ? '未入力': $_POST['starring'];
$prize_check = $_POST['prize_check'] ==1 ? '受賞' : 'ノミネート';
$times = $_POST['times'] == "" ? '未入力': $_POST['times'];
$year = $_POST['year'] == "" ? '未入力': $_POST['year'];
$record = $_POST['record'] == "" ? '未入力': $_POST['record'];

$_SESSION['USER'] =$user;
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
    <div class="container pc-only bg-light p-4">
      <h2><caption><i class="fas fa-edit" style="color:orange;"></i>&nbsp;内容確認</caption></h2>
        <dl class="row">
          <dd class="col-md-3 font-weight-bold">作品名</dd>
          <dd class="col-md-9 "><?php echo $title;?></dd>
          <dd class="col-md-3 font-weight-bold">制作会社</dd>
          <dd class="col-md-9"><?php echo $company;?></dd>
          <dd class="col-md-3 font-weight-bold">プロデューサー</dd>
          <dd class="col-md-9"><?php echo $producer;?></dd>
          <dd class="col-md-3 font-weight-bold">監督</dd>
          <dd class="col-md-9"><?php echo $director;?></dd>
          <dd class="col-md-3 font-weight-bold">主演</dd>
          <dd class="col-md-9"><?php echo $starring;?></dd>
          <dd class="col-md-3 font-weight-bold">受賞</dd>
          <dd class="col-md-9"><?php echo $prize_check;?></dd>
          <dd class="col-md-3 font-weight-bold">回</dd>
          <dd class="col-md-9"><?php echo $times;?></dd>
          <dd class="col-md-3 font-weight-bold">年</dd>
          <dd class="col-md-9"><?php echo $year;?></dd>
          <dd class="col-md-3 font-weight-bold">記録</dd>
          <dd class="col-md-9"><?php echo $record;?></dd>

          <!-- <input type='button' class="btn btn-info text-white m-3" onclick='history.back()' value='戻る'> -->

        </dl>
          <?php if($title != "") :?>
            <form method="post" action="data_add_edit_done.php">
              <input type="hidden" name="title" value="<?php echo $title;?>">
              <input type="hidden" name="company" value="<?php echo $company;?>" >
              <input type="hidden" name="producer" value="<?php echo $producer;?>" >
              <input type="hidden" name="director" value="<?php echo $director;?>" >
              <input type="hidden" name="starring" value="<?php echo $starring;?>" >
              <input type="hidden" name="prize_check" value=<?php echo intval($starring);?>>
              <input type="hidden" name="times" value="<?php echo $times;?>" >
              <input type="hidden" name="year" value="<?php echo $year;?>" >
              <input type="hidden" name="record" value="<?php echo $record;?>" >
              <input type="hidden" name="amend" value="<?php echo $amend;?>">
              <input type='button' class="btn btn-info text-white my-3" onclick='history.back()' value='戻る'><br>
              <input type='submit' class="btn btn-primary text-white" value='登録'>
              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
              </form>
          <?php endif; ?>

    </div> <!--end row--> 
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
