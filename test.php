<style>
img {
  border-radius: 50%;
  border: 3px solid gray;
}
</style>

<form>
  <label for="filename">
    <img src="noimage.png" class="preview" width='100' height='100' for="filename">
    <input type="file" name="filename" accept="image/*">
  </label>
</form>

<br><br>


<script src="js/jquery-2.2.2.min.js"></script>
<script>
  $(function(){
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