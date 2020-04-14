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
$error_message ='';
$flag='';
setToken();

if(empty($_SESSION['DATA']['title_ja'])) {
  $error_message ="邦題の入力は必須です";
} else {
  $title_ja = $_SESSION['DATA']['title_ja'];
}

$rank = $_SESSION['DATA']['rank'] == "" ? '':$_SESSION['DATA']['rank'];

$title_en = $_SESSION['DATA']['title_en'] == "" ? '': $_SESSION['DATA']['title_en'];
$year = $_SESSION['DATA']['year'] == "" ? '':$_SESSION['DATA']['year'];
$director = $_SESSION['DATA']['director'] == "" ? '': $_SESSION['DATA']['director'];
$producer = $_SESSION['DATA']['producer'] == "" ? '': $_SESSION['DATA']['producer'];
$starring = $_SESSION['DATA']['starring'] == "" ? '': $_SESSION['DATA']['starring'] ;
$prize = $_SESSION['DATA']['prize'] == "" ? '': $_SESSION['DATA']['prize'];

if(!empty($_POST)) {
  //新規登録か修正か判断する
  $pdo = connectDb();

  if(is_null($_SESSION['DATA']['amend_key'])) {

    //新規登録
    $sql = "INSERT INTO data (rank, title_ja, title_en, year, director, producer, starring, prize, created_at, created_by, updated_at, updated_by) VALUES(:rank, :title_ja, :title_en, :year, :director, :producer, :starring, :prize, now(), :created_by, now(), :updated_by)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':rank',$rank);
    $stmt->bindValue(':title_ja',$title_ja);
    $stmt->bindValue(':title_en',$title_en);
    $stmt->bindValue(':year',$year);
    $stmt->bindValue(':director',$director);
    $stmt->bindValue(':producer',$producer);
    $stmt->bindValue(':starring',$starring);
    $stmt->bindValue(':prize',$prize);
    $stmt->bindValue(':created_by',$user['id']);
    $stmt->bindValue(':updated_by',$user['id']);
  
    $flag = $stmt->execute();

    //操作ログを登録する
    $sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
    $stmt_log = $pdo->prepare($sql_log);
    $stmt_log->bindValue(':user_id',$user['id']);
    $stmt_log->bindValue(':action', $action_array['new_data']."【".$title_ja."】");
    $stmt_log->execute();

  } else {
    //修正
  
    $sql = "UPDATE data SET rank = :rank , title_ja = :title_ja, title_en = :title_en, year = :year, director =:director, producer =:producer, starring =:starring,prize =:prize, updated_at = now(), updated_by =:updated_by WHERE id =:id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':rank',$rank);
    $stmt->bindValue(':title_ja',$title_ja);
    $stmt->bindValue(':title_en',$title_en);
    $stmt->bindValue(':year',$year);
    $stmt->bindValue(':director',$director);
    $stmt->bindValue(':producer',$producer);
    $stmt->bindValue(':starring',$starring);
    $stmt->bindValue(':updated_by',$user['id']);
    $stmt->bindValue(':prize',$prize);
    $stmt->bindValue(':id',$_SESSION['DATA']['amend']);

    $flag = $stmt->execute();

    //操作ログを登録する
    $sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
    $stmt_log = $pdo->prepare($sql_log);
    $stmt_log->bindValue(':user_id',$user['id']);
    $stmt_log->bindValue(':action', $action_array['amend_data']."【".$title_ja."】");
    $stmt_log->execute();
  }

    // if($flag){
    //   echo'登録しました';
    // }
  //DBを切断
  unset($pdo);

}

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
          <li class="nav-item ml-4"><a href="admin.php" class="nav-link  disabled">ユーザー管理</a></li>
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
         <dd class="col-md-3 font-weight-bold">順位</dd>
          <dd class="col-md-9 "><?php echo $rank;?></dd>
          <dd class="col-md-3 font-weight-bold">邦題</dd>
          <?php if($error_message):?>
            <dd class="col-md-9 text-danger font-weight-bold"><?php echo $error_message;?></dd>
          <?php else:?>
            <dd class="col-md-9 "><?php echo $title_ja;?></dd>
          <?php endif;?>
          <dd class="col-md-3 font-weight-bold">原題</dd>
          <dd class="col-md-9 "><?php echo $title_en;?></dd>
          <dd class="col-md-3 font-weight-bold">公開年</dd>
          <dd class="col-md-9"><?php echo $year;?></dd>
          <dd class="col-md-3 font-weight-bold">監督</dd>
          <dd class="col-md-9"><?php echo $director;?></dd>
          <dd class="col-md-3 font-weight-bold">プロデューサー</dd>
          <dd class="col-md-9"><?php echo $producer;?></dd>
          <dd class="col-md-3 font-weight-bold">出演</dd>
          <dd class="col-md-9"><?php echo $starring;?></dd>
          <dd class="col-md-3 font-weight-bold">受賞</dd>
          <dd class="col-md-9"><?php echo $prize;?></dd>
          <div class="mt-3">
            <a href="data_edit.php?action=rewrite" class="btn btn-info text-white">戻る</a>
            <?php if(!empty($error_message)):?>
              <input class="btn btn-primary text-white ml-3 disabled" type="submit" value="登録する" />
            <?php else:?>
              <input class="btn btn-primary text-white ml-3" type="submit" value="登録する" />
            <?php endif; ?>
          </div>
        </dl>
      </form>
          <!-- <?php //if($title != "") :?>
            <form method="post" action="data_add_edit_done.php">
              <input type="hidden" name="title" value="<?php //echo $title;?>">
              <input type="hidden" name="company" value="<?php //echo $company;?>" >
              <input type="hidden" name="producer" value="<?php //echo $producer;?>" >
              <input type="hidden" name="director" value="<?php //echo $director;?>" >
              <input type="hidden" name="starring" value="<?php //echo $starring;?>" >
              <input type="hidden" name="prize_check" value=<?php //echo intval($starring);?>>
              <input type="hidden" name="times" value="<?php //echo $times;?>" >
              <input type="hidden" name="year" value="<?php //echo $year;?>" >
              <input type="hidden" name="record" value="<?php //echo $record;?>" >
              <input type="hidden" name="amend" value="<?php //echo $amend;?>">
              <input type='button' class="btn btn-info text-white my-3" onclick='history.back()' value='戻る'><br>
              <input type='submit' class="btn btn-primary text-white" value='登録'>
              <input type="hidden" name="token" value="<?php //echo h($_SESSION['sstoken']); ?>" />
            </form>
          <?php //endif; ?> -->
            
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
