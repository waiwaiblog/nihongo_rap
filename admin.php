<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　【管理者ページ】一覧ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();



if(!empty($_SESSION)) {
  if($_SESSION['user_id'] !== '1') {
    header('Location: login.php');
  }
} else {
  header('Location: login.php');
}


if(!empty($_POST)) {
  $id = $_POST['id'];
  try {
    $dbh = dbConnect();
    $sql = 'DELETE FROM applicate WHERE id=:id';
    $data = array(
      ':id' => $id,
    );
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt) {
      debug('削除成功');
    } else {
      debug('削除失敗');
    }
  
  } catch(PDOException $e) {
    error_log('SQLエラー:' . $e->getMessage());
  }
}


try {
  $dbh = dbConnect();
  // $sql = 'SELECT * FROM applicate';
  $sql = 'SELECT a.id AS applicate_id, a.artist, a.album, a.year, a.song_list, a.from_user, a.send_date, u.username, u.id AS userid FROM applicate AS a LEFT JOIN users AS u ON a.from_user = u.id WHERE a.flg = 0';
  $data = array();
  $stmt = queryPost($dbh, $sql, $data);
  $result = $stmt->fetchAll();

} catch(PDOException $e) {
  error_log('SQLエラー:' . $e->getMessage());
}



?>

<?php
$siteTitle = '管理画面';
require('head.php');
?>



<body>

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
            <?php putoutLink('admin.php','アルバム申請一覧'); ?>
            </li>
          </ul>
        </div>
      </div>

      <div class="main_article">
        <p style="text-align:right"><a href="admin_edit.php">投稿する</a>  <a href="logout.php">ログアウトする</a></style>
        <div class="main_title">
          管理者画面
        </div>

        <div class="admin_table pt-20">

          <table class="cp_table">
            <thead>
            <tr>
              <th>ID</th>
              <th>アルバム名</th>
              <th>アーティスト名</th>
              <th>送信日時</th>
              <th>送信者(ID)</th>
              <th>削除</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach($result as $key => $val) : ?>

              <tr>
              <td><a href="admin_edit.php?id=<?php echo $val['applicate_id'] ?>"><?php echo $val['applicate_id'] ?></a></td>
              <td><?php echo $val['album'] ?></td>
              <td><?php echo $val['artist'] ?></td>
              <td><?php echo $val['send_date'] ?></td>
              <td><?php echo $val['username'] ?>(<?php echo $val['userid'] ?>)</td>
              <td>
                <form action="" method="post">
                <button type="submit" name="id" value="<?php echo $val['applicate_id'] ?>">削除</button>
                </form>
              </td>
              </tr>

            
            <?php endforeach; ?>
            </tbody>
            </table>

        </div>
      </div>
    
    </div>

  </div>
</main>

<script src="js/jquery-2.2.2.min.js"></script>
  <script>
  $(function(){
    // メッセージ表示
    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
      $jsShowMsg.slideToggle('slow');
      setTimeout(function(){ $jsShowMsg.slideToggle('fast'); }, 2000);
    }
  });
  </script>
</body>
</html>