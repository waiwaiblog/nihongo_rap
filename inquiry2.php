<?php

require('function.php');

// レスポンスデータを定義します。
$res = array('is_success' => false);

// 各POSTデータを取り出します。
$reviews_title = array_key_exists('reviews_title', $_POST) ? trim($_POST['reviews_title']) : '';
$star = array_key_exists('star', $_POST) ? trim($_POST['star']) : '';
$reviews_comment = array_key_exists('reviews_comment', $_POST) ? trim($_POST['reviews_comment']) : '';
$from_users_id = array_key_exists('from_users_id', $_POST) ? trim($_POST['from_users_id']) : '';
$details_id = array_key_exists('details_id', $_POST) ? trim($_POST['details_id']) : '';

// エラーメッセージを格納する配列を定義します。
$errors = array();


if ($reviews_title === '') {
    $errors[] = array('classname' =>  'reviews_title', 'message' => 'レビューのタイトルを入力してください。');
}
if ($star === '') {
    $errors[] = array('classname' => 'star', 'message' => '星を選択してください。');
}
if ($reviews_comment === '') {
    $errors[] = array('classname' =>  'reviews_comment', 'message'  => 'レビュー内容を入力してください。');
}
if (count($errors) == 0) {

    if (mb_strlen($reviews_title) < 3) {
        $errors[] = array('classname' =>  'reviews_title', 'message' => 'レビューのタイトルは3文字以上で入力してください。');
    }
    if (mb_strlen($reviews_title) > 30) {
        $errors[] = array('classname' =>  'reviews_title', 'message' => 'レビューのタイトルは30文字以内で入力してください。');
    }
    if (mb_strlen($reviews_comment) < 6) {
        $errors[] = array('classname' =>  'reviews_comment', 'message' => 'レビュー内容は6文字以上で入力してください。');
    }

    if (count($errors) == 0) {
    // エラーが無い場合
    
        debug('えらーなし');
        try {
            $dbh = dbConnect();
            $sql = 'INSERT INTO reviews SET 
            reviews_title=:reviews_title, 
            star=:star, 
            reviews_comment=:reviews_comment, 
            from_users_id=:from_users_id, 
            details_id=:details_id, 
            send_date=:send_date, 
            create_date=:create_date';
            $data = array(
                ':reviews_title' => $reviews_title,
                ':star' => $star,
                ':reviews_comment' => $reviews_comment,
                ':from_users_id' => $from_users_id,
                ':details_id' => $details_id,
                ':send_date' => date('Y/m/d'),
                ':create_date' => date('Y-m-d H:i:s'),
            );
            $stmt = queryPost($dbh, $sql, $data);
            if($stmt) {
                debug('成功');
            } else {
                debug('失敗');
            }
            } catch(PDOException $e) {
            error_log('SQLエラー:' . $e->getMessage());
            }
        
            $res['is_success'] = true;
    } else {
        debug('エラー発生');
        // エラーがある場合は、レスポンスデータに追加します。
        $res['errors'] = $errors;

    }
} else {

    debug('エラー発生');
    // エラーがある場合は、レスポンスデータに追加します。
    $res['errors'] = $errors;
    }


header("Content-Type: application/json; charset=utf-8");
echo json_encode($res);

?>