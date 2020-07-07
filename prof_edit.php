<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();



require('auth.php');

// echo '<pre>';
// var_dump($_POST);
// var_dump($_FILES);

// echo '</pre>';


//$dbUserData データベースからのuserデータ

//getFormData フォームに表示させるための関数
//　　　　　　POSTで送信したか、GETで送信したか、
//　　　　　　データベースに情報があるか、どうか
//　　　　　　フォームエラーがあるか、どうか


$dbUserData = getUser($_SESSION['user_id']);

if(!empty($_POST)) {

  debug('POST情報：' . print_r($_POST,true));
  debug('POST情報：' . print_r($_FILES,true));

  //それぞれ投稿内容を変数に入れる
  //$birthは入っていなかったら0を入れる(int型のためデフォルトで0が入っている)
  //DBデータと違った場合のバリデーションがあるため、
  //空で入れると、DBが0のためバリデーションを通ってしまう。

  //$picは、空の場合は空文字を入れるが、
  //DBに情報がある場合は、その情報を入れる。
  //DBにも情報がなく、入力もなければ空のまま。
  $username = $_POST['username'];
  $email = $_POST['email'];
  $birth = !empty($_POST['birth']) ? (int)$_POST['birth'] : 0;
  $comment = $_POST['comment'];
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
  $pic = (empty($pic) && !empty($dbUserData['pic'])) ? $dbUserData['pic'] : $pic;


  //DBデータと違った場合（変更した場合にバリデーションをする）
  if($dbUserData['username'] !== $username) {
    validMaxLen($username, 'username');
    validRequired($username, 'username');
  }
  if($dbUserData['email'] !== $email) {
    validEmail($email, 'email');
    validEmailDup($email);
    validRequired($email, 'email');
  }
  if((int)$dbUserData['birth'] !== $birth) {
    validLength($birth, 'birth', 4);
  }
  if($dbUserData['comment'] !== $comment) {
    validMaxLen($comment,'comment');
  }

  if(empty($err_msg)) {
    debug('バリデーションOKです。');
    
    try {

      $dbh = dbConnect();
      $sql = 'UPDATE users SET username=:username, email=:email, birth=:birth, comment=:comment, pic=:pic WHERE id=:user_id AND delete_flg = 0';
      $data = array(
        ':username' => $username,
        ':email' => $email,
        ':birth' => $birth,
        ':comment' => $comment,
        ':pic' => $pic,
        ':user_id' => $dbUserData['id'],
      );

      $stmt = queryPost($dbh, $sql, $data);

      if($stmt) {
        debug('成功です。');

        $_SESSION['msg_success'] = '編集しました。';

        debug('再読み込みします。');
        header('Location: prof_edit.php');
        return;
      }

    } catch(PDOException $e) {
      error_log('SQLエラー' . $e->getMessage());
    }
  }

}





debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = 'プロフィール編集';
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
        <?php getName($_SESSION['user_id']) ?> さんのプロフィール編集
        </div>



            <p class="pb-30 pt-20 center">

            </p>
        
        <div class="form">
          <form method="post" action="" enctype="multipart/form-data">


          <span class="warning">
            <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
          </span>
          <div class="preview_wrap">
            <div class="img pb-10">
              <label for="pic1">
                <?php if(empty(getFormData('pic'))) : ?>
                <img src="noimage.png" class="preview" width="100" height="100" for="pic">
                <?php else: ?>
                <img src="<?php echo getFormData('pic') ?>" class="preview" width="100" height="100" for="pic">
                <?php endif; ?>
              </label>
            </div>
              <div class="pb-20">
                <label for="pic1" class="choosepic">+写真を選択
                <input type="file" name="pic" id="pic1" accept="image/*" style="display:none;">
                </label>
              </div>
            </div>
          <label>ニックネーム 
            <span class="warning">
              <?php if(!empty($err_msg['username'])) echo $err_msg['username'] ?>
            </span>
            <div class="cp_iptxt">
              <input type="text" placeholder="ニックネーム" name="username" value="<?php echo getFormData('username') ?>">
              <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>メールアドレス
            <span class="warning">
              <?php if(!empty($err_msg['email'])) echo $err_msg['email'] ?>
            </span>
            <div class="cp_iptxt">
              <input type="text" placeholder="メールアドレス" name="email" value="<?php echo getFormData('email') ?>">
              <i class="fa fa-envelope fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>生まれた年 
            <span class="warning">
              <?php if(!empty($err_msg['birth'])) echo $err_msg['birth'] ?>
            </span>
            <div class="cp_ipselect cp_sl02">
              <select name="birth">
              <option value="" hidden>選択してください</option>
              <?php
                for($number = 1940; $number <= 2020; $number++) {
                    if((int)$dbUserData['birth'] === $number) {
                      $selected = 'selected';
                    } else {
                      $selected = '';
                    }
                    echo '<option value="' . $number . '" ' . $selected . '>' . $number . '</option>';
                }
              ?>
              </select>
            </div>
          </label>

          <label>ひとこと
            <span class="warning">
              <?php if(!empty($err_msg['comment'])) echo $err_msg['comment'] ?>
            </span>
            <div class="cp_iptxt">
              <i class="fa fa-compact-disc fa-lg fa-fw" aria-hidden="true"></i>
              <textarea placeholder="好きなアーティストをおしえてください" name="comment" style="height: 100px;"><?php echo getFormData('comment') ?></textarea>
            </div>
          </label>

          <div class="center pb-30">
          <button type="submit" name="button_confirm" value="編集完了" class="button">編集完了</button>
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