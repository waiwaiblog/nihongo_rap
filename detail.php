<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　アルバム詳細ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


// var_dump($_GET);
// require('auth.php');


if(!empty($_SESSION['user_id'])) {
  $userData = getUser($_SESSION['user_id']);
} else {
  $userData = '';
}


$nowPage = (!empty($_GET['r'])) ? $_GET['r'] : 1;
$detail_num = $_GET['d'];
$page_num = (!empty($_GET['p'])) ? $_GET['p'] : 1;

$link = '';
if(!empty($_GET['ry'])) {
  $link .= '&ry=' . $_GET['ry'];
}
if(!empty($_GET['ws'])) {
  $link .= '&ws=' . $_GET['ws'];
}

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


// ---------------------GET['d']をいじくられないためのコード（ただし、別IDで存在しているページには飛ぶので要改良）
if(!empty($_GET['d'])) {
  if($_GET['d'] !== $result['id']) {
    header('Location: index.php');
  }
}
//------------




//レビュー表示件数
$listSpan = 5;

//表示レコード先頭を算出 1ページ目なら0から。2ページ目なら10から。
$minPage = ($nowPage-1) * $listSpan;

function displayReview($minPage, $listSpan = 5) {
  global $detail_num;
  $dbh = dbConnect();
  $sql = 'SELECT count(id) FROM reviews WHERE details_id = :details_id AND delete_flg = 0';
  $data = array(
    ':details_id' => $detail_num,
  );
  $stmt = queryPost($dbh, $sql, $data);
  $disp['total'] = $stmt->fetchColumn();
  $disp['total_page'] = ceil($disp['total'] / $listSpan); //総レコード数÷ページあたりの表示数で総ページ数を算出する
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
  $disp['data'] = $stmt->fetchAll();
  return $disp;
}

function getReview($num, $minPage, $listSpan = 5) {
  try {
    $dbh = dbConnect();
    $sql = 'SELECT r.id, r.reviews_title, r.star, r.reviews_comment, r.send_date, u.username, u.pic FROM reviews AS r LEFT JOIN users AS u ON r.from_users_id = u.id WHERE r.details_id=? ORDER BY r.create_date DESC LIMIT ? OFFSET ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $num, PDO::PARAM_STR);
    $stmt->bindValue(2, $listSpan, PDO::PARAM_INT);
    $stmt->bindValue(3, $minPage, PDO::PARAM_INT);
    $stmt->execute();

    if($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch(PDOException $e) {
    error_log('SQLエラー発生');
  }
}

$reviewData = getReview($detail_num, $minPage, $listSpan);

function displayAlbum($minPage, $listSpan = 10) {
  $dbh = dbConnect();
  $sql = 'SELECT count(id) FROM details  WHERE delete_flg = 0';
  $data = array();
  $stmt = queryPost($dbh, $sql, $data);
  $rst['total'] = $stmt->fetchColumn();
  $rst['total_page'] = ceil($rst['total'] / $listSpan); //総レコード数÷ページあたりの表示数で総ページ数を算出する
  if(!$stmt) {
    return false;
  }

  $sql = "SELECT * FROM details WHERE delete_flg = 0";
  $sql .= " ORDER BY id DESC";
  $sql .= " LIMIT ? OFFSET ?";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $listSpan, PDO::PARAM_INT);
  $stmt->bindValue(2, $minPage, PDO::PARAM_INT);
  $stmt->execute();
  $rst['data'] = $stmt->fetchAll();
  return $rst;
}

$rst = displayAlbum($minPage, $listSpan);
$disp = displayReview($minPage, $listSpan);


function pagination($nowPage, $totalPage, $link = '', $pageColNum = 5){
  global $page_num;
  global $detail_num;
  global $link;
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
        echo '<li><a href="?p='.$page_num. '&d='. $detail_num . '&r=1' . $link . '#review_container">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="';
        if($nowPage == $i ){ echo 'active'; }
        echo '"><a href="?p='.$page_num. '&d='. $detail_num . '&r=' . $i . $link . '#review_container">'.$i.'</a></li>';
      }
      if($nowPage != $maxPageNum && $maxPageNum > 1){
        echo '<li><a href="?p='.$page_num. '&d='. $detail_num . '&r=' .$totalPage . $link . '#review_container">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
  echo '</div>';
}

// if(!empty($_POST)) {
//   $reviews_title = $_POST['reviews_title'];
//   $star = $_POST['star'];
//   $reviews_comment = $_POST['reviews_comment'];
  
//   validRequired($reviews_title, 'reviews_title');
//   validRequired($star, 'star');
//   validRequired($reviews_comment, 'reviews_comment');

//   if(empty($err_msg)) {
//     validMaxLen($reviews_title, 'reviews_title');
//     validMaxLen($reviews_comment, 'reviews_comment');
//     validMinLen($reviews_comment, 'reviews_comment');

//     if(empty($err_msg)) {

//       try {
//         $dbh = dbConnect();
//         $sql = 'INSERT INTO reviews SET reviews_title=:reviews_title, star=:star, reviews_comment=:reviews_comment, from_users_id=:from_users_id, details_id=:details_id, send_date=:send_date, create_date=:create_date';
//         $data = array(
//           ':reviews_title' => $reviews_title,
//           ':star' => $star,
//           ':reviews_comment' => $reviews_comment,
//           ':from_users_id' => $_SESSION['user_id'],
//           ':details_id' => $detail_num,
//           ':send_date' => date('Y-m-d H:i:s'),
//           ':create_date' => date('Y-m-d H:i:s'),
//         );
//         $stmt = queryPost($dbh, $sql, $data);
//         if($stmt) {
//           debug('成功');

//           $_SESSION['msg_success'] = '投稿しました。';
//           return;
//         } else {
//           debug('失敗');
//         }
//       } catch(PDOException $e) {
//         error_log('SQLエラー:' . $e->getMessage());
//       }
//     }
//   }
// }
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
  <div class="backFav">

    <!------------------------------------index.phpからきたか、mypage.phpからきたかで戻るリンクを変更する -->
    <?php $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null ?>
    <?php $referer = basename($referer) ?>
    <!-- mypage.phpはGETを含むと変わってしまうので、正規表現で〜からはじまるとしてpreg_matchさせる -->
    <?php if(preg_match("/^mypage.php/", $referer)) : ?>
      <a href="mypage.php"><p class="left"><i class="fa fa-angle-left fa-lg fa-fw" aria-hidden="true" id="backward"></i>戻る</p></a>
    <?php else: ?>
    <a href="<?php echo 'index.php' .appendGetParam(['d','r']) ?>"><p class="left"><i class="fa fa-angle-left fa-lg fa-fw" aria-hidden="true" id="backward"></i>戻る</p></a>
    <?php endif; ?>
    <!-------------------------------------ログインしていたらお気に入りマークを表示 -->
    <?php if(isLogin()) : ?>
    <p class="right">
      <i class="js-favorites fa fa-heart fa-lg fa-fw <?php if(isLike($_SESSION['user_id'], $detail_num)) { echo 'active';} ?>" aria-hidden="true" id="heart" data-detailid="<?php echo $detail_num ?>"></i>
    </p>
    <?php endif; ?>
  </div>
  <div class="main_wrap" style="margin-top: 5px">
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
      <div class="review_container" id="review_container">


      
        <h2><?php echo $result['details_title'] ?> についてレビュー</h2>
        <p class="center pt-10"><?php if(!empty($_SESSION['user_id']) && isLogin()): ?>
        <a class="js-modal-open" href="">コメントを投稿する</a>
        <?php else: ?>
        <a href="login.php">ログイン</a> ( <a href="signup.php">新規登録</a> ) すればレビュー投稿できます。
        <?php endif; ?></p>
        
        <div class="review">
            <?php if(empty($reviewData)) : ?>
              投稿がありません
            <?php else : ?>
            <?php foreach($reviewData as $key => $val) : ?>
            <div class="review_box1">
              <div>
                <?php if(($val['pic']) === '') : ?>
                  <img src="noimage.png" class="review_profimg">
                  <?php else : ?>
                    <img src="<?php echo $val['pic'] ?>" class="review_profimg">
                <?php endif; ?>
              </div>
              <div style="align-self: center">
                <?php echo $val['username'] ?>
              </div>
            </div>

            <div class="review_box2">
              <div>
                <?php if($val['star'] === '5'): ?>
                  <img src="review_star_5_an.gif">
                <?php elseif($val['star'] === '4'): ?>
                  <img src="review_star_4.gif">
                <?php elseif($val['star'] === '3'): ?>
                  <img src="review_star_3.gif">
                <?php elseif($val['star'] === '2'): ?>
                  <img src="review_star_2.gif">
                <?php elseif($val['star'] === '1'): ?>
                  <img src="review_star_1.gif">
                <?php endif; ?>
              </div>
              <div>
                <b><?php echo $val['reviews_title'] ?></b>
              </div>
            </div>

            <div class="review_box3">
              <div>
                <font color="gray"><?php echo $val['send_date'] ?>にレビュー</font>
              </div>
              <div style="align-self: center">
                <?php if($val['send_date'] === date('Y-m-d')) : ?>
                  <img src="new.gif" width="32px" height="13px">
                <?php endif; ?>
              </div>
            </div>
            <div class="review_box4">
                <?php echo $val['reviews_comment'] ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php pagination($nowPage, $disp['total_page']); ?>
    </div>
  </div>
</main>









<footer>
  <div class="footer_wrap">
    ©️ 90年代の日本語ラップレビューする会
  </div>
  </footer>


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


      //画像ファイルプレビュー表示のイベント追加 fileを選択時に発火するイベントを登録
    $('form').on('change', 'input[type="file"]', function(e) {
      var file = e.target.files[0],
          reader = new FileReader(),
          $preview = $(".preview");
          t = this;

      // 画像ファイル以外の場合は何もしない
      if(file.type.indexOf("image") < 0){
        return false;
      }

      // ファイル読み込みが完了した際のイベント登録
      reader.onload = (function(file) {
        return function(e) {
          //既存のプレビューを削除
          $preview.empty();
          // .prevewの領域の中にロードした画像を表示するimageタグを追加
          $preview.attr({
                    src: e.target.result,
                    width: "100px",
                    height: "100px",
                    class: "preview",
                    title: file.name
                });
        };
      })(file);

      reader.readAsDataURL(file);
    });





    $(".custom-select").each(function() {
      var classes = $(this).attr("class"),
          id      = $(this).attr("id"),
          name    = $(this).attr("name");
      var template =  '<div class="' + classes + '">';
          template += '<span class="custom-select-trigger">' + $(this).attr("placeholder") + '</span>';
          template += '<div class="custom-options">';
          $(this).find("option").each(function() {
            template += '<span class="custom-option ' + $(this).attr("class") + '" data-value="' + $(this).attr("value") + '">' + $(this).html() + '</span>';
          });
      template += '</div></div>';
      
      $(this).wrap('<div class="custom-select-wrapper"></div>');
      $(this).hide();
      $(this).after(template);
    });
    $(".custom-option:first-of-type").hover(function() {
      $(this).parents(".custom-options").addClass("option-hover");
    }, function() {
      $(this).parents(".custom-options").removeClass("option-hover");
    });
    $(".custom-select-trigger").on("click", function() {
      $('html').one('click',function() {
        $(".custom-select").removeClass("opened");
      });
      $(this).parents(".custom-select").toggleClass("opened");
      event.stopPropagation();
    });
    $(".custom-option").on("click", function() {
      $(this).parents(".custom-select-wrapper").find("select").val($(this).data("value"));
      $(this).parents(".custom-options").find(".custom-option").removeClass("selection");
      $(this).addClass("selection");
      $(this).parents(".custom-select").removeClass("opened");
      $(this).parents(".custom-select").find(".custom-select-trigger").text($(this).text());
    });


  $('.js-modal-open').on('click',function(){
      $('.js-modal').fadeIn();
      $('form span.warning').remove();
      $('html, body').css('overflow', 'hidden');
      $('body').css('width','calc(100vw - 15px)');
      return false;
  });
  $('.js-modal-close').on('click',function(){
      $('.js-modal').fadeOut();
      $('body').removeAttr('style');
      $('html').removeAttr('style');
      return false;
  });




  $('form').submit(function() {
    // $('form p.error_message').remove();  // エラーメッセージをクリアします。
    $('form span.warning').remove();  // エラーメッセージをクリアします。
    var data = {};  // POSTデータを定義します。
    // 各要素（input[type="text"], textarea）でループします。
    $('form input, form select, form textarea').each(function() {
      // POSTデータを追加します。
      data[$(this).attr('class')] = $.trim($(this).val());
    });

    // Ajaxリクエストを投げます。
    $.ajax({
      url: './inquiry2.php',
      data: data,
      dataType: 'json',
      cache: false,
      type: 'POST',
      success: function(res) {
        if (res.is_success) {  // 入力エラーがなかった場合
          $('form input, form select, form textarea').val('');
          alert('送信されました。');
          $('.js-modal').fadeOut();
          $('body').removeAttr('style');
          $('html').removeAttr('style');
          $.ajax({
            url: "",
            context: document.body,
            success: function(s,x){
                $(this).html(s);
            }
          });
        } else {  // 入力エラーがあった場合
          var $target = null;  // スクロールさせるターゲットを定義します。
          $.each(res.errors, function(idx, error) {
            // エラーが発生した入力項目を取得します。
            var $elem = $('form .' + error.classname);
            var $elem2 = $elem.parent();
            console.log($elem2);

            // 入力項目の直前に、エラーメッセージを追加します。
            $elem2.before('<span class="warning">' + error.message + '</span>');
            // $elem.prev().addClass('error_message');
            // $elem.prev().append(error.message);

            if ($target == null || $target.offset().top > $elem.offset().top) {
              // スクロールのターゲットとなる入力項目を決定します。
              // エラーが複数存在した場合は、一番上の入力項目がターゲットになります。
              $target = $elem;
            }
          });

          if ($target != null) {
            $target.focus();  // フォーカスを当てます。

            // 入力項目を囲むdivまでスクロールさせます。
            $targetDiv = $target.closest('div');
            $('.modal').animate(
                {scrollTop: $targetDiv.offset().top}, 200, 'swing');
          }
        }
      }
    });

    return false;
  });



  var $like,
      likeDetailId;
  $like = $('.js-favorites') || null;
  likeDetailId = $like.data('detailid') || null;

  if(likeDetailId !== undefined && likeDetailId !== null) {
    $like.on('click', function() {
      var $this = $(this);
      $.ajax({
        type: 'POST',
        url: 'ajaxLike.php',
        data: { detailId : likeDetailId }
      }).done(function (data) {
        console.log('Ajax Success');

        $this.toggleClass('active');
      }).fail(function (msg) {
        console.log('Ajax Error');
      });


    });
  }
    

//   // #を含むアンカーをクリックした場合に処理
// $('a[href*=#]').click(function(){
// // スクロールの速度
// var speed = 400;// ミリ秒

// // アンカーの値取得
// var href= $(this).attr("href");

// // 移動先を取得
// var target = $(href == "#" || href == "" ? 'html' : href);

// // 移動先を数値で取得
// var position = target.offset().top;

// // スムーススクロール
// $($.support.safari ? 'body' : 'html').animate({scrollTop:position}, speed, 'swing');

// // URLにアンカーリンクを付加させない
// return false;
// });

});
</script>
</body>
</html>


<div class="modal js-modal">
    <div class="modal__bg js-modal-close"></div>
    <div class="modal__content">

    <h3>レビュー投稿する</h3><p class="modal-close"><a class="js-modal-close" href="">閉じる</a></p>

    <form action="" method="post">
        <!-- <div class="form_field">
          <p>タイトル</p>
          <input class="title" type="text" />
        </div> -->

      <label>ニックネーム 
        <div class="cp_iptxt">
          <p><?php echo $userData['username'] ?> さん</p>
          <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
        </div>
      </label>

      <label>レビューのタイトル
        <div class="cp_iptxt modalform">
          <input type="text" placeholder="タイトル" name="reviews_title" value="" class="reviews_title">
          <i class="fa fa-bookmark fa-lg fa-fw" aria-hidden="true"></i>
        </div>
      </label>

      <label>星の数(5点満点)
      <div class="cp_ipselect cp_sl02 modalform">
        <select name="star" id="star" class="star">
        <option value="" hidden>選択してください</option>
        <?php
          for($starnum = 5; $starnum >= 1; $starnum--) {
              echo '<option value="' . $starnum . '">' . '★×' . $starnum . '個' . '</option>';
          }
        ?>
        </select>
      </div>
    </label>

    <label>レビュー内容
      <div class="cp_iptxt modalform">
        <i class="fa fa-comment fa-lg fa-fw" aria-hidden="true"></i>
        <textarea placeholder="レビューよろしくお願いします。" name="reviews_comment" style="height: 300px;" id="reviews_comment" class="reviews_comment"></textarea>
      </div>
    </label>

    <input type="hidden" placeholder="" name="from_users_id" value="<?php echo $userData['id'] ?>" class="from_users_id">
    <input type="hidden" placeholder="" name="details_id" value="<?php echo $detail_num ?>" class="details_id">
        <!-- <div class="form_field">
          <p>星</p>
          <input class="star" type="text" />
        </div>
        <div class="form_field">
          <p>問い合わせ内容</p>
          <textarea class="message" rows="15" cols="40"></textarea>
        </div> -->
        <div class="button_field center">
          <button type="submit" name="button_submit" value="送信する" class="button">送信する</button>
        </div>
    </form>


  </div><!--modal__inner-->
</div><!--modal-->