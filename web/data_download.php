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


// 1.dataテーブルから全データを取得する
//データベースに接続
$pdo = connectDb();

//データを格納する配列を用意
$data = array();

// 2.取得したデータをループして、CSV形式のデータを作成（fputcsv）
$sql = "SELECT * FROM `data`";
$stmt = $pdo->query($sql);

foreach ($stmt->fetchAll() as $row) {
  array_push($data, $row);
}

// CSVデータ書き出し用の一時ファイルを準備
$temp = tmpfile();

// 3.CSVデータを出力（ダウンロード）
// 取得したデータをループ
foreach ($data as $key => $datum) {
  // 出力するデータの配列を作成
  // $datum['ref'] = mb_convert_encoding($datum['ref'], "SJIS", "UTF-8");
  // $datum['title'] = mb_convert_encoding($datum['title'], "SJIS", "UTF-8");
  // $datum['year'] = mb_convert_encoding($datum['year'], "SJIS", "UTF-8");
  // $datum['genre'] = mb_convert_encoding($datum['genre'], "SJIS", "UTF-8");
  // $datum['duration'] = mb_convert_encoding($datum['duration'], "SJIS", "UTF-8");
  // $datum['director'] = mb_convert_encoding($datum['director'], "SJIS", "UTF-8");
  // $datum['writer'] = mb_convert_encoding($datum['writer'], "SJIS", "UTF-8");
  // $datum['production'] = mb_convert_encoding($datum['production'], "SJIS", "UTF-8");
  // $datum['actors'] = mb_convert_encoding($datum['actors'], "SJIS", "UTF-8");
  // $datum['description'] = mb_convert_encoding($datum['description'], "SJIS", "UTF-8");
  
  $array = array(
    $datum['ref'], 
    $datum['title'],
    $datum['year'],
    $datum['genre'],
    $datum['duration'],
    $datum['director'],
    $datum['writer'],
    $datum['production'],
    $datum['actors'],
    $datum['description']
  );
    // 作成した配列をCSV形式で一時ファイルに出力
    fputcsv($temp, $array);
}

//操作ログを登録する
$sql_log = "INSERT INTO history (user_id, action, created_at, updated_at) VALUES(:user_id, :action, now(), now())";
$stmt_log = $pdo->prepare($sql_log);
$stmt_log->bindValue(':user_id',$user['id']);
$stmt_log->bindValue(':action', $action_array['download_data']);
$stmt_log->execute();
// データベース接続を切断する
unset($pdo);

// レスポンスヘッダー（MIMEタイプ）の設定
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=data.csv");
// 一時ファイルの情報を取得
$meta = stream_get_meta_data($temp);

// 一時ファイルの内容を出力
readfile($meta['uri']);

// 一時ファイルクローズ
fclose($temp);

?>