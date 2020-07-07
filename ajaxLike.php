<?php

require 'function.php';


debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　お気に入りajax通信ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


if(isset($_POST['detailId']) && isLogin()) {
  debug('POST通信があります。');
  $d_id = $_POST['detailId'];
  debug('アルバムID:' . $d_id);

  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(*) AS cnt FROM favorites WHERE details_id = :d_id AND users_id = :u_id';
    $data = array(
      ':d_id' => $d_id,
      ':u_id' => $_SESSION['user_id'],
    );
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result['cnt'] > 0) {
      $sql = 'DELETE FROM favorites WHERE details_id = :d_id AND users_id = :u_id';
      $data = array(
        ':d_id' => $d_id,
        ':u_id' => $_SESSION['user_id'],
      );
      $stmt = queryPost($dbh, $sql, $data);
    } else {
      $sql = 'INSERT INTO favorites SET details_id = :d_id, users_id = :u_id, create_date = :c_date';
      $data = array(
        ':d_id' => $d_id,
        ':u_id' => $_SESSION['user_id'],
        'c_date' => date('Y-m-d H:i:s'),
      );
      $stmt = queryPost($dbh, $sql, $data);
    }

  } catch(PDOException $e) { 
    error_log('SQLエラー:' . $e->getMessage());
  }
}