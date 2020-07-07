<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　トップページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


//何も指定がない時、p=1とする
$nowPage = (!empty($_GET['p'])) ? $_GET['p'] : 1;



// if(!is_int($nowPage)){
  //   error_log('エラー発生:指定ページに不正な値が入りました');
  //   header("Location:index.php"); //トップページへ
  // }
  
  //表示件数
  $listSpan = 10;
  
  $link = '';
  
  //表示レコード先頭を算出 1ページ目なら0から。2ページ目なら10から。
  $minPage = ($nowPage-1) * $listSpan;
  
  if(!empty($_GET['ry'])) {
    $link .= '&ry=' . $_GET['ry'];
    $releaseYear = (int)$_GET['ry'];
  } else {
    $releaseYear = 0;
  }
  if(!empty($_GET['ws'])) {
    $link .= '&ws=' . $_GET['ws'];
    $wordSearch = $_GET['ws'];
  } else {
    $wordSearch = 0;
  }
  
  
  function displayAlbum($minPage, $listSpan = 10, $releaseYear, $wordSearch) {
    $dbh = dbConnect();
    $sql = 'SELECT count(id) FROM details WHERE delete_flg = 0';
    if(!empty($releaseYear)) {
      $sql .= " AND years = '{$releaseYear}'";
    }
    if(!empty($wordSearch)) {
      $sql .= " AND (details_title LIKE \"%{$wordSearch}%\" OR artist LIKE \"%{$wordSearch}%\")";
    }
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->fetchColumn();
    $rst['total_page'] = ceil($rst['total'] / $listSpan); //総レコード数÷ページあたりの表示数で総ページ数を算出する
    if(!$stmt) {
      return false;
    }
    
    
    
    $sql = "SELECT * FROM details WHERE delete_flg = 0";
    if(!empty($releaseYear)) {
      $sql .= " AND years = '{$releaseYear}'";
    }
    
    if(!empty($wordSearch)) {
      $sql .= " AND (details_title LIKE \"%{$wordSearch}%\" OR artist LIKE \"%{$wordSearch}%\")";
    }
    
    
    
    $sql .= " ORDER BY id DESC";
    $sql .= " LIMIT ? OFFSET ?";
    debug($sql);
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $listSpan, PDO::PARAM_INT);
    $stmt->bindValue(2, $minPage, PDO::PARAM_INT);
    $stmt->execute();
    $rst['data'] = $stmt->fetchAll();
    return $rst;
  }
  
  $rst = displayAlbum($minPage, $listSpan, $releaseYear, $wordSearch);
  
//------------------------パラメータ改ざん防止（ページバージョン）
  if(!empty($_GET['p'])) {
    if(!is_int((int)$_GET['p']) || (1 > $_GET['p']) || ($rst['total_page'] < $_GET['p'])) {
      header('Location: index.php');
    }
  }

//------------------------パラメータ改ざん防止（年数検索バージョン）
//年を配列にして、その中になかったらindex.phpに飛ばす処理をする
if(!empty($_GET['ry'])) {
  if(!in_array($_GET['ry'],['1990','1991','1992','1993','1994','1995','1996','1997','1998','1999','2000'],true)) {
    header('Location: index.php');
  }
}

function pagination($nowPage, $totalPage, $link = '', $pageColNum = 5){
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if( $nowPage == $totalPage && $totalPage > $pageColNum){
    $minPageNum = $nowPage - 4;
    $maxPageNum = $nowPage;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif( $nowPage == ($totalPage-1) && $totalPage > $pageColNum){
    $minPageNum = $nowPage - 3;
    $maxPageNum = $nowPage + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif( $nowPage == 2 && $totalPage > $pageColNum){
    $minPageNum = $nowPage - 1;
    $maxPageNum = $nowPage + 3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif( $nowPage == 1 && $totalPage > $pageColNum){
    $minPageNum = $nowPage;
    $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPage < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPage;
  // それ以外は左に２個出す。
  }else{
    $minPageNum = $nowPage - 2;
    $maxPageNum = $nowPage + 2;
  }
  

  echo '<div class="pagination_wrap">';
  echo '<div class="pagination_list">';
    echo '<ul>';
      if($nowPage != 1){
        echo '<li><a href="?p=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="';
        if($nowPage == $i ){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      if($nowPage != $maxPageNum && $maxPageNum > 1){
        echo '<li><a href="?p='.$totalPage.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
  echo '</div>';
}
?>

<?php
$siteTitle = 'トップページ';
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
        
        <div class="menu_wrap"><!-- メニュー用 -->
          <div class="seach_wrap" style="text-align:right">
            <p style="color:white">
              <?php if($rst['total'] == 0) : ?>
                0件表示
              <?php elseif($rst['total'] == 1): ?>
                1件表示
              <?php else: ?>
              <?php echo $minPage + 1 ?>〜
              <?php if($nowPage == $rst['total_page']) : ?>
              <?php echo $rst['total'] ?>件表示
              <?php else: ?>
              <?php echo $listSpan*$nowPage ?>件表示
              <?php endif; ?>
              <?php endif; ?>(計:<?php echo $rst['total'] ?>件)
            </p>
          </div>
          

          <form action="" method="get"><!-- ソート用フォーム -->
            <div class="seach_wrap"><!-- アルバム・アーティスト検索 -->
              <div class="search">
                <input type="text" class="searchTerm" name="ws" placeholder="アルバム・アーティスト検索" value="<?php if(!empty($_GET['ws'])) echo $_GET['ws'] ?>">
                <button type="submit" class="searchButton">
                  <i class="fa fa-search"></i>
                </button>
              </div>
            </div>

            <div class="seach_wrap"><!-- リリース年検索 -->
              <div class="cp_ipselect cp_sl02">
                <select name="ry">
                  <option value="">リリース年検索</option>
                  <option value="1990" <?php if(!empty($_GET['ry']) && $_GET['ry'] == 1990) echo 'selected' ?>>1990年</option>
                  <option value="1991" <?php if(!empty($_GET['ry']) && $_GET['ry'] == 1991) echo 'selected' ?>>1991年</option>
                  <option value="1992" <?php if(!empty($_GET['ry']) && $_GET['ry'] == 1992) echo 'selected' ?>>1992年</option>
                  <option value="1993" <?php if(!empty($_GET['ry']) && $_GET['ry'] == 1993) echo 'selected' ?>>1993年</option>
                  <option value="1994" <?php if(!empty($_GET['ry']) && $_GET['ry'] == 1994) echo 'selected' ?>>1994年</option>
                  <option value="1995" <?php if(!empty($_GET['ry']) && $_GET['ry'] == 1995) echo 'selected' ?>>1995年</option>
                  <option value="1996" <?php if(!empty($_GET['ry']) && $_GET['ry'] == 1996) echo 'selected' ?>>1996年</option>
                  <option value="1997" <?php if(!empty($_GET['ry']) && $_GET['ry'] == 1997) echo 'selected' ?>>1997年</option>
                  <option value="1998" <?php if(!empty($_GET['ry']) && $_GET['ry'] == 1998) echo 'selected' ?>>1998年</option>
                  <option value="1999" <?php if(!empty($_GET['ry']) && $_GET['ry'] == 1999) echo 'selected' ?>>1999年</option>
                  <option value="2000" <?php if(!empty($_GET['ry']) && $_GET['ry'] == 2000) echo 'selected' ?>>2000年以降</option>
                </select>
              </div>
            </div>
          </form>
        </div><!-- menu_wrap -->
      </div><!-- main_menu -->



      <div class="main_article"><!-- アルバム一覧表示 -->
      
      <div class="top_wrap flex">
        <?php if($rst['total'] == 0) :?>
          Not found
        <?php endif; ?>
          <?php foreach($rst['data'] as $key => $val) : ?>
            <?php if($val['years'] === '2000') {
              $years = $val['years'] . '年以降';
            } else {
              $years = $val['years'] . '年';
            }
            ?>
          <div class="top_article">
            <a href="detail.php?p=<?php echo $nowPage ?>&d=<?php echo $val['id'] . $link ?>"><img src="<?php echo $val['pic'] ?>" width="250" height="250" class="hover"></a><br>
            <p><b><?php echo $val['details_title'] ?></b></p>
            <p>(<?php echo $val['artist'] ?>)</p>
            <p><?php echo $years ?></p>
            
          </div>
          <?php endforeach; ?>
        </div>

        <?php pagination($nowPage, $rst['total_page'], $link); ?>
      
      </div><!-- main_article -->


    </div><!-- flex -->
  </div><!-- main_wrap -->
</main>



<?php
require('footer.php');
?>