<?php

$linkArray = array(
  'applicate_album.php',
  'prof_edit.php',
  'pass_edit.php',
  'contact.php',
  'withdraw.php'
);

$result_link = in_array(basename($_SERVER['SCRIPT_NAME']), $linkArray, true);


?>

<header>
    <div class="header_wrap">
      <div class="header_logo">
      <a href="index.php"><img src="top.png" width="427" height="100" alt="トップバナー" class="hover"></a>
      </div>

      <?php if(empty($_SESSION['user_id'])) : ?>
      <div class="header_list">
        <ul>
        <li>
        <?php putoutLink('index.php','トップページ'); ?>
        </li>
        <li>
        <?php putoutLink('login.php','ログイン'); ?>
        </li>
        <li>
        <?php putoutLink('signup.php','新規登録'); ?>
        </li>
        </ul>
      </div>
      
      <?php else : ?>
        
        <div class="header_list">
          <ul>
          <li>
          <?php putoutLink('index.php','トップページ'); ?>
          </li>
          <li>
          <?php if($result_link) : ?>
          <a href="mypage.php" class="active">マイページ</a>
          <?php else: ?>
          <?php putoutLink('mypage.php','マイページ'); ?>
          <?php endif; ?>
          </li>
          <li>
          <?php putoutLink('logout.php','ログアウト'); ?>
          </li>
          </ul>
        </div>


      <?php endif; ?>
    </div>
</header>