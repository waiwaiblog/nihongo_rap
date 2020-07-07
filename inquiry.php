<?php
// レスポンスデータを定義します。
$res = array('is_success' => false);

// 各POSTデータを取り出します。
$name = array_key_exists('name', $_POST) ? trim($_POST['name']) : '';
$email = array_key_exists('email', $_POST) ? trim($_POST['email']) : '';
$tel = array_key_exists('tel', $_POST) ? trim($_POST['tel']) : '';
$message = array_key_exists('message', $_POST) ? trim($_POST['message']) : '';

// エラーメッセージを格納する配列を定義します。
$errors = array();

if ($name === '') {
    $errors[] = array('classname' => 'name', 'message' => '名前を入力してください。');
}
if ($email === '') {
    $errors[] = array('classname' =>  'email', 'message' => 'メールアドレスを入力してください。');
}
if ($tel === '') {
    $errors[] = array('classname' => 'tel', 'message' => '電話番号を入力してください。');
}
if ($message === '') {
    $errors[] = array('classname' =>  'message', 'message'  => 'お問い合わせ内容を入力してください。');
}

if (count($errors) == 0) {
    // エラーが無い場合
    $res['is_success'] = true;
} else {
    // エラーがある場合は、レスポンスデータに追加します。
    $res['errors'] = $errors;
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($res);

?>