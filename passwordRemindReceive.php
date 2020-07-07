<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行認証ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();



debug('セッションの中身' . print_r($_SESSION,true));

if(empty($_SESSION['auth_key'])){
  debug('sessionにauthkeyがないよ');
  header("Location: passwordRemindSend.php"); //認証キー送信ページへ
}

//POST送信されているとき
if(!empty($_POST)) {
  debug('ボタン押されてます');
  $password = $_POST['password'];

  validRequired($password, 'password');
  
  if(empty($err_msg)) {
    debug('未入力OK');
    validLength($password, 'password');
    validHalf($password, 'password');

    if(empty($err_msg)) {
      debug('入力文字チェックOK');

      if($password !== $_SESSION['auth_key']) {
        $err_msg['password'] = MSG08;
        debug('入力が違う');
      }

      if(time() > $_SESSION['auth_key_limit']) {
        debug('期限切れ');
        $err_msg['password'] = MSG10;
      }

      if(empty($err_msg)) {
        debug('バリッドオールおK');

        $password_new = makeRandKey();

        debug('新しいパスは' . print_r($password_new,true));
        try {
          $dbh = dbConnect();
          $sql = 'UPDATE users SET password=:password WHERE email=:email AND delete_flg = 0';
          $data = array(
            ':password' => password_hash($password_new, PASSWORD_DEFAULT),
            ':email' => $_SESSION['auth_email'],
          );
          $stmt = queryPost($dbh, $sql, $data);

          if($stmt) {

            debug('クエリに成功。');
            $from = 'info@waiwaiblog.net';
            $to = $_SESSION['auth_email'];
            $subject = '【パスワード再発行完了】｜日本語ラップレビュー会';
            $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：http://localhost:8888/nihongorap/login.php
再発行パスワード：{$password_new}
※ログイン後、パスワードのご変更をお願い致します

////////////////////////////////////////
日本語ラップレビューの会
※尚、本メールは送信用ですのでご返信はできません。
////////////////////////////////////////
EOT;

            sendMail($from, $to, $subject, $comment);
            debug('セッション変数の中身：'.print_r($_SESSION,true));
            
            debug('セッション情報を消します。');
            session_unset();
            $_SESSION['msg_success'] = 'メールアドレスに新パスワードを送信しました。';
            debug('ログインページへ移動します。');
            header('Location: login.php');
            return;
          } else {
            debug('クエリに失敗しました。');
            $err_msg['common'] = MSG06;
          }
  
        } catch(PDOException $e) {
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG06;
        }

      }

    }
  }
} 


?>

<?php
$siteTitle = 'パスワード再発行認証ページ';
require('head.php');
?>



<body>


<?php

require('header.php')

?>
<p id="js-show-msg" style="display:none;" class="msg-slide">
  <?php echo getSessionFlash('msg_success'); ?>
</p>



<main>
  <div class="main_wrap">
    <h1><?php echo $siteTitle ?></h1>

    <div class="form">

      <form method="post" action="">

      <label>ご指定のメールアドレスにお送りした【パスワード再発行認証】メール内にある「認証キー」をご入力ください。<br>
        <span class="warning">
          <?php if(!empty($err_msg['password'])) echo $err_msg['password'] ?>
          <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
        </span>
        <div class="cp_iptxt">
          <input type="password" placeholder="認証キー" name="password" value="<?php if(!empty($_POST['password'])) echo $_POST['password'] ?>">
          <i class="fa fa-key fa-lg fa-fw" aria-hidden="true"></i>
        </div>
      </label>

      <div class="center pb-30">
      <button type="submit" name="button_submit" value="送信する" class="button">送信する</button>
      </div>
      </form>

      <div class="reminder">
        <p>メールアドレスをもう一度送信する方は<a href="passwordRemindSend.php">こちら</a></p>
      </div>
    </div>
  </div>
</main>



<?php
require('footer.php');
?>