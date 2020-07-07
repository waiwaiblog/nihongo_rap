<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


//ログイン認証
require('auth.php');

// セッションからusers idを取得
$userid = $_SESSION['user_id'];

//------------------アルバム取得用--------------
// １ページあたりのfavorites details表示数
$listSpan = 2;
// GETでdetailsの表示ページを取得
$nowPage = (!empty($_GET['m_d'])) ? $_GET['m_d'] : 1;
// LIMIT OFFSET句で使うために、DBの結果から何件目までを取るかを決める
$minPage = ($nowPage - 1) * $listSpan;


//------------------レビュー取得用--------------
$listSpan2 =  5;
$nowPage2 = (!empty($_GET['m_r'])) ? $_GET['m_r'] : 1;
$minPage2 = ($nowPage2 - 1) * $listSpan2;


//ページネーションリンク用
$link = '';
$link2 = '';
if(!empty($_GET['m_r'])) {
  $link .= '&m_r=' . $_GET['m_r'];
}
if(!empty($_GET['m_d'])) {
  $link2 .= '&m_d=' . $_GET['m_d'];
}

//-------------------users,detailsを紐づけて全てのカラムを取得
if(!empty($userid)) {
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM users 
            LEFT JOIN favorites ON users.id = favorites.users_id
            LEFT JOIN details ON details.id = favorites.details_id
            WHERE users.id = ?
            ORDER BY favorites.create_date DESC
            LIMIT ? OFFSET ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $userid, PDO::PARAM_INT);
    $stmt->bindValue(2, $listSpan, PDO::PARAM_INT);
    $stmt->bindValue(3, $minPage, PDO::PARAM_INT);
    $stmt->execute();

    if($stmt) {
      debug('成功' . print_r($stmt, true));
      $rst['data'] = $stmt->fetchAll();
    } else {
      debug('エラーです。ステートメント返ってこない');
    }

    $sql2 = 'SELECT count(details.id) AS cnt FROM users 
            LEFT JOIN favorites ON users.id = favorites.users_id
            LEFT JOIN details ON details.id = favorites.details_id
            WHERE users.id = :userid';
    $data = array(
      ':userid' => $userid,
    );
    $stmt2 = queryPost($dbh, $sql2, $data);
    if($stmt2) {
      debug('成功' . print_r($stmt2, true));
      $rst['total'] = $stmt2->fetchColumn();
      $rst['total_page'] = ceil($rst['total'] / $listSpan);//トータルページ数
      
    } else {
      debug('エラーです。ステートメント返ってこない');
    }
  } catch(PDOException $e) {
    error_log('SQLエラー:' . $e->getMessage());
  }
}


//----------確認用
// echo '<pre>';
// var_dump($rst);
// echo '</pre>';


//---------------------reviewsテーブル情報を取得
if(!empty($userid)) {
  try {
    $dbh = dbConnect();
    $sql = 'SELECT r.id AS reviewsid, r.reviews_title, r.send_date, d.details_title, d.artist, d.id AS detailsid FROM reviews AS r LEFT JOIN details AS d ON r.details_id = d.id 
            WHERE r.from_users_id = ? 
            ORDER BY r.create_date DESC 
            LIMIT ? OFFSET ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $userid, PDO::PARAM_INT);
    $stmt->bindValue(2, $listSpan2, PDO::PARAM_INT);
    $stmt->bindValue(3, $minPage2, PDO::PARAM_INT);
    $stmt->execute();
    
    if($stmt) {
      debug('SQL成功しました。' . print_r($stmt, true));
      $rev['data'] = $stmt->fetchAll();
    } else {
      debug('エラーです。ステートメント返ってこない');
    }
    
    $sql = 'SELECT count(id) FROM reviews WHERE from_users_id = ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $userid, PDO::PARAM_INT);
    $stmt->execute();
    if($stmt) {
      debug('SQL成功しました。' . print_r($stmt, true));
      $rev['total'] = $stmt->fetchColumn();
      $rev['total_page'] = ceil($rev['total'] / $listSpan2);
    } else {
      debug('エラーです。ステートメント返ってこない');
    }

  } catch(PDOException $e) {
    error_log('SQLエラー' . $e->getMessage());
  }
}



//-------------------削除ボタンポスト用
// var_dump($_POST);

if(!empty($_POST)) {

  $revid = $_POST['delete'];

    try {
      $dbh = dbConnect();
      $sql = 'DELETE FROM reviews WHERE id=:id';
      $data = array(
        ':id' => $revid,
      );
      $stmt = queryPost($dbh, $sql, $data);

      if($stmt) {
        debug('削除成功' . print_r($stmt, true));
        $_SESSION['msg_success'] = '削除しました。 ' . $revid;
        header('Location: mypage.php#review_anchor');
        return;
      } else {
        debug('削除失敗' . print_r($stmt,true));
      }
    } catch(PDOException $e) {
      error_log('SQLError:' . $e->getMessage());
    }

}

//------------------------パラメータ改ざん防止（ページバージョン）
//アルバム改ざん用
if(!empty($_GET['m_d'])) {
  if(!is_int((int)$_GET['m_d']) || (1 > $_GET['m_d']) || ($rst['total_page'] < $_GET['m_d'])) {
    header('Location: mypage.php');
  }
}
//レビュー改ざん用
if(!empty($_GET['m_r'])) {
  if(!is_int((int)$_GET['m_r']) || (1 > $_GET['m_r']) || ($rev['total_page'] < $_GET['m_r'])) {
    header('Location: mypage.php');
  }
}


//------------------ページネーション3個表示用
function pagination($nowPage, $totalPage, $link = '', $pageColNum = 3){
  // 現在のページが、総ページ数と同じなら左にリンク2個出す（総ページ数が表示項目数以上）
  if( $nowPage == $totalPage && $totalPage > $pageColNum){
    $minPageNum = $nowPage - 2;
    $maxPageNum = $nowPage;
  // 現ページが2の場合は左にリンク１個、右にリンク１個だす（総ページ数が表示項目数以上）
  }elseif( $nowPage == 2 && $totalPage > $pageColNum){
    $minPageNum = $nowPage - 1;
    $maxPageNum = $nowPage + 1;
  // 現ページが1の場合は左に何も出さない。右に2個出す（総ページ数が表示項目数以上）
  }elseif( $nowPage == 1 && $totalPage > $pageColNum){
    $minPageNum = $nowPage;
    $maxPageNum = 3;
  // 総ページ数をループのMax、ループのMinを１に設定（総ページ数が表示項目数以下の場合）
  }elseif($totalPage <= $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPage;
  // それ以外は左右に１個出す。
  }else{
    $minPageNum = $nowPage - 1;
    $maxPageNum = $nowPage + 1;
  }
  

  echo '<div class="pagination_wrap" style="width:150px">';
  echo '<div class="pagination_list">';
    echo '<ul style="background-color:#ccc">';
      if($nowPage != 1){
        echo '<li><a href="?m_d=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="';
        if($nowPage == $i ){ echo 'active'; }
        echo '"><a href="?m_d='.$i.$link.'">'.$i.'</a></li>';
      }
      if($nowPage != $maxPageNum && $maxPageNum > 1){
        echo '<li><a href="?m_d='.$totalPage.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
  echo '</div>';
}

//------------------ページネーション3個表示用
function pagination2($nowPage, $totalPage, $link = '', $pageColNum = 3){
  // 現在のページが、総ページ数と同じなら左にリンク2個出す（総ページ数が表示項目数以上）
  if( $nowPage == $totalPage && $totalPage > $pageColNum){
    $minPageNum = $nowPage - 2;
    $maxPageNum = $nowPage;
  // 現ページが2の場合は左にリンク１個、右にリンク１個だす（総ページ数が表示項目数以上）
  }elseif( $nowPage == 2 && $totalPage > $pageColNum){
    $minPageNum = $nowPage - 1;
    $maxPageNum = $nowPage + 1;
  // 現ページが1の場合は左に何も出さない。右に2個出す（総ページ数が表示項目数以上）
  }elseif( $nowPage == 1 && $totalPage > $pageColNum){
    $minPageNum = $nowPage;
    $maxPageNum = 3;
  // 総ページ数をループのMax、ループのMinを１に設定（総ページ数が表示項目数以下の場合）
  }elseif($totalPage <= $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPage;
  // それ以外は左右に１個出す。
  }else{
    $minPageNum = $nowPage - 1;
    $maxPageNum = $nowPage + 1;
  }
  

  echo '<div class="pagination_wrap" style="width:150px">';
  echo '<div class="pagination_list">';
    echo '<ul style="background-color:#ccc">';
      if($nowPage != 1){
        echo '<li><a href="?m_r=1'.$link.'#review_anchor">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="';
        if($nowPage == $i ){ echo 'active'; }
        echo '"><a href="?m_r='.$i.$link.'#review_anchor">'.$i.'</a></li>';
      }
      if($nowPage != $maxPageNum && $maxPageNum > 1){
        echo '<li><a href="?m_r='.$totalPage.$link.'#review_anchor">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
  echo '</div>';
}

?>

<?php
$siteTitle = 'マイページ';
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
        <b><?php getName($_SESSION['user_id']) ?></b> さんのマイページ
        </div>

        <h4 class="center pb-30 pt-10">お気に入り(<?php echo $rst['total'] ?>件)</h4>
        <div class="top_wrap flex">
          <?php if($rst['total'] == 0) :?>
            お気に入りはありません
          <?php else: ?>
          <?php foreach($rst['data'] as $key => $val) : ?>
            <?php if($val['years'] === '2000') {
                $years = $val['years'] . '年以降';
              } else {
                $years = $val['years'] . '年';
              }
            ?>
            <div class="top_article" style="margin-bottom:10px">
              <a href="detail.php?d=<?php echo $val['id'] ?>"><img src="<?php echo $val['pic'] ?>" width="250" height="250" class="hover"></a><br>
              <p><b><?php echo $val['details_title'] ?></b></p>
              <p>(<?php echo $val['artist'] ?>)</p>
              <p><?php echo $years ?></p>
              
            </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php pagination($nowPage, $rst['total_page'], $link); ?>


        
        <h4 class="center pb-10 pt-30" id="review_anchor">レビュー履歴(<?php echo $rev['total'] ?>件)</h4>
        <div class="admin_table pt-20">

          <table class="cp_table">
            <thead>
            <tr>
              <th>アルバム名</th>
              <th>アーティスト名</th>
              <th>レビュータイトル</th>
              <th>投稿日</th>
              <th>削除</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach($rev['data'] as $key => $val) : ?>
              <tr>
              <td><a href="detail.php?d=<?php echo $val['detailsid'] ?>#review_container"><?php echo $val['details_title'] ?></a></td>
              <td><?php echo $val['artist'] ?></td>
              <td><?php echo $val['reviews_title'] ?></td>
              <td><?php echo $val['send_date'] ?></td>
              <td>
                <form action="" method="post" name="deleteform" id="deleteform">
                  <button type="submit" name="delete" id="mypagedelete" value="<?php echo $val['reviewsid'] ?>">削除</button>
                </form>
              </td>
              </tr>

            
            <?php endforeach; ?>
            </tbody>
          </table>

          <?php pagination2($nowPage2, $rev['total_page'], $link2); ?>
        </div>
      </div>
    
    </div>

  </div>
</main>




<?php
require('footer.php');
?>