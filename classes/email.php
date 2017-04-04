<?php
require_once 'PHPMailer/class.phpmailer.php';
require_once 'PHPMailer/class.smtp.php';

function send_text_email($emailFromName, $emailFrom, $emailToName, $emailTo, $subject, $text, $emailCCName, $emailCC, $attachment)
{
    $mail = new PHPMailer();
    $mail->CharSet  = 'UTF-8';
    $mail->Timeout  = 15;
    $mail->Host     = 'mail.greenpeace.ru';
    $mail->From     = $emailFrom;

    $mail->isSMTP();
    $mail->FromName = $emailFromName;
    $mail->Subject  = $subject;
    $mail->Body     = $text;

    $mail->addAddress($emailTo, $emailToName);
    if ($emailCC) $mail->addCC($emailCC, $emailCCName);

    if ($attachment) $mail->addAttachment($attachment);

    if(!$mail->send()){
        throw new RuntimeException('failed to send message via PHPMailer: '.$mail->ErrorInfo);
    }
}
