<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();



require('auth.php');




//POST送信されているとき
if(!empty($_POST)) {
  $email = $_POST['email'];
  $password = $_POST['password'];
  $pass_save = !empty($_POST['pass_save']) ? true : false;

  validRequired($email, 'email');
  validRequired($password, 'password');

  if(empty($err_msg)) {

    validEmail($email, 'email');
    validMaxLen($email, 'email');

    validHalf($password, 'password');
    validMinLen($password, 'password');
    validMaxLen($password, 'password');

    if(empty($err_msg)) {

      try {

        $dbh = dbConnect();
        $sql = 'SELECT password,id FROM users WHERE email=:email AND delete_flg = 0';
        $data = array(
          'email' => $email,
        );
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);


        //管理者画面への遷移ページ
        if(!empty($result) && $result['id'] === '1' && password_verify($password, $result['password'])) {
          $_SESSION['user_id'] = $result['id'];
          $_SESSION['msg_success'] = '管理者ログインしました。';
          header('Location: admin.php');
          return;
        }

        if(!empty($result) && password_verify($password, $result['password'])) {
          $sesLimit = 60*60;
          $_SESSION['login_date'] = time();
          $_SESSION['user_id'] = $result['id'];
          
          if($pass_save) {
            $_SESSION['login_limit'] = $sesLimit * 24 * 30;
          } else {
            $_SESSION['login_limit'] = $sesLimit;
          }
          
          debug('セッション変数の中身：'.print_r($_SESSION,true));
  
          $_SESSION['msg_success'] = 'ログインしました。';
          header('Location: mypage.php');
          return;
        } else {
          debug('パスワードがアンマッチです。');
          $err_msg['common'] = MSG08;
        }



      } catch(PDOException $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG06;
      }
    }
  }
} 


?>

<?php
$siteTitle = 'ログイン';
require('head.php');
?>



<body>


<?php

require('header.php')

?>
<?php
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null ?>
<?php if(empty($_SESSION) && (preg_match("/^mypage.php/", basename($referer)) || basename($referer) === 'applicate_album.php' || basename($referer) === 'prof_edit.php' || basename($referer) === 'pass_edit.php' || basename($referer) === 'contact.php' || basename($referer) === 'withdraw.php' || preg_match("/^index.php/", basename($referer)) || preg_match("/^detail.php/", basename($referer)))): ?>
<?php $_SESSION['msg_success'] = 'ログアウトしました。'; ?>
<p id="js-show-msg" style="display:none;" class="msg-slide">
  <?php echo getSessionFlash('msg_success'); ?>
</p>
<?php endif; ?>

<?php
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null ?>
<?php if(basename($referer) === 'admin.php'): ?>
<?php $_SESSION['msg_success'] = '管理者ログアウトしました。'; ?>
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

      <form method="post" action="">

      <label>メールアドレス 
        <span class="warning">
          <?php if(!empty($err_msg['email'])) echo $err_msg['email'] ?>
          <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
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
        <div class="cp_iptxt">
          <input type="password" placeholder="パスワード" name="password" value="">
          <i class="fa fa-key fa-lg fa-fw" aria-hidden="true"></i>
        </div>
      </label>

      <div class="cp_ipcheck pb-30">
        <label>
          <input type="checkbox" class="option-input05" name="pass_save">
          <span>次回から自動ログインする</span>
        </label>
      </div>

      <div class="center pb-30">
      <button type="submit" name="button_submit" value="ログインする" class="button">ログインする</button>
      </div>
      </form>

      <div class="reminder">
        <p>パスワードを忘れた方は<a href="passwordRemindSend.php">こちら</a></p>
      </div>
    </div>
  </div>
</main>



<?php
require('footer.php');
?>