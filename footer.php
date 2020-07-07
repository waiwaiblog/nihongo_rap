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
      $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;'});
    }

    // メッセージ表示
    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
      $jsShowMsg.slideToggle('slow');
      setTimeout(function(){ $jsShowMsg.slideToggle('fast'); }, 2000);
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





});
</script>
</body>
</html>