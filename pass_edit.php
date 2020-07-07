<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード変更ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();



require('auth.php');

$dbUserData = getUser($_SESSION['user_id']);

if(!empty($_POST)) {

  $pass_old = h($_POST['pass_old']);
  $pass_new = h($_POST['pass_new']);
  $pass_new_re = h($_POST['pass_new_re']);

  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');

  if(empty($err_msg)) {

    //新パスのバリデーション
    validHalf($pass_new, 'pass_new');
    validMinLen($pass_new, 'pass_new');
    validMaxLen($pass_new, 'pass_new');
    validHalf($pass_new, 'pass_new');

    //登録してあるパスワードと古いパスワードが違う場合にエラー
    if(!password_verify($pass_old,$dbUserData['password'])) {
      $err_msg['pass_old'] = MSG12;
    }
    //入力値（新旧が同じ場合）にえらー
    if($pass_old === $pass_new){
      $err_msg['pass_new'] = MSG13;
    }
    //再入力があっていなければエラー
    validMatch($pass_new, $pass_new_re, 'pass_new');

    if(empty($err_msg)) {
      debug('パスワードバリデーションOKです。');

      try {

        $dbh = dbConnect();
        $sql = 'UPDATE users SET password=:password WHERE id=:id AND delete_flg = 0';
        $data = array(
          ':password' => password_hash($pass_new, PASSWORD_DEFAULT),
          ':id' => $dbUserData['id'],
        );
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt) {
          
          $username = $dbUserData['username'];
          $from = 'info@waiwaiblog.net';
          $to = $dbUserData['email'];
          $subject = 'パスワード変更通知｜日本語ラップレビューの会';
          //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
          //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
          $comment = <<<EOT
{$username}　さん
パスワードが変更されました。
                      
////////////////////////////////////////
日本語ラップレビューの会
////////////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);

          $_SESSION['msg_success'] = 'パスワードを更新しました。';

          header('Location: pass_edit.php');
          return;
        } else {
          $err_msg['common'] = MSG06;
        }

      } catch(PDOException $e) {
        error_log('SQLエラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG06;
      }
    }
  }
}





?>

<?php
$siteTitle = 'パスワード変更';
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
    
    <!-- 2カラム用div -->
    <div class="flex">
      <div class="main_menu">

        <div class="menu_wrap">
          <ul>
            <li>
            <?php putoutLink('applicate_album.php','アルバム作成申請'); ?>
            </li>
            <li>
            <?php putoutLink('prof_edit.php','プロフィール編集'); ?>
            </li>
            <li>
            <?php putoutLink('pass_edit.php','パスワード変更'); ?>
            </li>
            <li>
            <?php putoutLink('contact.php','お問い合わせ'); ?>
            </li>
            <li>
            <?php putoutLink('withdraw.php','退会'); ?>
            </li>
          </ul>
        </div>
      </div>

      <div class="main_article">
      
        <div class="main_title">
          パスワード変更
        </div>


            <p class="pb-30 center"></p>
        
        <div class="form">
          <form method="post" action="">
          <label>古いパスワード 
            <span class="warning">
              <?php getErrMsg('common') ?>
              <?php getErrMsg('pass_old') ?>
            </span>
            <div class="cp_iptxt">
              <input type="password" placeholder="古いパスワード" name="pass_old" value="">
              <i class="fa fa-recycle fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>新しいパスワード
            <span class="warning">
              <?php getErrMsg('pass_new') ?>
            </span>
            <div class="cp_iptxt">
              <input type="password" placeholder="新しいパスワード" name="pass_new" value="">
              <i class="fa fa-unlock fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>新しいパスワード（再入力）
            <span class="warning">
              <?php getErrMsg('pass_new_re') ?>
            </span>
            <div class="cp_iptxt">
              <input type="password" placeholder="新しいパスワード（再入力）" name="pass_new_re" value="">
              <i class="fa fa-unlock-alt fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>



          <div class="center pb-30">
          <button type="submit" name="button_submit" value="変更する" class="button">変更する</button>
          </div>        
          </form>
          </div>
        </div>
    
    </div>

  </div>
</main>



<?php
require('footer.php');
?>