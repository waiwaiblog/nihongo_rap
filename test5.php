<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　アルバム詳細ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();



// require('auth.php');


$userData = getUser($_SESSION['user_id']);


$detail_num = $_GET['d'];

function getAlbum($num) {

  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM details WHERE id=:num';
    $data = array(
      ':num' => $num,
    );
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch(PDOException $e) {
    error_log('SQLエラー発生');
  }
  
}

$result = getAlbum($detail_num);



$nowPage = (!empty($_GET['r'])) ? $_GET['r'] : 1;

//レビュー表示件数
$listSpan = 5;

//表示レコード先頭を算出 1ページ目なら0から。2ページ目なら10から。
$minPage = ($nowPage-1) * $listSpan;

function displayReview($minPage, $listSpan = 10) {
  global $detail_num;
  $dbh = dbConnect();
  $sql = 'SELECT count(id) FROM reviews WHERE details_id = :details_id AND delete_flg = 0';
  $data = array(
    ':details_id' => $detail_num,
  );
  $stmt = queryPost($dbh, $sql, $data);
  $rst['total'] = $stmt->fetchColumn();
  $rst['total_page'] = ceil($rst['total'] / $listSpan); //総レコード数÷ページあたりの表示数で総ページ数を算出する
  if(!$stmt) {
    return false;
  }
  $sql = "SELECT * FROM reviews WHERE  details_id = ? AND delete_flg = 0";
  $sql .= " ORDER BY id DESC";
  $sql .= " LIMIT ? OFFSET ?";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $detail_num, PDO::PARAM_INT);
  $stmt->bindValue(2, $listSpan, PDO::PARAM_INT);
  $stmt->bindValue(3, $minPage, PDO::PARAM_INT);
  $stmt->execute();
  $rst['data'] = $stmt->fetchAll();
  return $rst;
}

function getReview($num) {
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM reviews WHERE details_id=:num';
    $data = array(
      ':num' => $num,
    );
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch(PDOException $e) {
    error_log('SQLエラー発生');
  }
}

$reviewData = getReview($detail_num);


if(!empty($_POST)) {
  $reviews_title = $_POST['reviews_title'];
  $star = $_POST['star'];
  $reviews_comment = $_POST['reviews_comment'];
  
  validRequired($reviews_title, 'reviews_title');
  validRequired($star, 'star');
  validRequired($reviews_comment, 'reviews_comment');

  if(empty($err_msg)) {
    validMaxLen($reviews_title, 'reviews_title');
    validMaxLen($reviews_comment, 'reviews_comment');
    validMinLen($reviews_comment, 'reviews_comment');

    if(empty($err_msg)) {

      try {
        $dbh = dbConnect();
        $sql = 'INSERT INTO reviews SET reviews_title=:reviews_title, star=:star, reviews_comment=:reviews_comment, from_users_id=:from_users_id, details_id=:details_id, send_date=:send_date, create_date=:create_date';
        $data = array(
          ':reviews_title' => $reviews_title,
          ':star' => $star,
          ':reviews_comment' => $reviews_comment,
          ':from_users_id' => $_SESSION['user_id'],
          ':details_id' => $detail_num,
          ':send_date' => date('Y-m-d H:i:s'),
          ':create_date' => date('Y-m-d H:i:s'),
        );
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt) {
          debug('成功');

          $_SESSION['msg_success'] = '投稿しました。';
          return;
        } else {
          debug('失敗');
        }
      } catch(PDOException $e) {
        error_log('SQLエラー:' . $e->getMessage());
      }
    }
  }
}
?>

<?php
$siteTitle = 'アルバム詳細ページ';
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
    <div class="detail_flex">
      <div class="detail_left">
        <img src="<?php echo $result['pic'] ?>">
      </div>
      <div class="detail_right">
      <h3><?php echo $result['years'] ?>年リリース</h3>
      <h1><?php echo $result['details_title'] ?></h1>
      <h2>（<?php echo $result['artist'] ?>）</h2>
      <div class="detail_songlist">
        <pre><?php echo $result['song_list'] ?></pre>
      </div>
      </div>
    </div>
      <div class="review_container">


      
        <h2>このアルバムついてコメント一覧</h2>
        <?php if(!empty($_SESSION['user_id'])): ?>
        <p><a class="js-modal-open" href="">コメントを投稿する</a>
        <?php else: ?>
        <a href="login.php">ログイン</a>すればコメントを投稿できます。
        <?php endif; ?></p>
        
        <div class="review">
          <?php echo $reviewData['reviews_title'] ?>
        </div>
    
    </div>
  </div>
</main>

<div class="modal js-modal">
    <div class="modal__bg js-modal-close"></div>
    <div class="modal__content">


    <form method="post" action="">


        <div class="cp_iptxt">
          <p><?php echo $userData['username'] ?></p>
          <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
        </div>

        <label>レビューの見出し
          <div class="cp_iptxt">
            <input type="text" placeholder="見出し" name="reviews_title" value="<?php if(!empty($_POST['reviews_title'])) echo $_POST['reviews_title'] ?>" class="reviews_title">
            <i class="fa fa-bookmark fa-lg fa-fw" aria-hidden="true"></i>
          </div>
        </label>

        <label>星の数(5点満点)
          <div class="cp_ipselect cp_sl02">
            <select name="star" class="star">
            <option value="" hidden>選択してください</option>
            <?php
              for($starnum = 1; $starnum <= 5; $starnum++) {
                  if(!empty($_POST['star'])) {
                    if((int)$_POST['star'] === $starnum) {
                      $selected = 'selected';
                    } else {
                      $selected = '';
                    }
                  }
                  echo '<option value="' . $starnum . '" ' . $selected . '>' . '★×' . $starnum . '個' . '</option>';
              }
            ?>
            </select>
          </div>
        </label>

        <label>コメント
          <div class="cp_iptxt">
            <i class="fa fa-comment fa-lg fa-fw" aria-hidden="true"></i>
            <textarea placeholder="レビューよろしくお願いします。" name="reviews_comment" style="height: 300px;" class="reviews_comment"><?php if(!empty($_POST)) echo $_POST['reviews_comment'] ?></textarea>
          </div>
        </label>



        
        <div class="center pb-30">
        <button type="submit" name="button_submit" value="投稿する" class="button" id="chgDateSub">投稿する</button>
        </div>        
        </form>
        <a class="js-modal-close" href="">閉じる</a>
    </div><!--modal__inner-->
</div><!--modal-->




<?php
require('footer.php');
?>