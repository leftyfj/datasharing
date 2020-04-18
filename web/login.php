<?php
//関数読み込み
require_once('config.php');
require_once('functions.php');
ini_set('display_errors',0);
error_reporting(0);
session_start();

$user = $_SESSION['USER'];
$recNo = getVersionNo();
//データベースに接続する（PDOを使う）
$pdo = connectDb();

//ユーザーネームをDBから取得し配列を作る
$sql = "SELECT `user_name` FROM `user`";
$stmt = $pdo->query($sql);
$users = $stmt->fetchALL();


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // CSRF対策↓
    setToken();
    // 初めて画面にアクセスした時の処理
    if($_COOKIE['USERNAME']!=''){
      $user_email = $_COOKIE['USERNAME'];
    }

    if($_COOKIE['PASSWORD']) {
      $user_password =$_COOKIE['PASSWORD'];
    }

    // ログインされた状態ならindex.phpへされていない場合はlogin.phpへ
    // 遷移させる。
    // if($user) {
    //   $clickLogo = "./index.php";
    // } else {
    //   $clickLogo = "./login.php";
    // }

} else {
    //POSTリクエスト
    // CSRF対策↓
    checkToken();

    // フォームからサブミットされた時の処理
    // 処理1
    // 入力されたメールアドレス、パスワードを受け取り、変数に入れる。
    $user_name = $_POST['user_name'];
    $user_password = $_POST['user_password'];

    //データベースに接続する（PDOを使う）
    $pdo = connectDb();

    $error_message = array();

    if ($user_password == '') {
    // エラーメッセージを配列に保存
	    $error_message['user_password'] = 'パスワードを入力して下さい。';
    } else {

        $user = getUserByUserName($user_name, $user_password, $pdo);

        if(!$user) {
            $error_message['user_password'] ="パスワードが正しくありません。";
        }
    }

    // 自動ログイン情報を一度クリアする。
    if(isset($_COOKIE['CONTENTS'])) {

      $random_key = $_COOKIE['CONTENTS'];
      $pdo = connectDb();

      $stmt = $pdo->prepare("DELETE FROM `auto_login` WHERE `c_key`= :c_key");
      $stmt->bindValue(':c_key', $random_key);
      $flag = $stmt->execute();

      unset($pdo);

      setcookie('CONTENTS', '', time()-86400);

  }

    if(empty($error_message)) {

        // ↓セッションハイジャック対策
        session_regenerate_id(true);
        //ログインに成功したのでセッションにユーザーデータを保存する
        $_SESSION['USER'] = $user;

        // 自動ログインにチェックがあるので認証済情報のクッキーを保存する
        if($_POST['save'] === 'on') {
            $random_key = sha1(uniqid(mt_rand(), true));
            $expirery_date = time() + 60*60*24*14;
            setcookie('CONTENTS', $random_key, $expirery_date);
            setcookie('EMAIL', $user_email, $expirery_date);
            setcookie('PASSWORD', $user_password, $expirery_date);

            // データベース（auto_loginテーブル）に新規登録する。
            $stmt = $pdo->prepare("INSERT INTO `auto_login` (`user_id`, `c_key`, `expire`, `created_at`, `updated_at`)
    VALUES (:user_id, :c_key, :expire, now(), now())");  

            $stmt->bindValue(':user_id', intval($user['id']));
            $stmt->bindValue(':c_key', $random_key);
            $stmt->bindValue(':expire', date('Y-m-d H:i:s', $expirery_date));
            $stmt->execute();

        }

        

        //操作ログを登録する
        $sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
        $stmt_log = $pdo->prepare($sql_log);
        $stmt_log->bindValue(':user_id',$user['id']);
        $stmt_log->bindValue(':action', $action_array['user_login']."【ユーザーID:".$user['id']."】");
        $stmt_log->execute();

        $_SESSION['USER'] = $user;
        header('Location: '.SITE_URL);
        exit;
        unset($pdo);

    }

    /* ログインされた状態ならindex.phpへされていない場合はlogin.phpへ
    遷移させる。 */
    if($user) {
      $clickLogo = "./index.php";
    } else {
      $clickLogo = "./login.php";
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
          <li class="nav-item ml-4"><a href="index.php" class="nav-link disabled">一覧</a></li>
          <li class="nav-item ml-4"><a href="data_edit.php" class="nav-link disabled">登録・編集</a></li>
          <li class="nav-item ml-4"><a href="data_upload.php" class="nav-link disabled">一括登録</a></li>
          <li class="nav-item ml-4"><a href="personal_setting.php" class="nav-link disabled">個人設定</a></li>
          <li class="nav-item ml-4"><a href="admin.php" class="nav-link disabled">管理</a></li>
          <li class="nav-item ml-4"><a href="logout.php" class="nav-link disabled">ログアウト</a></li>
        </ul>
      </div>
		</div>

  </nav>
  </header>
  <main>
    <div class="container pc-only bg-light p-4">
      <h2><caption><i class="fas fa-sign-in-alt" style="color:orange;"></i>&nbsp;ログイン</caption></h2>
      <div class="panel-body">
        <form action="" method="POST">
          <div  class="form-group">
            <label for="user_name" class="font-weight-bold"><span class="fontsize_responsive">ユーザーネーム</span></label>
            <select  class="form-control" name="user_name">
              <option value= ""></option>
              <?php foreach ($users as $user): ?>
                <option value= "<?php echo h($user['user_name']); ?>"> <?php echo h($user['user_name']);?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="password" class="font-weight-bold"><span class="fontsize_responsive">パスワード</span></label>
            <input id="password" class="form-control" type="password" name="user_password" value="<?php echo h($user_password);?>">
            <?php if($error_message['user_password'] !=''): ?>
              <small class="error text-danger"><?php echo h($error_message['user_password']);?></small>
            <?php endif;?>
          </div> <!--end form-group -->
          <button type="submit" class="btn btn-info  mb-2">ログイン</button>
          <div class="mt-3">
            <input type="checkbox" name="save" value="on"><span class="fontsize_responsive">次回から自動でログイン</span>
          </div>
            <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
        </form>
          <div class="mt-3">
            <a class="fontsize_responsive" href="./password_reset.php">パスワードを忘れた方はこちらをクリック</a>
          </div>
      </div><!--end panel-body-->
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
