<?php

//タイムゾーンを東京に設定
date_default_timezone_set('Asia/Tokyo');
//エラーログをとるかどうか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = true;
//デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}

//================================
// セッション準備・セッション有効期限を延ばす
//================================
//セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime ', 60*60*24*30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart(){
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：'.session_id());
  debug('セッション変数の中身：'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ：'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug( 'ログイン期限日時タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit'] ) );
  }
}



const MSG01 = '入力必須です。';
const MSG02 = 'Emailの形式で入力してください。';
const MSG03 = '半角英数字のみご利用いただけます。';
const MSG04 = '6文字以上で入力してください。';
const MSG05 = '255文字以内で入力してください。';
const MSG06 = 'エラーが発生しました。';
const MSG07 = 'そのEmailは既に登録されています。';
const MSG08 = '入力に誤りがあります。';
const MSG09 = '文字で入力してください。';
const MSG10 = '認証キーの期限切れです。';
const MSG11 = '再入力が違っています。';
const MSG12 = '古いパスワードが認証できません。';
const MSG13 = '登録してあるパスワードと同じです。';


$err_msg = array();

//そのページの時、activeをつける
function putoutLink($link, $name) {
  
  if(basename($_SERVER['SCRIPT_NAME']) !== $link) {
    echo '<a href="' . $link . '">' . $name . '</a>';
  } else {   
    echo '<a href="' . $link . '" class="active">' . $name . '</a>';
  }
}

function getErrMsg($key) {
  global $err_msg;
  if(!empty($err_msg[$key])) {
    echo $err_msg[$key];
  }
}

function validMatch($str1, $str2, $key) {
  if($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = MSG11;
  }
}

function validRequired($str, $key) {
  if($str === '') {
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}

function validMaxLen($str, $key, $max = 255) {
  if(mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
  global $err_msg;
  
}
function validMinLen($str, $key, $min = 6) {
  if(mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
  global $err_msg;
  
}

function validEmail($str, $key) {
  $preg = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
  if(!preg_match($preg, $str)) {
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}

function validHalf($str, $key){
  $preg ="/^[a-zA-Z0-9]+$/";
  if(!preg_match($preg, $str)){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

function validLength($str, $key, $length = 8){
  if(mb_strlen($str) !== $length){
    global $err_msg;
    $err_msg[$key] = $length . MSG09;
  }
}

function validEmailDup($email) {
  global $err_msg;
  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(*) AS cnt FROM users WHERE email=:email';
    $data = array(
      ':email' => $email,
    );
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
  
    if($result['cnt'] > 0) {
      $err_msg['email'] = MSG07;
    }

  } catch(PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG06;
  }
}


function dbConnect() {
  $dsn = 'mysql:host=localhost;dbname=nihongorap;charset=utf8';
  $user = 'root';
  $pass = 'root';
  $options = array(
        // SQL実行失敗時にはエラーコードのみ設定
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        // デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
        // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  $dbh = new PDO($dsn, $user, $pass, $options);
  return $dbh;
}

function queryPost($dbh, $sql, $data) {
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if(!$stmt->execute($data)){
    debug('クエリに失敗しました。');
    debug('失敗したSQL：'.print_r($stmt,true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('クエリ成功。'.print_r($stmt,true));
  return $stmt;

}

function sendMail($from, $to, $subject, $comment) {
  if(!empty($to) && !empty($subject) && !empty($comment)){
    //文字化けしないように設定（お決まりパターン）
    mb_language("Japanese"); //現在使っている言語を設定する
    mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設定
    
    //メールを送信（送信結果はtrueかfalseで返ってくる）
    $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
    //送信結果を判定
    if ($result) {
      debug('メールを送信しました。');
    } else {
      debug('【エラー発生】メールの送信に失敗しました。');
    }
  }
}



function makeRandKey($length = 8) {
  static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for ($i = 0; $i < $length; ++$i) {
      $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}


function getSessionFlash($key) {
  if(!empty($_SESSION[$key])) {
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}

//名前を取得する
function getName($u_id) {

  if(!empty($u_id)) {
    try {
  
      $dbh = dbConnect();
      $sql = 'SELECT username FROM users WHERE id=:u_id';
      $data = array(
        ':u_id' => $u_id,
      );
      $stmt = queryPost($dbh, $sql, $data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
  
      if(!empty($result)) {
        echo $result['username'];
      } else {
        echo '名無し';
      }
  
  
    } catch(PDOException $e) {
      error_log('SQLエラー発生：' . $e->getMessage());
    }

  } else {
    echo '名無し';
  }
}



//postが~のときにselectedにする
function checkSelectBox($str) {
  if(!empty($_POST['year'])) {
    if($_POST['year'] === $str) {
      echo 'selected';
    } else {
      echo '';
    }
  }
}


//checkSelectBox改良版
function checkSelectBox2($var, $key, $val) {
  if(!empty($var[$key])) {
    if($var[$key] === $val) {
      echo 'selected';
    } else {
      echo '';
    }
  }
}


function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報：'.print_r($file,true));
  
  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
          case UPLOAD_ERR_OK: // OK
              break;
          case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
              throw new RuntimeException('ファイルが選択されていません');
          case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
          case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
              throw new RuntimeException('ファイルサイズが大きすぎます');
          default: // その他の場合
              throw new RuntimeException('その他のエラーが発生しました');
      }
      
      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
          throw new RuntimeException('画像形式が未対応です');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
          throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);
      
      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;

    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();

    }
  }
}


function getUser($u_id){
  debug('ユーザー情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM users  WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ成功の場合
//    if($stmt){
//      debug('クエリ成功。');
//    }else{
//      debug('クエリに失敗しました。');
//    }
    // クエリ結果のデータを１レコード返却
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
    
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function h($str) {
  return htmlspecialchars($str,ENT_QUOTES);
}


function getFormData($key){
  global $dbUserData;
  global $err_msg;
  // ユーザーデータがある場合
  if(!empty($dbUserData)){
    //フォームのエラーがある場合
    if(!empty($err_msg[$key])){
      //POSTにデータがある場合
      if(isset($_POST[$key])){
        return h($_POST[$key]);
      }else{
        //ない場合（基本ありえない）はDBの情報を表示
        return h($dbUserData[$key]);
      }
    }else{
      //POSTにデータがあり、DBの情報と違う場合
      if(isset($_POST[$key]) && $_POST[$key] !== $dbUserData[$key]){
        return h($_POST[$key]);
      }else{
        return h($dbUserData[$key]);
      }
    }
  }else{
    if(isset($_POST[$key])){
      return h($_POST[$key]);
    }
  }
}



function isLogin() {
  if( !empty($_SESSION['login_date']) ){
    debug('ログイン済みユーザーです。');
  
    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      debug('ログイン有効期限オーバーです。');
  
      // セッションを削除（ログアウトする）
      session_destroy();
      return false;
    }else{
      debug('ログイン有効期限以内です。');
      //最終ログイン日時を現在日時に更新
      $_SESSION['login_date'] = time();
      
      return true;
      
    }
  
  }else{
    debug('未ログインユーザーです。');
    return false;
    }
}


function isLike($u_id, $d_id) {
  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(*) AS cnt FROM favorites WHERE details_id=:d_id AND users_id=:u_id';
    $data = array(
      ':d_id' => $d_id,
      ':u_id' => $u_id,
    );
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
  
    if($result['cnt'] > 0) {
      debug('お気に入りデータあり');
      return true;
    } else {
      debug('お気に入りデータなし');
      return false;
    }
  } catch(PDOException $e) {
    error_log('SQLエラー：' . $e->getMessage());
    exit();
  }
}


function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(!in_array($key,$arr_del_key,true)){ //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");

    return $str;
  }
}
//ex. $_GET['d'] = 2　と　$_GET['p'] = 1　が入っているとする。
//    $_GET['p']を消したい場合に、それをappendGetParamの引数（pと書く）に指定する。
//    $_GET自体がないとできないので、条件文に$_GETがある場合として、
//    foreachで$_GETの中身を１つずつ取り出す。
//    その中でin_array(第１引数が第２引数（配列）内にあるかどうかをboolする)で条件を指定
//    !in_arrayなので合致しない場合に下の処理が続く。
//    $strに合致しない場合のみ、そのキーと中身の間に=と後ろに&を付与する。
//    つまり、消したくない場合はそれが残る。
//    消したくないものが何個かあれば、その都度$strに付与され、最後にmb_substrで&を取ってそれをreturnする