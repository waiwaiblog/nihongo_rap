<?php

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  require 'PHPMailer/src/Exception.php';
  require 'PHPMailer/src/PHPMailer.php';
  require 'PHPMailer/src/SMTP.php';



  class MailTest
  {
    public function sendMail($sub, $message, $receive)
    {
      mb_language("japanese");
      mb_internal_encoding("UTF-8");

      $mail = new PHPMailer();
      $mail->isSMTP();
      $mail->Encoding = "7bit";
      $mail->CharSet = '"UTF-8"';

      $mail->Host = 'smtp.gmail.com';
      $mail->Port = 587;
      $mail->SMTPAuth = true;
      $mail->SMTPSecure = "tls";
      $mail->Username = 'yukazo.yukizo@gmail.com';
      $mail->Password = 'xzkzgkzivvryfwva';
      // ※１は、２段階認証有効なら「アプリ固有のパスワード」を生成してそれをいれる
      // 無効なら「安全性の低いアプリからのアクセスを許可」しておく
      $mail->From = 'yukazo.yukizo@gmail.com';
      $mail->FromName = mb_encode_mimeheader("TOYREUSE info", "ISO-2022-JP", "UTF-8"); // "表示名" <メールアドレス>
      $mail->Subject = mb_encode_mimeheader($sub, "ISO-2022-JP", "UTF-8");
      $mail->Body = mb_convert_encoding($message, "UTF-8", "auto");
      $mail->AddAddress($receive);

      if (!$mail->send()) {
        debug("送信エラー " . $mail->ErrorInfo);
      } else {
        debug('送信しました');
      }

    }

  }