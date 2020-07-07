<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　退会ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();



require('auth.php');

$dbUserData = getUser($_SESSION['user_id']);

$page_flg = 0;

if(!empty($_POST) && $page_flg === 0) {
  debug('１度目の退会ボタン押されました。');

  $page_flg = 1;
}

if(!empty($_POST['button_submit']) && $page_flg === 1) {
  debug('２度目の退会ボタン押されました。');


  try {

    $dbh = dbConnect();
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id=:id';
    $sql2 = 'UPDATE favorites SET delete_flg = 1 WHERE users_id=:id';
    $sql3 = 'UPDATE reviews SET delete_flg = 1 WHERE from_users_id=:id';
    $data = array(
      ':id' => $dbUserData['id'],
    );

    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);

    if($stmt1) {
      session_destroy();
      debug('セッション変数の中身：'.print_r($_SESSION,true));
      debug('トップページへ遷移します。');
      header("Location:signup.php");
      return;
    }


  } catch(PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG06;
  }

}


?>

<?php
$siteTitle = '退会ページ';
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
          退会
        </div>


            <p class="pb-30 center"></p>
        
        <div class="form">
          <form method="post" action="">

          <?php if($page_flg === 0) : ?>
          <div class="center pb-30">
          <button type="submit" name="button_confirm" value="退会する" class="button">退会する</button>
          </div>        
          <?php endif; ?>


          <?php if($page_flg === 1) : ?>

            <p class="pb-30 pt-20 center">本当に退会しますか？</p>
            <span class="warning">
              <?php getErrMsg('common') ?>
            </span>
            <div class="center pb-30">
            <button type="submit" name="button_submit" value="退会する" class="button">退会する</button>
          </div>        
          <?php endif; ?>
          </form>
          </div>
        </div>
    
    </div>

  </div>
</main>



<?php
require('footer.php');
?>