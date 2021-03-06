<?php
//関数読み込み
require_once('config.php');
require_once('functions.php');
ini_set('display_errors',0);
error_reporting(0);
session_start();

//関数読み込み
require_once('config.php');
require_once('functions.php');
ini_set('display_errors',0);
error_reporting(0);
session_start();

$user = $_SESSION['USER'];
$recNo = getVersionNo();

if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}


//ページネーション
// 正規表現でパラメーターが数値かどうかのチェックを行う
if (preg_match('/^[1-9][0-9]*$/', $_GET['page'])) {
	// 正規表現にマッチしたらパラメーターをそのまま受け取る
	$page = $_GET['page'];
} else {
	// 数値以外のパラメーターが渡されたら強制的に1にする
	$page = 1;
}

$page_count = getPageCount($user['id']);
//ページ開始は何件目のデータか算出
//開始番号=１ページの件数 * (ページ番号-1)
$offset = $page_count * ($page -1);
$page_left = ceil($page / PAGE_NO_WIDTH) * PAGE_NO_WIDTH -(PAGE_NO_WIDTH -1);
$page_right = $page_left + (PAGE_NO_WIDTH -1);


//getされたキーワードを受け取る
$search_query = $_GET['q'];
//$sortBy = 'name';
//$orderBy = ASC;

if($_GET['o'] !='') {
  $orderBy = $_GET['o']; //ソートのリクエストがあったときのget
} else {
  $orderBy = ASC;
}

if($_GET['s'] !='') {
  $sortBy = $_GET['s']; //ソートのリクエストがあったときのget
} else {
  //$sortBy = 'id';
  $sortBy = 'ref';
}

  //データベースに接続
  $pdo = connectDb();

  //キーワードを部分一致に変換、キーワードがなければ全件抽出
  //キーワードを含むデータを検索するsql文
  //ホワイトリスト照合
  // ホワイトリストの準備（カラム）

   $sort_whitelist = [
     'ref' => ['ref','Ref.No.'],
     'title' =>['title', 'タイトル'],
     'year' => ['year','公開年'],
     'genre' => ['genre','ジャンル'],
     'duration' => ['duration','公開期間'],
     'director' => ['director','監督'],
     'writer' => ['writer','脚本'],
     'production' => ['production','制作'],
     'actors' => ['actors','出演'],
     'description' => ['description','内容']
   ];

  //$sort_safe = isset($sort_whitelist[$sortBy]) ? $sort_whitelist[$sortBy] : $sort_whitelist['id'];
  $sort_safe = isset($sort_whitelist[$sortBy][0]) ? $sort_whitelist[$sortBy][0] : $sort_whitelist['ref'][0];

  $order_whitelist = array('asc' => 'asc', 'desc' => 'desc');

  $order_safe = isset($order_whitelist[$orderBy]) ? $order_whitelist[$orderBy] : $order_whitelist['asc'];

  $sql = "SELECT * FROM `data` WHERE `ref` LIKE :ref
  OR `title` LIKE :title
  OR `year` LIKE :year
  OR `genre` LIKE :genre 
  OR `duration` LIKE :duration
  OR `director` LIKE :director
  OR `writer` LIKE :writer
  OR `production` LIKE :production 
  OR `actors` LIKE :actors
  OR `description` LIKE :description
  ORDER BY  $sort_safe $order_safe LIMIT  :offset, :count";

  //該当データを取得する
  $stmt = $pdo->prepare($sql);
  $stmt ->bindValue(':ref', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt ->bindValue(':title', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt ->bindValue(':year', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt ->bindValue(':genre', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt ->bindValue(':duration', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt ->bindValue(':director', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt ->bindValue(':writer', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt ->bindValue(':production', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt ->bindValue(':actors', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt ->bindValue(':description', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmt ->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt ->bindValue(':count', $page_count, PDO::PARAM_INT);
  $stmt-> execute();
  //取得したデータを配列に収める
  $data = $stmt->fetchALL(PDO::FETCH_ASSOC);

  //抽出されたデータは何件あるか調べる
  $sqlForCounts = "SELECT count(*) FROM `data` 
  WHERE `title` LIKE :title
  OR `year` LIKE :year 
  OR `genre` LIKE :genre 
  OR `duration` LIKE :duration 
  OR `director` LIKE :director 
  OR `writer` LIKE :writer 
  OR `production` LIKE :production 
  OR `actors` LIKE :actors 
  OR `description` LIKE :description"; 

  $stmtForCounts = $pdo->prepare($sqlForCounts);
  $stmtForCounts ->bindValue(':title', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmtForCounts ->bindValue(':year', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmtForCounts ->bindValue(':genre', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmtForCounts ->bindValue(':duration', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmtForCounts ->bindValue(':director', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmtForCounts ->bindValue(':writer', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmtForCounts ->bindValue(':production', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmtForCounts ->bindValue(':actors', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmtForCounts ->bindValue(':description', '%'.$search_query.'%', PDO::PARAM_STR);
  $stmtForCounts-> execute();
  $total = $stmtForCounts->fetchColumn();
  //該当データのページ数
  $total_page = ceil($total / $page_count);

  //データベース接続を切断する
  unset($pdo)

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
    <div class="containera bg-light p-4">
      <div id="jq"></div>
      <h2><caption><i class="fas fa-sign-in-alt" style="color:orange;"></i>&nbsp;データ一覧</caption><span id="data-count">(総データ数 <?php echo $total;?>件)</span></h2>
          <div class="row justify-content-sm-between mb-3">
            <div class="col-sm-6">
              <form class="form-inline" action="" method="get">
                <input class="form-control" type="text" name="q" size="30" value="<?php echo h($search_query); ?>" placeholder="キーワード入力してください">
                <input class="btn btn-info ml-2" type="submit" value="検索">
              </form>
            </div> <!--end col-sm-8 -->
            <div class="col-sm-6 text-right">
              <button class="btn btn-outline-primary"><a href="javascript:void(0);" onclick="var ok=confirm('全データをダウンロードします。宜しいですか?');
              if (ok) location.href='data_download.php'; return false;">データダウンロード</a></button>
            </div> <!--end col-sm-4 -->
          </div> <!--end row -->

          <?php 
            if($orderBy =="desc") {
              $arrow_icon = "fas fa-arrow-down";
              $order = "asc";
            } else {
              $arrow_icon = "fas fa-arrow-up";
              $order = "desc";
            }
          ?>
          <table class="table table-sm table-hover">
          <form action="data_edit.php" method="post">
            <thead class="thead-light">
              <tr>
                <?php foreach($sort_whitelist as $column): ?>
                  <?php if($column[0] == $sortBy):?>
                    <th>
                      <a style="font-size:14px;" href="?s=<?php echo h($column[0]);?>&o=<?php echo h($order);?>&q=<?php echo h($search_query);?>"><?php echo h($column[1]);?><span><i class="<?php echo h($arrow_icon);?>"></i></span></a>
                    </th>
                  <?php else:?>
                    <th>
                      <a style="font-size:14px;" href="?s=<?php echo h($column[0]);?>&o=asc&q=<?php echo h($search_query);?>"><?php echo h($column[1]);?><span><i class="<?php echo h($arrow_icon);?>"></i></span></a>
                    </th>
                  <?php endif;?>
                <?php endforeach;?> 
                    <th></th>
              </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $datum) :?>
                  <tr>
                    <!-- <td><?php //echo $datum['id']; ?></td> -->
                    <td class="contents_list" style="width:6%"><?php echo h($datum['ref']); ?></td>
                    <td class="contents_list" style="width:8%"><?php echo h($datum['title']); ?></td>
                    <td class="contents_list" style="width:5%"><?php echo h($datum['year']); ?></td>
                    <td class="contents_list" style="width:6%"><?php echo h($datum['genre']); ?></td>
                    <td class="contents_list" style="width:7%"><?php echo h($datum['duration']); ?></td>
                    <td class="contents_list" style="width:9%"><?php echo h($datum['director']); ?></td>
                    <td class="contents_list" style="width:9%"><?php echo h($datum['writer']); ?></td>
                    <td class="contents_list" style="width:10%"><?php echo h($datum['production']); ?></td>
                    <td class="contents_list" style="width:12%"><?php echo h($datum['actors']); ?></td>
                    <td class="contents_list" style="font-size:16px; width:18%"><?php echo h($datum['description']); ?></td>
                    <td class="contents_list" style="width:10%">
                    <a href="data_edit.php?id=<?php echo h($datum['id']); ?>">[編集]</a>
    	              <a href="javascript:void(0);" onclick="var ok=confirm('削除しても宜しいですか?');
                    if (ok) location.href='data_delete.php?id=<?php echo h($datum['id']); ?>'; return false;">[削除]</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
             </tbody>
          </form>

          </table>
          <div class="row">
            <div class="col-sm-6 ">
            </div>
            <div class="col-sm-6 d-flex align-items-center justify-content-end pr-3">
              <?php if($search_query !="") :?>
                <form action="" method="get">
                  <input type="hidden" name="q" value="">
                  <input type="submit" class="btn btn-secondary" value="選択解除">
                </form>
              <?php endif; ?>
            </div>
          </div>
    </div> <!--end row--> 
      
    </div> <!--end container-->
  </main>
  <?php if($total_page >=2): ?>
    <nav class="my-5">
        <ul class="pagination justify-content-center">
         <li class="page-item"><a href="?page=1;?>&s=<?php echo h($sortBy);?>&o=<?php echo h($orderBy);?>&q=<?php echo h($search_query);?>" class="page-link">最初のページへ</a></li>

        <?php if($page >1):?>
          <li class="page-item"><a href="?page=<?php echo h($page)-1;?>&s=<?php echo h($sortBy);?>&o=<?php echo h($orderBy);?>&q=<?php echo h($search_query);?>" class="page-link">&laquo</a></li>
        <?php else: ?>
          <li class="page-item disabled"><a href="#" class="page-link">&laquo</a></li>
        <?php endif;?>

          <?php for($i=$page_left ; $i<= $page_right; $i++): ?>
            <?php if($i==$page):?>
              <li class="page-item active"><a href="#" class="page-link"><?php echo h($i);?></a></li>
            <?php elseif($i <= $total_page): ?> <!-- 最終ページに到達したらそれ以降はページ数を表示しない-->
              <li class="page-item"><a href="?page=<?php echo h($i);?>&s=<?php echo h($sortBy);?>&o=<?php echo h($orderBy);?>&q=<?php echo h($search_query);?>" class="page-link"><?php echo h($i);?></a></li>
            <?php endif;?>
          <?php endfor;?>

        <?php if($page < $total_page):?>
          <li class="page-item"><a href="?page=<?php echo h($page)+1;?>&s=<?php echo h($sortBy);?>&o=<?php echo h($orderBy);?>&q=<?php echo h($search_query);?>" class="page-link">&raquo</a></li>
        <?php else: ?> 
          <li class="page-item disabled"><a href="#" class="page-link">&raquo</a></li>

        <?php endif;?>
        <li class="page-item"><a href="?page=<?php echo h($total_page);?>&s=<?php echo h($sortBy);?>&o=<?php echo h($orderBy);?>&q=<?php echo h($search_query);?>" class="page-link">最後のページへ</a></li>
        </ul>
    </nav>
  <?php endif; ?>
  <footer>
  </footer>


  <!-- ここから下記３行が抜けていた汗 -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

</body>
