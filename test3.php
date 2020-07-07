<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script>
      $(function() {
  $('form').submit(function() {
    $('form p.error_message').remove();  // エラーメッセージをクリアします。

    var data = {};  // POSTデータを定義します。
    // 各要素（input[type="text"], textarea）でループします。
    $('form input, form textarea').each(function() {
      // POSTデータを追加します。
      data[$(this).attr('class')] = $.trim($(this).val());
    });
    console.log(data);

    // Ajaxリクエストを投げます。
    $.ajax({
      url: './inquiry.php',
      data: data,
      dataType: 'json',
      cache: false,
      type: 'POST',
      success: function(res) {
        if (res.is_success) {  // 入力エラーがなかった場合
          alert('THANKS!!');
        } else {  // 入力エラーがあった場合
          var $target = null;  // スクロールさせるターゲットを定義します。

          $.each(res.errors, function(idx, error) {
            // エラーが発生した入力項目を取得します。
            var $elem = $('form .' + error.classname);

            // 入力項目の直前に、エラーメッセージを追加します。
            $elem.before('<p class="error_message">' + error.message +  '</p>');

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
            $('body, html').animate(
                {scrollTop: $targetDiv.offset().top}, 200, 'swing');
          }
        }
      }
    });

    return false;
  });
});
    </script>



<style>
  body {
  margin: 20px 0 0 20px;
  width: 300px;
}
h1 {
  font-size: 20px;
}
form {
  margin-bottom: 40px;
}
.form_field {
  margin-bottom: 30px;
}
.form_field > p.error_message {
  color: #f00;
  font-weight: bold;
}
.button_field > button {
  width: 120px;
  height: 48px;
  font-size: 20px;
  cursor: pointer;
}
  </style>

  </head>
  <body>
    <h1>お問い合わせ（サンプル）</h1>
    <p>
      サンプルのお問い合わせフォームです。個人情報などの入力はご遠慮ください。
    </p>
    <form action="" method="post">
      <div class="form_field">
        <p>名前</p>
        <input class="name" type="text" />
      </div>
      <div class="form_field">
        <p>メールアドレス</p>
        <input class="email" type="email" />
      </div>
      <div class="form_field">
        <p>電話番号</p>
        <input class="tel" type="tel" />
      </div>
      <div class="form_field">
        <p>問い合わせ内容</p>
        <textarea class="message" rows="15" cols="40"></textarea>
      </div>
      <div class="button_field">
        <button type="submit">送信</button>
      </div>
    </form>
  </body>
</html>