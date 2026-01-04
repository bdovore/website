<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function bdovoreMail($to, $subject, $message, $headers = null)
{
    $mail = new PHPMailer(true);

    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = 'base64';

    try {
        /* ========================
         * CONFIG SMTP OVH
         * ======================== */
        $mail->isSMTP();
        $mail->Host       = 'ssl0.ovh.net';
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_ADRESS;
        $mail->Password   = EMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;


        /* ========================
         * HEADERS COMPAT mail()
         * ======================== */
        $fromEmail = EMAIL_ADRESS;
        $fromName  = 'BDovore';

        $mail->setFrom($fromEmail, $fromName);
        $mail->addReplyTo($fromEmail, $fromName);
        $mail->Sender = $fromEmail;

        /*
        if ($headers) {
            if (is_array($headers)) {
                foreach ($headers as $h) {
                    parseHeader($mail, $h, $fromEmail, $fromName);
                }
            } else {
                $lines = explode("\n", $headers);
                foreach ($lines as $line) {
                    parseHeader($mail, trim($line), $fromEmail, $fromName);
                }
            }
        } */


        /* ========================
         * DESTINATAIRES
         * ======================== */
        if (is_array($to)) {
            foreach ($to as $addr) {
                $mail->addAddress($addr);
            }
        } else {
            $mail->addAddress($to);
        }

        /* ========================
         * CONTENU
         * ======================== */
        $mail->Subject = $subject;

        if (strip_tags($message) !== $message) {
            $mail->isHTML(true);
            $mail->Body    = $message;
            $mail->AltBody = html_entity_decode(strip_tags($message));
        } else {
            $mail->isHTML(false);
            $mail->Body = $message;
        }

        return $mail->send();

    } catch (Exception $e) {
        error_log('[bdovoreMail] ' . $mail->ErrorInfo);
        return false;
    }
}

/* ========================
 * Parser headers mail()
 * ======================== */
function parseHeader(PHPMailer $mail, $line, &$fromEmail, &$fromName)
{
    if (stripos($line, 'from:') === 0) {
        $value = trim(substr($line, 5));
        if (preg_match('/(.*)<(.+)>/', $value, $m)) {
            $fromName  = trim($m[1], "\" ");
            $fromEmail = trim($m[2]);
        } else {
            $fromEmail = $value;
        }
    }

    if (stripos($line, 'reply-to:') === 0) {
        $mail->addReplyTo(trim(substr($line, 9)));
    }

    if (stripos($line, 'cc:') === 0) {
        $mail->addCC(trim(substr($line, 3)));
    }

    if (stripos($line, 'bcc:') === 0) {
        $mail->addBCC(trim(substr($line, 4)));
    }
}
