<?php

$p = $_GET['a'];

// var_dump($_GET);

function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(!in_array($key,$arr_del_key,true)){ //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}
//消したいGETパラメータを指定する
//$_GETがある時、
//消したいパラメータ（配列になっている）と同じGETパラメータのじゃない場合に、
//消したいGETパラメータじゃない方に、そのキーと、中身を$strに追記する。
// 最後に&を消す処理をして完成。

//ex. $_GET['d'] = 2　と　$_GET['p'] = 1　が入っているとする。
//    $_GET['p']を消したい場合に、それをappendGetParamの引数（pと書く）に指定する。
//    $_GET自体がないとできないので、条件文に$_GETがある場合として、
//    foreachで$_GETの中身を１つずつ取り出す。
//    その中でin_array(第１引数が第２引数（配列）内にあるかどうかをboolする)で条件を指定
//    !in_arrayなので合致しない場合に下の処理が続く。
//    $strに合致しない場合のみ、そのキーと中身の間に=と後ろに&を付与する。
//    つまり、消したくない場合はそれが残る。
//    消したくないものが何個かあれば、その都度$strに付与され、最後にmb_substrで&を取ってそれをreturnする



?>

<form method="get" action="">

<select name="a">
  <option value="3" <?php if(!empty($_GET['a']) && $_GET['a'] == 3) echo 'selected' ?>>a
  </option>
  <option value="4" <?php if(!empty($_GET['a']) && $_GET['a'] == 4) echo 'selected' ?>>b
  </option>
  <option value="5" <?php if(!empty($_GET['a']) && $_GET['a'] == 5) echo 'selected' ?>>c
  </option>
  </option>

</select>
    <button type="submit">
      送信
    </button>
</form>


<?php if(empty($p)) echo $p ?>