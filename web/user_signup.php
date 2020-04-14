<?php
require_once('config.php');
require_once('functions.php');
session_start();
error_reporting(0);

$user = $_SESSION['USER'];

if ($_SESSION['USER']['admin_check'] =='0') {
    header('Location: '.SITE_URL.'index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // 初めて画面にアクセスした時の処理
    // CSRF対策↓
    setToken();
} else {
    $flag = '';
    // フォームからサブミットされた時の処理
    // CSRF対策↓
    checkToken();
    // 処理1
    // 入力されたニックネーム、メールアドレス、パスワードを受け取り、変数に入れる。
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];
    $admin_check = $_POST['admin'];

    // データベースに接続する（PDOを使う）
    $pdo = connectDb();
    // 処理2
    // 配列の定義
    $error_message = array();

    //文字数制限チェック
    $error_message['user_name'] = halfstrCountCheck($user_name, 20);
    $error_message['user_email'] = halfstrCountCheck($user_email, 50);
    $error_message['user_password'] = halfstrCountCheck($user_password, 20);

    // 入力チェック
    if ($user_name == '') {
    // エラーメッセージを配列に保存
	    $error_message['user_name'] = 'ユーザーネームを入力して下さい。';
    }

    if ($user_email == '') {
    // エラーメッセージを配列に保存
	    $error_message['user_email'] = 'メールアドレスを入力して下さい。';
    } else {
        //メールアドレスの形式チェック
        if(!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $error_message['user_email'] = '正しくメールアドレスを入力してください';
        } else {
            //メールアドレスの重複有無チェック
            if (checkEmail($user_email, $pdo)) {
                 $error_message['user_email'] = 'メールアドレスが既に登録されています。他のメールアドレスで登録してください。';
            }
        }

    }

    if ($user_password == '') {
    // エラーメッセージを配列に保存
	    $error_message['user_password'] = 'パスワードを入力して下さい。';
    }

    //配列$error_messageの各要素がNULLかチェック
    foreach($error_message as $err) {
      if (!empty($err)) {
      break;
      }
    }

    // もし$err配列に何もエラーメッセージが保存されていなかったら
    if (empty($err)) {
	// DB登録処理
        // 処理3
        // データベース（cm_userテーブル）に新規登録する。
        $stmt = $pdo->prepare("INSERT INTO user (user_name, user_email, user_password, admin_check, created_at, updated_at)
        VALUES (:user_name, :user_email, :user_password, :admin_check, now(), now())");

        $stmt->bindValue(':user_name', $user_name);
        $stmt->bindValue(':user_email', $user_email);
        $stmt->bindValue(':user_password', password_hash($user_password, PASSWORD_DEFAULT));
        $stmt->bindValue(':admin_check', intval($admin_check));
        $flag = $stmt->execute();

        //操作ログを登録する
        $sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
        $stmt_log = $pdo->prepare($sql_log);
        $stmt_log->bindValue(':user_id',$user['id']);
        $stmt_log->bindValue(':action', $action_array['new_user']);
        $stmt_log->execute();
        //自動ログイン
        //$user = getUser($user_email, $user_password, $pdo);
        // ↓セッションハイジャック対策
        session_regenerate_id(true);

        

    }

    unset($pdo);
}
$_SESSION['USER'] = $user;

?>;


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
  <title><?php echo SITE_TITLE; ?> | ユーザー登録</title>
</head>
<body>
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
          <li class="nav-item ml-4"><a href="index.php" class="nav-link text-white">データ一覧</a></li>
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
    <div class="container bg-light pc-only p-4">
          <h2 ><caption><i class="fas fa-users-cog" style="color:orange;"></i></caption>&nbsp;ユーザー登録 &ensp;</h2>
          <form method="post" >
            <div class="form-group <?php if($error_message['user_name'] !='') echo 'has-error'; ?>">
                <label class="font-weight-bold" for="user_name">ユーザーネーム<small>&emsp;(半角英数字20文字以内)</small></label><br/>
                <input id="user_name" class="form-control" type="text" name="user_name" value ="<?php echo h($user_name);?>" placeholder="ユーザーネーム">
                <span class="help-block text-danger"><?php echo $error_message['user_name']; ?></span>
            </div>
            <div class="form-group <?php if($error_message['user_email'] !='') echo 'has-error'; ?>">
                <label class="font-weight-bold">メールアドレス<small>&emsp;(半角英数字50文字以内)</small></label><br/>
                <input class="form-control" type="text" name="user_email" value ="<?php echo h($user_email);?>" placeholder="メールアドレス">
                <span class="help-block text-danger"><?php echo $error_message['user_email']; ?></span>
            </div>
            <div class="form-group <?php if($error_message['user_password'] !='') echo 'has-error'; ?>">
                <label class="font-weight-bold">パスワード<small>&emsp;(半角英数字20文字以内)</small></label><br/>
                <input class="form-control" type="password" name="user_password" value ="<?php echo h($user_password);?>" placeholder="パスワード">
                <span class="help-block text-danger"><?php echo $error_message['user_password']; ?></span>
            </div>
            <div class="form-group mb-4">
              <label class="font-weight-bold mr-4">アクセス権限</label>
              <div class="form-check form-check-inline mr-4">
                <input class="form-check-input" type="radio" name="admin" id="admin" value="1">
                <label class="form-check-label" for="admin">管理者</label>
              </div>
              <div class="form-check form-check-inline mr-4">
                <input class="form-check-input" type="radio" name="admin" id="nonadmin" value="0" checked>
                <label class="form-check-label" for="nonadmin">利用者</label>
              </div>
            </div>
            <button type="submit" class="btn btn- btn-primary mb-2">登録</button><br>
            <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
            <input type="button" class="btn btn- btn-success mb-2" onclick="history.back()" value="戻る">
        </form>

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
      <div class="alert alert-success fade show text-center">新規ユーザー登録完了しました<span id="close" class="font-weight-bold">&nbsp;&times;</span></div>
      <!-- <div id="close">閉じる</div> -->
      </section>
  <footer>
  </footer>

<!-- <script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.bundle.js"></script>  -->
  <!-- ここから下記３行が抜けていた汗 -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  <!-- <script src="../js/jquery.min.js"></script>
  <script src="../js/bootstrap.bundle.js"></script> -->
  <script src="../js/modal_newuser.js"></script>
</body>
