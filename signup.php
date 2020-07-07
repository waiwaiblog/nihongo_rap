<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　新規登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();



$page_flg = 0;






//POST送信されているとき
if(!empty($_POST) && $page_flg === 0) {

  debug('投稿ボタン押されました。ページ：' . print_r($page_flg,true));

  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  validRequired($username, 'username');
  validRequired($email, 'email');
  validRequired($password, 'password');

  if(empty($err_msg)) {

    debug('未入力バリッドパスしました');
    validMaxLen($username, 'username');
    
    validMaxLen($email, 'email');
    validMinLen($email, 'email');
    validEmail($email, 'email');
    validEmailDup($email);
    
    validMaxLen($password, 'password');
    validMinLen($password, 'password');
    validHalf($password, 'password');
    
    if(empty($err_msg)) {
      $page_flg = 1;
      debug('全バリデーションパスしました。');
      debug('ページ：' . print_r($page_flg,true));
    }
    
  }
}

if(!empty($_POST['back']) && $page_flg === 1) {
  $page_flg = 0;
}

if(!empty($_POST['button_submit']) && $page_flg === 1) {
  debug('ページ：' . print_r($page_flg,true));

  debug('登録するボタン押されました');
    try {
      $dbh = dbConnect();
      $sql = 'INSERT INTO users SET username=:username, email=:email, password=:password, create_date=:create_date, login_time=:login_time';
      $data = array(
        ':username' => $username,
        ':email' => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT),
        ':create_date' => date('Y-m-d H:i:s'),
        ':login_time' => date('Y-m-d H:i:s'),
      );
      $stmt = queryPost($dbh, $sql, $data);

      if($stmt) {
        $sesLimit = 60*60;
        $_SESSION['login_date'] = time();
        $_SESSION['login_limit'] = $sesLimit;
        $_SESSION['user_id'] = $dbh->lastInsertId();


        $_SESSION['msg_success'] = 'ユーザー登録しました。';

        debug('セッション変数の中身：'.print_r($_SESSION,true));

        header('Location: mypage.php');
        $page_flg = 0;
        return;
      }

    } catch(PDOException $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG06;
    }

}

?>

<?php
$siteTitle = '新規登録';
require('head.php');
?>



<body>


<?php

require('header.php')

?>
<?php
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null ?>
<?php if(basename($referer) === 'withdraw.php'): ?>
<?php $_SESSION['msg_success'] = '退会しました。ご利用ありがとうございました。'; ?>
<p id="js-show-msg" style="display:none;" class="msg-slide">
  <?php echo getSessionFlash('msg_success'); ?>
</p>
<?php endif; ?>

<p id="js-show-msg" style="display:none;" class="msg-slide">
  <?php echo getSessionFlash('msg_success'); ?>
</p>



<main>
  <div class="main_wrap">
    <h1><?php echo $siteTitle ?></h1>

    <div class="form">

      <?php if($page_flg === 0) : ?>
      <form method="post" action="">
      <label>ニックネーム 
        <span class="warning">
          <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
          <?php if(!empty($err_msg['username'])) echo $err_msg['username'] ?>
        </span>
        <div class="cp_iptxt">
          <input type="text" placeholder="ニックネーム" name="username" value="<?php if(!empty($_POST['username'])) echo $_POST['username'] ?>">
          <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
        </div>
      </label>

      <label>メールアドレス 
        <span class="warning">
          <?php if(!empty($err_msg['email'])) echo $err_msg['email'] ?>
        </span>
        <div class="cp_iptxt">
          <input type="text" placeholder="メールアドレス" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email'] ?>">
          <i class="fa fa-envelope fa-lg fa-fw" aria-hidden="true"></i>
        </div>
      </label>

      <label>パスワード 
        <span class="warning">
          <?php if(!empty($err_msg['password'])) echo $err_msg['password'] ?>
        </span>
        <div class="cp_iptxt pb-30">
          <input type="password" placeholder="パスワード" name="password" value="">
          <i class="fa fa-key fa-lg fa-fw" aria-hidden="true"></i>
        </div>
      </label>

      <div class="center">
      <button type="submit" name="button_confirm" value="確認する" class="button">確認する</button>
      </div>
      </form>

      <?php endif; ?>

      <?php if($page_flg === 1) : ?>

        <p style="text-align: center"><b>以下でよろしいですか？</b></p>
      <form method="post" action="">


      <label>ニックネーム 
        <div class="cp_iptxt">
          <p><?php echo $_POST['username'] ?></p>
          <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
          <input type="hidden" name="username" value="<?php echo $_POST['username'] ?>">
        </div>
      </label>

      <label>メールアドレス 
        <div class="cp_iptxt">
        <p><?php echo $_POST['email'] ?></p>
          <i class="fa fa-envelope fa-lg fa-fw" aria-hidden="true"></i>
          <input type="hidden" name="email" value="<?php echo $_POST['email'] ?>">
        </div>
      </label>

      <label>パスワード 
        <div class="cp_iptxt pb-30">
          <p>表示されません。</p>
          <i class="fa fa-key fa-lg fa-fw" aria-hidden="true"></i>
          <input type="hidden" name="password" value="<?php echo $_POST['password'] ?>">
        </div>
      </label>



      <div class="center">
      <button type="submit" name="back" value="戻る" class="button">書き直す</button> 
      <button type="submit" name="button_submit" value="登録する" class="button">登録する</button>
      </div>
      </form>

      <?php endif; ?>
    </div>
  </div>
</main>



<?php
require('footer.php');
?>