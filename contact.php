<?php 

require('function.php');
require ('MailTest.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　お問い合わせページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();



require('auth.php');

$dbUserData = getUser($_SESSION['user_id']);
$page_flg = 0;

//POST送信されているとき
if(!empty($_POST) && $page_flg === 0) {

  debug('投稿ボタン押されました。ページ：' . print_r($page_flg,true));

  $username = h($dbUserData['username']);
  $email = h($dbUserData['email']);
  $message = h($_POST['message']);

  validRequired($message, 'message');

  if(empty($err_msg)) {

    debug('未入力バリッドパスしました');

    validMinLen($message, 'message');

    
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
  

  $to = 'webmaster@waiwaiblog.net';
  $subject = 'お問い合わせあり｜日本語ラップレビューの会';
  //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
  //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
  $comment = <<<EOT
{$username}({$email})　さんよりメールが届きました。

{$message}
              
////////////////////////////////////////
日本語ラップレビューの会
////////////////////////////////////////
EOT;
  $send = new MailTest();
  $send->sendMail($subject, $comment, $to);

  $_SESSION['msg_success'] = '送信されました。';
      
  debug('セッション変数の中身：'.print_r($_SESSION,true));
  
  $page_flg = 2;
  return;

}




?>

<?php
$siteTitle = 'お問い合わせ';
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
          お問い合わせ
        </div>

          <?php if($page_flg === 0) : ?>

            <p class="pb-30 pt-20 center">何かお気付きの点ありましたらご連絡ください。<br>１週間経っても返信がない場合は再度お問い合わせ下さい。</p>
        
        <div class="form">
          <form method="post" action="">

          <label>ニックネーム
            <div class="cp_iptxt">
              <p><?php echo $dbUserData['username'] ?> さん</p>
              <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>メールアドレス
            <div class="cp_iptxt">
              <p><?php echo $dbUserData['email'] ?></p>
              <i class="fa fa-envelope fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>お問い合わせ内容
            <span class="warning">
              <?php if(!empty($err_msg['message'])) echo $err_msg['message'] ?>
            </span>
            <div class="cp_iptxt">
              <i class="fa fa-comment-alt fa-lg fa-fw" aria-hidden="true"></i>
              <textarea placeholder="お問い合わせ内容" name="message" value="" style="height:200px"><?php if(!empty($_POST['message'])) echo $_POST['message'] ?></textarea>
            </div>
          </label>

          <div class="center pb-30">
          <button type="submit" name="button_confirm" value="確認する" class="button">確認する</button>
          </div>        
          </form>
          </div>
          <?php endif; ?>
          <?php if($page_flg === 1) : ?>

            <p class="pb-30 pt-20 center">以下でよろしいですか？</p>
        
        <div class="form">
          <form method="post" action="">
          <label>ニックネーム
            <div class="cp_iptxt">
              <p><?php echo $dbUserData['username'] ?> さん</p>
              <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>メールアドレス
            <div class="cp_iptxt">
              <p><?php echo $dbUserData['email'] ?></p>
              <i class="fa fa-envelope fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>お問い合わせ内容
            <div class="cp_iptxt">
            <pre><p><?php echo $_POST['message'] ?></p></pre>
              <i class="fa fa-comment-alt fa-lg fa-fw" aria-hidden="true"></i>
              <input type="hidden" name="message" value="<?php echo $_POST['message'] ?>">
            </div>
          </label>


          <div class="center pb-30">
            <button type="submit" name="back" value="戻る" class="button">書き直す</button> 
            <button type="submit" name="button_submit" value="送信する" class="button">送信する</button>
          </div>        
          </form>
        
          </div>
          <?php endif; ?>
          <?php if($page_flg === 2): ?>
            <p class="pb-30 pt-20 center">送信されました。</p>
          <?php endif; ?>
        
      </div>
    
    </div>

  </div>
</main>



<?php
require('footer.php');
?>