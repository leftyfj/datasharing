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
//入力チェック用配列
$error_message = array();

//完了メッセージ用変数
$complete_message = 0;

//ファイルの1行目はヘッダーかデータかのチェック
if($_POST['header'] == 1) {
  //1行目はヘッダー
  $header = 1;
} else {
  $header = 0;
}

//一括登録したデータ数のカウンター変数
$data_counter = 0;

if ($_POST['check_file'] == 1) {
  if(!$_FILES['upload_file']['tmp_name']) {
  $error_message['upload_file'] = "アップロードするCSVファイルを選択してください。";
} else {
    $file_extension = substr($_FILES['upload_file']['name'],-3);
    if($file_extension !== 'csv') {
        $error_message['upload_file'] = "CSV形式でアップロードしてください。";
    }
}
}

  //配列$error_messageの各要素がNULLかチェック
foreach($error_message as $err) {
      if (!empty($err)) {
      break;
      }
}

if(empty($err)) {
  // アップロードされたファイルを文字列として読み。
  $data = file_get_contents($_FILES['upload_file']['tmp_name']);

  // 日本語の文字コードをUTF-8に変換。
  mb_language("Japanese");
  $data = mb_convert_encoding($data, 'UTF-8', 'auto');

  // 文字コード変換したデータを再度CSVファイルとして書き出す。
  $temp = tmpfile();
  fwrite($temp, $data);
  rewind($temp);

  // CSVファイルをfgetcsvを使って配列に書き出します。
  $data_array = array();
  while (($data = fgetcsv($temp, 0, ",")) !== FALSE) {
    $data_array[] = $data;
  }

  $rows_count = count($data_array);
 
  // CSVファイルをクローズします。
  fclose($temp);

  //データベースに接続する
  $pdo = connectDb();
  //ループ開始
  for($j=$header;$j<$rows_count;$j++) {
    $row = $data_array[$j];
    // カラムの長さを確認
    //var_dump(count($row));
    if(count($row)==8) {
      for($i=0; $i<count($row); $i++) {
        //文字数を調べる
        if(strlen(mb_check_encoding($row[$i], 'SJIS','UTF-8')) > 200) {
          $row[$i] = substr($row[$i], 0 , 200) ;//200文字を超えていた場合、最初の200字を取得する
        }
      }
    }
    // SQL文
    // データベースにセット
    $sql = "INSERT INTO data (rank, title_ja, title_en, year, director, producer, starring, prize, created_at, created_by, updated_at) VALUES (:rank, :title_ja, :title_en, :year, :director, :producer, :starring, :prize, now(), :created_by, now())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':rank', $row[0]);
    $stmt->bindValue(':title_ja', $row[1]);
    $stmt->bindValue(':title_en', $row[2]);
    $stmt->bindValue(':year', $row[3]);
    $stmt->bindValue(':director', $row[4]);
    $stmt->bindValue(':producer', $row[5]);
    $stmt->bindValue(':starring', $row[6]);
    $stmt->bindValue(':prize', $row[7]);
    $stmt->bindValue(':created_by', $user['id']);
    $stmt->execute();
    $data_counter ++;
    // ループ終了
  }

  //操作ログを登録する
    $sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
    $stmt_log = $pdo->prepare($sql_log);
    $stmt_log->bindValue(':user_id',$user['id']);
    $stmt_log->bindValue(':action', $action_array['upload_collectively']);
    $stmt_log->execute();
  //データベースから切断する
  unset($pdo);
  //完了メッセージを設定
   $complete_message = $data_counter;
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
      <h2><caption><i class="fas fa-edit" style="color:orange;"></i>&nbsp;データ一括登録</caption></h2>
      <p><strong>データをCSV形式で保存してアップロードしてください。</strong></p>
            <?php if($error_message['upload_file'] !=''):?>
              <span class="help-block text-danger"><strong><?php echo $error_message['upload_file']; ?></strong></span>
            <?php else: ?>
              <span class="help-block text-danger">&nbsp;</span>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
              <div class="form-group mt-2 mb-2">
                <input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
                <!-- <label class="mb-3">CSVファイルを指定して下さい。</label><br> -->
                <input type="file" name="upload_file" />
              </div><!--form-group-->
              <div class="form-group mb-4">
                <input type="checkbox" name="header" value="1">1行目をヘッダ行として処理する
              </div><!--form-group-->
              <div class="form-group mb-4">
              <input type="hidden" name="check_file" value="1" />
                <input class="btn btn-primary" type="submit" value="登録">
              </div><!--form-group-->
            </form>
            
            <?php if($complete_message !==0):?>
                <p><strong style="color:blue;"><?php echo $complete_message."件のデータを登録しました。";?></strong></p>
            <?php endif;?>
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
