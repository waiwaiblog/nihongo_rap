<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　アルバムページ作成申請ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();



require('auth.php');

// echo '<pre>';
// var_dump($_POST);
// echo '</pre>';

$page_flg = 0;

//POST送信されているとき
if(!empty($_POST) && $page_flg === 0) {

  debug('投稿ボタン押されました。ページ：' . print_r($page_flg,true));

  $artist = $_POST['artist'];
  $album = $_POST['album'];
  $year = $_POST['year'];
  $song_list = $_POST['song_list'];

  validRequired($artist, 'artist');
  validRequired($album, 'album');
  validRequired($year, 'year');
  validRequired($song_list, 'song_list');

  if(empty($err_msg)) {

    debug('未入力バリッドパスしました');
    validMinLen($song_list, 'song_list');

    
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
    $sql = 'INSERT INTO applicate SET artist=:artist, album=:album, year=:year, song_list = :song_list, from_user=:from_user, send_date=:send_date';
    $data = array(
      ':artist' => $artist,
      ':album' => $album,
      ':year' => $year,
      ':song_list' => $song_list,
      ':from_user' => $_SESSION['user_id'],
      ':send_date' => date('Y-m-d H:i:s'),
    );
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt) {
      
      $_SESSION['msg_success'] = '送信されました。';
      
      debug('セッション変数の中身：'.print_r($_SESSION,true));
      
      $page_flg = 2;
      
      }

    } catch(PDOException $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG06;
    }

}




?>

<?php
$siteTitle = 'アルバムページ作成申請';
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
          アルバム作成申請
        </div>

          <?php if($page_flg === 0) : ?>

            <p class="pb-30 pt-20 center">このアルバムを追加してほしい、という要望がありましたらご投稿下さい。<br>
        管理者が確認後、掲載いたします。</p>
        
        <div class="form">
          <form method="post" action="">
          <label>アーティスト名 
            <span class="warning">
              <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
              <?php if(!empty($err_msg['artist'])) echo $err_msg['artist'] ?>
            </span>
            <div class="cp_iptxt">
              <input type="text" placeholder="アーティスト名" name="artist" value="<?php if(!empty($_POST['artist'])) echo $_POST['artist'] ?>">
              <i class="fa fa-users fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>アルバム名 
            <span class="warning">
              <?php if(!empty($err_msg['album'])) echo $err_msg['album'] ?>
            </span>
            <div class="cp_iptxt">
              <input type="text" placeholder="アルバム名" name="album" value="<?php if(!empty($_POST['album'])) echo $_POST['album'] ?>">
              <i class="fa fa-compact-disc fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>リリース年 
            <span class="warning">
              <?php if(!empty($err_msg['year'])) echo $err_msg['year'] ?>
            </span>
            <div class="cp_ipselect cp_sl02">
              <select name="year" required>
              <option value="" hidden>選択してください</option>
              <option value="1990" <?php checkSelectBox('1990') ?>>1990年</option>
              <option value="1991" <?php checkSelectBox('1991') ?>>1991年</option>
              <option value="1992" <?php checkSelectBox('1992') ?>>1992年</option>
              <option value="1993" <?php checkSelectBox('1993') ?>>1993年</option>
              <option value="1994" <?php checkSelectBox('1994') ?>>1994年</option>
              <option value="1995" <?php checkSelectBox('1995') ?>>1995年</option>
              <option value="1996" <?php checkSelectBox('1996') ?>>1996年</option>
              <option value="1997" <?php checkSelectBox('1997') ?>>1997年</option>
              <option value="1998" <?php checkSelectBox('1998') ?>>1998年</option>
              <option value="1999" <?php checkSelectBox('1999') ?>>1999年</option>
              <option value="2000" <?php checkSelectBox('2000') ?>>2000年以降</option>
              </select>
            </div>
          </label>

          <label>曲目 その他 
            <span class="warning">
              <?php if(!empty($err_msg['song_list'])) echo $err_msg['song_list'] ?>
            </span>
            <div class="cp_iptxt">
              <i class="fa fa-compact-disc fa-lg fa-fw" aria-hidden="true"></i>
              <textarea placeholder="曲目 その他コメントなど" name="song_list" value=""><?php if(!empty($_POST['song_list'])) echo $_POST['song_list'] ?></textarea>
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
          <label>アーティスト名 
            <div class="cp_iptxt">
              <p><?php echo $_POST['artist'] ?></p>
              <i class="fa fa-users fa-lg fa-fw" aria-hidden="true"></i>
              <input type="hidden" name="artist" value="<?php echo $_POST['artist'] ?>">
            </div>
          </label>

          <label>アルバム名 
            <div class="cp_iptxt">
              <p><?php echo $_POST['album'] ?></p>
              <i class="fa fa-compact-disc fa-lg fa-fw" aria-hidden="true"></i>
              <input type="hidden" name="album" value="<?php echo $_POST['album'] ?>">
            </div>
          </label>

          <label>リリース年 
            <div class="cp_ipselect cp_sl02">
              <p><?php echo $_POST['year'] ?>年</p>
              <input type="hidden" name="year" value="<?php echo $_POST['year'] ?>">
            </div>
          </label>

          <label>曲目 その他 
            <div class="cp_iptxt">
              <pre><p><?php echo $_POST['song_list'] ?></p></pre>
              <i class="fa fa-compact-disc fa-lg fa-fw" aria-hidden="true"></i>
              <input type="hidden" name="song_list" value="<?php echo $_POST['song_list'] ?>">
            </div>
          </label>

          <div class="center pb-30">
            <button type="submit" name="back" value="戻る" class="button">書き直す</button> 
            <button type="submit" name="button_submit" value="登録する" class="button">送信する</button>
          </div>        
          </form>
        
          </div>
          <?php endif; ?>
          <?php if($page_flg === 2): ?>
            <p class="pb-30 pt-20 center">送信されました。<br>管理者が確認後、掲載致しますのでお待ちください。</p>
            <p class="center">引き続き登録申請をするからは<a href="applicate_album.php">こちら</a>。</p>
          <?php endif; ?>
        
      </div>
    
    </div>

  </div>
</main>



<?php
require('footer.php');
?>