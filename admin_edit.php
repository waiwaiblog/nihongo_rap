<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　【管理者ページ】編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if(!empty($_SESSION)) {
  if($_SESSION['user_id'] !== '1') {
    header('Location: login.php');
  }
} else {
  header('Location: login.php');
}

$get_flg = true;

if(!empty($_GET)) {
  $applicate_id = $_GET['id'];
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM applicate WHERE id=:id';

    $data = array(
      ':id' => $applicate_id,
    );
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

  } catch(PDOException $e) {
    error_log('SQLエラー:' . $e->getMessage());
  }

} else {
  $get_flg = false;
}


if(!empty($_POST) && !empty($_FILES)) {
  echo '<pre>';
  var_dump($_POST);
  var_dump($_FILES);
  echo '</pre>';

  $artist = $_POST['artist'];
  $title = $_POST['details_title'];
  $song_list = $_POST['song_list'];
  $years = $_POST['years'];
  $users_id = $_POST['users_id'];
  $create_date = $_POST['create_date'];
  $pic = uploadImg($_FILES['pic'], 'pic');

  
  try {

    $dbh = dbConnect();
    $sql = 'INSERT INTO details SET details_title=:title, artist=:artist, song_list=:list, years=:years, users_id=:users_id, pic=:pic, create_date=:create_date';
    $data = array(
      ':title' => $title,
      ':artist' => $artist,
      ':list' => $song_list,
      ':years' => $years,
      ':users_id' => $users_id,
      ':pic' => $pic,
      ':create_date' => $create_date,
    );
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt) {

      $sql2 = 'UPDATE applicate SET flg = 1 WHERE id=:id';
      $data2 = array(
        ':id' => $applicate_id,
      );
      $stmt2 = queryPost($dbh, $sql2, $data2);


      $_SESSION['msg_success'] = '投稿しました。';

      header('Location: admin.php');
      return;

    } else {
      debug('投稿えらー');
      $err_msg['common'] = '投稿エラーです';
    }

  } catch(PDOException $e) {
    error_log('SQLエラー:' . $e->getMessage());
  }
}


?>

<?php
if($get_flg) {
  $siteTitle = '編集';
} else {
  $siteTitle = '新規登録';
}
require('head.php');
?>



<body>




<main>
  <div class="main_wrap">
    
  <!-- 2カラム用div -->
    <div class="flex">
      <div class="main_menu">

        <div class="menu_wrap">
          <ul>
            <li>
            <?php putoutLink('admin.php','アルバム申請一覧'); ?>
            </li>
          </ul>
        </div>
      </div>

      <div class="main_article">
        <p style="text-align:right"><a href="admin.php">戻る</a></p>
        <div class="main_title">
          管理者画面： 
          <?php if($get_flg) : ?>
          <b><?php echo $result['album'] ?></b>
          <?php else: ?>
          <b>新規投稿</b>
          <?php endif; ?>
            のページ
        </div>

        <span class="warning">
          <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
        </span>
        <div class="form pt-20">
          <form method="post" action="" enctype="multipart/form-data">
          <label>アーティスト名 
            <div class="cp_iptxt">
              <input type="text" placeholder="アーティスト名" name="artist" value="<?php  if($get_flg) echo $result['artist'] ?>">
              <i class="fa fa-users fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>アルバム名 
            <div class="cp_iptxt">
              <input type="text" placeholder="アルバム名" name="details_title" value="<?php if($get_flg) echo $result['album'] ?>">
              <i class="fa fa-compact-disc fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>リリース年 
            <div class="cp_ipselect cp_sl02">
              <select name="years" required>
              <option value="" hidden>選択してください</option>
              <?php if($get_flg): ?>
              <option value="1990" <?php checkSelectBox2($result, 'year', '1990') ?>>1990年</option>
              <option value="1991" <?php checkSelectBox2($result, 'year', '1991') ?>>1991年</option>
              <option value="1992" <?php checkSelectBox2($result, 'year', '1992') ?>>1992年</option>
              <option value="1993" <?php checkSelectBox2($result, 'year', '1993') ?>>1993年</option>
              <option value="1994" <?php checkSelectBox2($result, 'year', '1994') ?>>1994年</option>
              <option value="1995" <?php checkSelectBox2($result, 'year', '1995') ?>>1995年</option>
              <option value="1996" <?php checkSelectBox2($result, 'year', '1996') ?>>1996年</option>
              <option value="1997" <?php checkSelectBox2($result, 'year', '1997') ?>>1997年</option>
              <option value="1998" <?php checkSelectBox2($result, 'year', '1998') ?>>1998年</option>
              <option value="1999" <?php checkSelectBox2($result, 'year', '1999') ?>>1999年</option>
              <option value="2000" <?php checkSelectBox2($result, 'year', '2000') ?>>2000年以降</option>
              <?php endif; ?>
              <option value="1990">1990年</option>
              <option value="1991">1991年</option>
              <option value="1992">1992年</option>
              <option value="1993">1993年</option>
              <option value="1994">1994年</option>
              <option value="1995">1995年</option>
              <option value="1996">1996年</option>
              <option value="1997">1997年</option>
              <option value="1998">1998年</option>
              <option value="1999">1999年</option>
              <option value="2000">2000年以降</option>

              </select>
            </div>
          </label>

          <label>曲目 その他 
            <div class="cp_iptxt">
              <i class="fa fa-book-open fa-lg fa-fw" aria-hidden="true"></i>
              <textarea placeholder="曲目 その他コメントなど" name="song_list" value=""><?php if($get_flg) echo $result['song_list'] ?></textarea>
            </div>
          </label>


          <label>投稿者ID 
            <div class="cp_iptxt">
              <input type="text" placeholder="投稿者ID" name="users_id" value="<?php
                if($get_flg) {
                  echo $result['from_user'];
                } else {
                  echo $_SESSION['user_id'];
                }
                ?>">
              <i class="fa fa-address-card fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <label>投稿日 
            <div class="cp_iptxt">
              <input type="text" placeholder="投稿日" name="create_date" value="<?php
              if($get_flg) {
                echo $result['send_date']; 
              } else {
                echo date('Y-m-d H:i:s');
              }
              ?>">
              <i class="fa fa-calendar-day fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>


          <label>ジャケット画像 
            <div class="cp_iptxt">
              <input type="file" placeholder="ジャケット画像" name="pic" value="">
              <i class="fa fa-compact-disc fa-lg fa-fw" aria-hidden="true"></i>
            </div>
          </label>

          <div class="center pb-30">
          <button type="submit" name="button_submit" value="投稿する" class="button">投稿する</button>
          </div>        
          </form>
        </div>
      </div>
    
    </div>

  </div>
</main>



<script src="js/jquery-2.2.2.min.js"></script>
  <script>
  $(function(){
    var $ftr = $('.footer_wrap');
    if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
      $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
    }

    // メッセージ表示
    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
      $jsShowMsg.slideToggle('slow');
      setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 4000);
    }
  });
  </script>
</body>
</html>