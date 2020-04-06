<?php

require_once('config.php');
//データベースに接続する
function connectDb() {
    $db = DB;
    $host = HOST;
    $user = USER;
    $pass = PASS;

    try{
      $param = "mysql:dbname=".$db.";host=".$host;
      $pdo = new PDO($param, $user, $pass);
      $pdo->query('SET NAMES utf8;');
      return $pdo;
    } catch (PDOException $e) {
      echo $e->getMessage();
      exit;
    }

}

//入力文字数のチェック
function strCountCheck($str, $count) {
    if (strlen(mb_convert_encoding($str, 'SJIS', 'UTF-8')) > $count) {
        $error = sprintf("全角%d文字以内で入力してください。", $count/2);
        return $error;
    }
    return NULL;
}

//メールアドレス存在のチェック
function checkEmail($user_email, $pdo) {
    $stmt = $pdo->prepare("SELECT user_email FROM user WHERE user_email = :user_email limit 1");
    $stmt->bindValue(':user_email', $user_email);
    $stmt->execute();
    $user = $stmt->fetch();

    return $user ? true : false;
}
//ユーザーネーム存在のチェック
function checkUserName($user_name, $pdo) {
    $stmt = $pdo->prepare("SELECT user_name FROM user WHERE user_name =  BINARY :user_name limit 1"); //BINARYを加えると大文字、小文字を区別する
    $stmt->bindValue(':user_name', $user_name);
    $stmt->execute();
    $user = $stmt->fetch();

    return $user ? true : false;
}

//他のユーザーがメールアドレスを使用しているかのチェック
function checkEmailwithoutMyself($user_email, $pdo, $id) {
    $stmt = $pdo->prepare("SELECT user_email FROM user WHERE user_email = :user_email and id != :id limit 1");
    $stmt->bindValue(':id', $id);
    $stmt->bindValue(':user_email', $user_email);
    $stmt->execute();
    $user = $stmt->fetch();

    return $user ? true : false;
}

//入力文字数のチェック
function halfstrCountCheck($str, $count) {
        $error = NULL;
        if (strlen(mb_convert_encoding($str, 'SJIS', 'UTF-8')) > $count) {
            $error = sprintf("半角%d文字以内で入力してください。", $count);
        }

        return $error;
    }

//メールアドレスとパスワードからuserを検索する
function getUser($user_email, $user_password, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE user_email = :user_email");
    $stmt ->bindValue(':user_email', $user_email);
    $stmt ->execute();
    $all = $stmt->fetchAll();

    foreach($all as $user) {
      if(password_verify($user_password, $user['user_password'])) {
        return $user;
        break;
      }
    }
    return false;
}

//ユーザーネームとパスワードからuserを検索する
function getUserByUserName($user_name, $user_password, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE user_name = :user_name");
    $stmt ->bindValue(':user_name', $user_name);
    $stmt ->execute();
    $all = $stmt->fetchAll();

    foreach($all as $user) {
      if(password_verify($user_password, $user['user_password'])) {
        return $user;
        break;
      }
    }
    return false;
}

// トークンを発行する処理
function setToken() {
    $token = sha1(uniqid(mt_rand(), true));
    $_SESSION['sstoken'] = $token;
}

// トークンをチェックする処理
function checkToken() {
    if (empty($_SESSION['sstoken']) || ($_SESSION['sstoken']!=$_POST['token']) ) {
        echo '<html><head><meta charset="utf-8"></head><body>不正なアクセスです。</body></html>';
        exit;
    }
}

//仮パスワード発行
function random($length){
    return substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, $length);
}


// 配列からプルダウンメニューを生成する
function arrayToSelect($inputName, $srcArray, $selectedIndex ='') {
    $temphtml = '<select class="form-control" name="'. $inputName. '" style="width:500px;">'. "\n";
    foreach ($srcArray as $key => $val) {
        if ($selectedIndex == $key) {
            $selectedText = ' selected="selected"';
        } else {
            $selectedText = '';
        }
        $temphtml .= '<option value="'. $key. '"'. $selectedText. '>'. $val. '</option>'. "\n";
    }
    $temphtml .= '</select>'. "\n";

    return $temphtml;
}


function getProcessedTime($time1, $time2) {
    $interval = (strtotime($time1) - strtotime($time2))/3600;
    return $interval;
}

function saveLog($user_id, $message, $keyword, $title, $pdo) {
    $sql = "INSERT INTO `cm_cron_log`(`user_id`, `message`, `keyword`,`title`,`created_at`, `updated_at`) VALUES (:user_id, :message, :keyword, :title, now(), now())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_id" => $user_id, ":message" => $message, ":keyword" => $keyword, ":title" => $title));
}

function getUserbyID($user_id, $pdo) {
    $sql = "SELECT * FROM `user` WHERE id = id limit 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch();

    return $user ? $user : false;

}

//XSS対策
  function h($original_str) {
	    return htmlspecialchars($original_str, ENT_QUOTES, "UTF-8");

    }

?>