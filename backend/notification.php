<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Instancie o objeto PHPMailer
$mail = new PHPMailer(true);

try {
    // Configurações do servidor SMTP externo
    $mail->isSMTP();
    $mail->Host       = 'smtp.seu-servidor-smtp.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'seu-email@dominio.com';
    $mail->Password   = 'sua-senha-do-email';
    $mail->SMTPSecure = 'tls';  // Use 'tls' ou 'ssl' dependendo das configurações do seu servidor
    $mail->Port       = 587;    // Use a porta apropriada: 587 para TLS, 465 para SSL

    // Configurações adicionais
    $mail->setFrom('seu-email@dominio.com', 'Seu Nome');
    $mail->addAddress('destinatario@dominio.com', 'Nome do Destinatário');

    // Conteúdo do e-mail
    $mail->isHTML(true);
    $mail->Subject = 'Assunto do E-mail';
    $mail->Body    = 'Conteúdo do E-mail';

    // Envia o e-mail
    $mail->send();
    echo 'E-mail enviado com sucesso!';
} catch (Exception $e) {
    echo 'Erro ao enviar o e-mail: ', $mail->ErrorInfo;
}
?>
