<?php
//関数読み込み
require_once('config.php');
require_once('functions.php');
ini_set('display_errors',0);
error_reporting(0);
session_start();

$user = $_SESSION['USER'];
$flag="";
if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}

$recNo = getVersionNo();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // 初めて画面にアクセスした時の処理
    // CSRF対策↓
    setToken();

     //データベースに接続
    $pdo = connectDb();
  
    $sql = "SELECT `show_data` FROM `user` WHERE id =:id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id',$user['id']);
    $stmt->execute();
    $data = $stmt->fetch();
    //データベース接続を切断する
    unset($pdo);
} else {

   //データベースに接続
  $pdo = connectDb();
  $show_data =intval($_POST['show_data']);

  $sql = "UPDATE `user` SET show_data = :show_data ,updated_at = now() WHERE id =:id";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':show_data',$show_data);
  $stmt->bindValue(':id',$user['id']);
  $flag = $stmt->execute();

  //操作ログを登録する
  $sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
  $stmt_log = $pdo->prepare($sql_log);
  $stmt_log->bindValue(':user_id',$user['id']);
  $stmt_log->bindValue(':action', $action_array['amend_data']."【".$title_ja."】");
  $stmt_log->execute();
  
  //データベース接続を切断する
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
  <title><?php echo SITE_TITEL; ?> | <?php echo $recNo; ?></title>
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
          <li class="nav-item ml-4"><a href="admin.php" class="nav-link text-white">管理</a></li>
          <li class="nav-item ml-4"><a href="logout.php" class="nav-link text-white">ログアウト</a></li>
        </ul>
      </div>
		</div>

  </nav>
  </header>
  <main>
    <div class="container pc-only bg-light p-4">
      <h2><caption><i class="fas fa-sliders-h" style="color:orange;"></i>&nbsp;個人設定</caption></h2>
      <div class="panel-body">
        <form action="" method="POST">
          <div  class="form-group">
            <label for="show_data" class="font-weight-bold"><span class="fontsize_responsive">データ表示件数</span><span>&nbsp;(1ページ当たりの表示件数を設定します)</span></label>
            <select  class="form-control" name="show_data">
              <?php for($i=5; $i<21; $i++):?>
                <?php if($i==$data['show_data']):?>
                  <option value= "<?php echo $i; ?>" selected> <?php echo $i;?></option>
                <?php else:?>
                <option value= "<?php echo $i; ?>"> <?php echo $i;?></option>
                <?php endif;?>
              <?php endfor; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-info  mb-2">設定</button>
          <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
        </form>
      </div><!--end panel-body-->
    </div> <!--end container-->
  </main>
  <?php if($flag):?>
      <div id="mask">
    <?php else: ?>
      <div id="mask" class="hidden">
    <?php  endif;?>
      </div>
    <?php if($flag):?>
      <section id="modal">
    <?php else: ?>
      <section id="modal" class="hidden">
    <?php  endif;?>
      <div class="alert alert-success fade show text-center">設定しました<span id="close" class="font-weight-bold">&nbsp;&times;</span></div>
      <!-- <div id="close">閉じる</div> -->
      </section>
  <footer>
  </footer>

<!-- <script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.bundle.js"></script> -->
  <!-- ここから下記３行が抜けていた汗 -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<script src="../js/modal_setting.js"></script>
</body>
