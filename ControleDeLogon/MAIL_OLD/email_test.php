<?php
require_once('phpmailer\PHPMailerAutoload.php');
require_once('conf.php');

$mailUser = base64_decode($mailAcc);
$mailPassword = base64_decode($mailPwd);
$mail = new PHPMailer;
$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'svdf07w000014.call.br';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = $mailUser.'@call.br';                 // SMTP username
$mail->Password = $mailPassword;                        // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;                                    // TCP port to connect to

$mail->setFrom('jornada@call.inf.br');
$mail->addAddress('diego.souza@call.inf.br');     // Add a recipient
//$mail->addAddress('ellen@example.com');               // Name is optional
//$mail->addReplyTo('info@example.com', 'Information');
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');

//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = 'Teste de envio via PHP';
$mail->Body    = '<p>Teste de envio de e-mail.</p><p>Teste usando HTML <b>EMAIL</b>.</p>';
//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

if(!$mail->send()) {
    echo "<script type='text/javascript'>";
	echo "alert('Houve um erro ao enviar o erro.\\nPor favor informe o seguinte erro ao administrador do sistema:\\n".$mail->ErrorInfo."');";
	echo "window.location = 'index.php';";
	echo "</script>";
} else {
	echo "<script type='text/javascript'>";
	echo "alert('O erro foi reportado com sucesso.');";
	echo "window.location = 'index.php';";
	echo "</script>";
}
?>