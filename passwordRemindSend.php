<?php 

require('function.php');
require('MailTest.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行メール送信　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();







//POST送信されているとき
if(!empty($_POST)) {
  $email = $_POST['email'];

  validRequired($email, 'email');

  if(empty($err_msg)) {

    validEmail($email, 'email');
    validMaxLen($email, 'email');

    if(empty($err_msg)) {

      try {

        $dbh = dbConnect();
        $sql = 'SELECT count(*) AS cnt FROM users WHERE email=:email AND delete_flg = 0';
        $data = array(
          'email' => $email,
        );
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt && $result['cnt'] > 0) {
          $_SESSION['msg_success'] = 'メールアドレスに認証キーを送信しました。';

          $auth_key = makeRandKey();

          $to = $email;
          $subject = '【パスワード再発行認証】｜日本語ラップレビュー会';
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8888/nihongorap/passRemindReceive.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/nihongorap/passRemindSend.php

////////////////////////////////////////
日本語ラップレビューの会
※尚、本メールは送信用ですのでご返信はできません。
////////////////////////////////////////
EOT;
          $send = new MailTest();
          $send->sendMail($subject, $comment, $to);
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_key_limit'] = time() + (60 * 30);

          debug('セッションの中身' . print_r($_SESSION,true));
  
          session_write_close();
          header('Location: passwordRemindReceive.php');
          return;

        } else {
          debug('メールアドレスが違うか、未登録です。');
          $err_msg['common'] = 'メールアドレスが違うか、未登録です';
        }

      } catch(PDOException $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
} 


?>

<?php
$siteTitle = 'パスワード再発行メール送信';
require('head.php');
?>



<body>


<?php

require('header.php')

?>




<main>
  <div class="main_wrap">
    <h1><?php echo $siteTitle ?></h1>

    <div class="form">

      <form method="post" action="">

      <label>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。<br>
        <span class="warning">
          <?php if(!empty($err_msg['email'])) echo $err_msg['email'] ?>
          <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
        </span>
        <div class="cp_iptxt">
          <input type="text" placeholder="メールアドレス" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email'] ?>">
          <i class="fa fa-envelope fa-lg fa-fw" aria-hidden="true"></i>
        </div>
      </label>

      <div class="center">
      <button type="submit" name="button_submit" value="送信する" class="button">送信する</button>
      </div>
      </form>

    </div>
  </div>
</main>



<?php
require('footer.php');
?>