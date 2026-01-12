<?php
// mailer.php - wrapper that uses PHPMailer if available, otherwise falls back to mail()
$cfg = require __DIR__ . '/mailer_config.php';

function send_mail($to, $subject, $body){
  global $cfg;

  // If configured to use SMTP and PHPMailer is available
  if($cfg['use_smtp']){
    if(file_exists(__DIR__ . '/../vendor/autoload.php')){
      require_once __DIR__ . '/../vendor/autoload.php';
      $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
      try {
        $mail->isSMTP();
        $mail->Host = $cfg['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $cfg['smtp_user'];
        $mail->Password = $cfg['smtp_pass'];
        $mail->SMTPSecure = $cfg['smtp_secure'];
        $mail->Port = $cfg['smtp_port'];

        $mail->setFrom($cfg['from_email'], $cfg['from_name']);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML(false);
        $mail->CharSet = 'UTF-8';

        return $mail->send();
      } catch (Exception $e) {
        // fallback to mail()
      }
    }
  }

  // fallback to PHP mail()
  $headers = "From: " . ($cfg['from_email'] ?? 'no-reply@localhost') . "\r\n" .
             "Content-Type: text/plain; charset=utf-8\r\n";
  return @mail($to, $subject, $body, $headers);
}
