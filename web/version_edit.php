<?php
//関数読み込み
require_once('config.php');
require_once('functions.php');
ini_set('display_errors',0);
error_reporting(0);
session_start();

if ($_SESSION['USER']['admin_check'] =='0') {
    header('Location: '.SITE_URL.'index.php');
    exit;
}
$recNo = getVersionNo();
$user = $_SESSION['USER'];
$version_id_to_edit = $_GET['id'];

$flag ='';

$former_url = $_SERVER['HTTP_REFERER'];
if($_SERVER['REQUEST_METHOD']!="POST"){

  //データベースに接続する
  $pdo = connectDb();

  //sql文 $idのデータをSELECTする
  $sql = "SELECT * FROM `version` WHERE `id`=:id";
  //データベースからデータを取得する
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':id',$version_id_to_edit,PDO::PARAM_INT);
  $stmt->execute();
  $version_to_edit = $stmt->fetch();

  $changes = $version_to_edit['changes'];

  //データベースへの接続を解除する
  unset($pdo);
} else {
  // CSRF対策↓
    //setToken();
    // CSRF対策↓
    //checkToken();
  $changes = $_POST['changes'];
  // 入力チェック
  // 配列の定義
  $error_message = array();

  //文字数制限チェック
  $error_message['changes'] = halfstrCountCheck($changes, 200);
  if ($changes == '') {
     // エラーメッセージを配列に保存
    $error_message['changes'] = '内容を入力して下さい。';
  }

//もし$err配列に何もエラーメッセージが保存されていなかったら
  //配列$error_messageの各要素がNULLかチェック
  foreach($error_message as $err) {
    if (!empty($err)) {
      break;
    }
  }
    // データベース（cm_userテーブル）にUPDATEする
    //データベースに接続する
  $pdo = connectDb();

  if(empty($err)) {
    $stmt = $pdo->prepare("UPDATE version SET changes = :changes, updated_at = now() WHERE id = :id");
    $stmt->bindValue(':changes', $changes);
    $stmt->bindValue(':id', $version_id_to_edit);
    $flag = $stmt->execute();

    //操作ログを登録する
    $sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
    $stmt_log = $pdo->prepare($sql_log);
    $stmt_log->bindValue(':user_id',$user['id']);
    $stmt_log->bindValue(':action', $action_array['amend_version']."【バージョン.NO.".$version_id_to_edit."】");
    $stmt_log->execute();

    //データベースへの接続を解除する
    unset($pdo);
    // ↓セッションハイジャック対策
    session_regenerate_id(true);
    //UPDATE後のデータにセッションを更新する
    $_SESSION['USER'] = $user;
    
    }
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
   
      <div class="container bg-light p-3">
      <h2 id="inlineblock_for_over768" class="font-weight-bold"><caption><i class="fas fa-user-alt" style="color:orange;"></i></caption>&nbsp;バージョン変更履歴修正&nbsp;</h2>
        <form action="" method="POST" class="mb-2">
        <div class="form-group">
          <label for="changes">内容<span class="required">必須</span></label>
          <textarea type="text" row="3" name="changes" value="<?php echo h($changes);?>"class="form-control form-control"><?php echo h($changes);?></textarea>
        </div> <!--end form-group -->
        
            <input class="btn btn-info mb-2" type="submit" value="修正"><br>
            <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
            <input type="button"  class="btn btn-secondary text-white" onclick="history.back()" value="戻る">
        </form>
      </div>
    <hr>
    </div>
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
      <div class="alert alert-success fade show text-center">変更内容を登録しました<span id="close" class="font-weight-bold">&nbsp;&times;</span></div>
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
  <script src="../js/modal_version.js"></script>
</body>
