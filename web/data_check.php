<?php
//関数読み込み
require_once('config.php');
require_once('functions.php');
ini_set('display_errors',0);
error_reporting(0);
session_start();

echo '1 ';
if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}
echo '2 ';
$recNo = getVersionNo();
$user = $_SESSION['USER'];
$error_message ='';
$flag='';

//setToken();
echo '3 ';
$ref = $_SESSION['DATA']['ref'] == "" ? '':$_SESSION['DATA']['ref'];
if(empty($_SESSION['DATA']['title'])) {
  $error_message ="タイトルの入力は必須です";
} else {
  $title = $_SESSION['DATA']['title'];
}
$year = $_SESSION['DATA']['year'] == "" ? '':$_SESSION['DATA']['year'];
$genre = $_SESSION['DATA']['genre'] == "" ? '': $_SESSION['DATA']['genre'];
$duration = $_SESSION['DATA']['duration'] == "" ? '': $_SESSION['DATA']['duration'];
$director = $_SESSION['DATA']['director'] == "" ? '': $_SESSION['DATA']['director'];
$writer = $_SESSION['DATA']['writer'] == "" ? '': $_SESSION['DATA']['writer'];
$production = $_SESSION['DATA']['production'] == "" ? '': $_SESSION['DATA']['production'];
$actors = $_SESSION['DATA']['actors'] == "" ? '': $_SESSION['DATA']['actors'];
$description = $_SESSION['DATA']['description'] == "" ? '': $_SESSION['DATA']['description'] ;
echo '4 ';
 // CSRF対策↓
	setToken();
if(!empty($_POST)) {
echo '5 ';
// CSRF対策↓
echo h($_SESSION['sstoken']);
 checkToken();
 // CSRF対策↓
	//setToken();
  //新規登録か修正か判断する
  $pdo = connectDb();

  if(is_null($_SESSION['DATA']['amend_key'])) {

    //新規登録
    $sql = "INSERT INTO data (ref, title, year, genre, duration, director, writer, production, actors, description, created_at, created_by, updated_at, updated_by) VALUES(:ref, :title, :year, :genre, :duration, :director, :writer, :production, :actors, :description, now(), :created_by, now(), :updated_by)";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':ref',$ref);
    $stmt->bindValue(':title',$title);
    $stmt->bindValue(':year',$year);
    $stmt->bindValue(':genre',$genre);
    $stmt->bindValue(':duration',$duration);
    $stmt->bindValue(':director',$director);
    $stmt->bindValue(':writer',$writer);
    $stmt->bindValue(':production',$production);
    $stmt->bindValue(':actors',$actors);
    $stmt->bindValue(':description',$description);

    $stmt->bindValue(':created_by',$user['id']);
    $stmt->bindValue(':updated_by',$user['id']);
  
    $flag = $stmt->execute();

    if($flag) {
       //操作ログを登録する
      $sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
      $stmt_log = $pdo->prepare($sql_log);
      $stmt_log->bindValue(':user_id',$user['id']);
      $stmt_log->bindValue(':action', $action_array['new_data']."【".$title."】");
      $stmt_log->execute();
    }
   
echo '6 ';
  } else {
    //修正
    // CSRF対策↓
    checkToken();
   echo '7 '; 
    $sql = "UPDATE data SET ref = :ref, title =:title, genre = :genre, duration = :duration, year = :year, director = :director, 
    writer = :writer, production = :production, actors = :actors, description = :description, updated_by = :updated_by WHERE id =:id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':ref',$ref);
    $stmt->bindValue(':title',$title);
    $stmt->bindValue(':year',$year);
    $stmt->bindValue(':genre',$genre);
    $stmt->bindValue(':duration',$duration);
    $stmt->bindValue(':director',$director);
    $stmt->bindValue(':writer',$writer);
    $stmt->bindValue(':production',$production);
    $stmt->bindValue(':actors',$actors);
    $stmt->bindValue(':description',$description);
    $stmt->bindValue(':updated_by',$user['id']);
    $stmt->bindValue(':id',$_SESSION['DATA']['amend']);

    $flag = $stmt->execute();
echo '8 ';
    if($flag) {
      //操作ログを登録する
      $sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
      $stmt_log = $pdo->prepare($sql_log);
      $stmt_log->bindValue(':user_id',$user['id']);
      $stmt_log->bindValue(':action', $action_array['amend_data']."【".$ref." | ".$title."】");
      $stmt_log->execute();
    }
 echo '9 ';   
  }

    // if($flag){
    //   echo'登録しました';
    // }
  //DBを切断
  unset($pdo);

}
echo '10';
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
  <title><?php echo h(SITE_TITEL); ?> | <?php echo h($recNo); ?></title>
</head>

<body  style="padding-top:110px;">
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
          <li class="nav-item ml-4"><a href="index.php" class="nav-link disabled">一覧</a></li>
          <li class="nav-item ml-4"><a href="data_edit.php" class="nav-link disabled">登録・編集</a></li>
          <li class="nav-item ml-4"><a href="data_upload.php" class="nav-link disabled">一括登録</a></li>
          <li class="nav-item ml-4"><a href="personal_setting.php" class="nav-link text-white">個人設定</a></li>
          <li class="nav-item ml-4"><a href="admin.php" class="nav-link  disabled">管理</a></li>
          <li class="nav-item ml-4"><a href="logout.php" class="nav-link disabled">ログアウト</a></li>
        </ul>
      </div>
		</div>
  </nav>
  </header>
  <main>
    <div class="container pc-only bg-light p-4">
      <h2><caption><i class="fas fa-edit" style="color:orange;"></i>&nbsp;内容確認</caption></h2>
      <form action="" method="post">
        <input type="hidden" name="action" value="submit" />
        <dl class="row">
         <dd class="col-md-3 font-weight-bold">Ref.No.</dd>
          <dd class="col-md-9 "><?php echo h($ref);?></dd>
          <dd class="col-md-3 font-weight-bold">タイトル</dd>
          <?php if($error_message):?>
            <dd class="col-md-9 text-danger font-weight-bold"><?php echo h($error_message);?></dd>
          <?php else:?>
            <dd class="col-md-9 "><?php echo h($title);?></dd>
          <?php endif;?>
          <dd class="col-md-3 font-weight-bold">公開年</dd>
          <dd class="col-md-9"><?php echo h($year);?></dd>
          <dd class="col-md-3 font-weight-bold">ジャンル</dd>
          <dd class="col-md-9"><?php echo h($genre);?></dd>
          <dd class="col-md-3 font-weight-bold">公開期間</dd>
          <dd class="col-md-9"><?php echo h($duration);?></dd>
          <dd class="col-md-3 font-weight-bold">監督</dd>
          <dd class="col-md-9"><?php echo h($directorh);?></dd>
          <dd class="col-md-3 font-weight-bold">脚本</dd>
          <dd class="col-md-9"><?php echo h($writer);?></dd>
          <dd class="col-md-3 font-weight-bold">制作</dd>
          <dd class="col-md-9"><?php echo h($production);?></dd>
          <dd class="col-md-3 font-weight-bold">出演</dd>
          <dd class="col-md-9"><?php echo h($actors);?></dd>
          <dd class="col-md-3 font-weight-bold">内容</dd>
          <dd class="col-md-9"><?php echo h($description);?></dd>
          <div class="mt-3">
          <?php echo h($_SESSION['sstoken']); ?>
             <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
            <?php if(!empty($error_message)):?>
              <input class="btn btn-primary text-white ml-3 disabled" type="submit" value="登録する" /><br>
            <?php else:?>
              <input class="btn btn-primary text-white ml-3" type="submit" value="登録する" /><br>
            <?php endif; ?>
            <input type="button" class="btn btn-secondary text-white m-3" onclick="history.back()" value="戻る">
          </div>
        </dl>
      </form>
    </div> <!--end row--> 
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
      <div class="alert alert-success fade show text-center">登録完了しました<span id="close" class="font-weight-bold">&nbsp;&times;</span></div>
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
  <script src="../js/modal.js"></script>
</body>
