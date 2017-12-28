
	<?php
		require_once("restrict.php");
		require_once("ldapConnection.php");
		require_once("getDate.php");
		require_once("userInfo.php");
		require_once('phpmailer\PHPMailerAutoload.php');
		require_once('conf.php');
		
		date_default_timezone_set("America/Sao_Paulo");
		
		$msgType = $_POST["to_opt"];
		$recipients = $_POST["to_recipients"];
		$sender = $_POST["to_sender"];
		$database = $_POST["to_database"];
		if(isset($_POST["to_mail"])){
			$sendMail = $_POST["to_mail"];
		} else {
			$sendMail = "NO MAIL";
		}
		
		//$msgpath = "//call.br/servicos/LOGS/LogsForceLogoff/Jornada/MSG";
		$msgpath = "//call.br/servicos/LOGS/LogsMessages/SCL";
		$fileCounter = 0;
		
		
		//Parte visível da mensagem==================
		$viewSender = $_POST["sender"];
		$viewRecipients = $_POST["recipient"];
		$subtitle = $_POST["subtitle"];
		$textbody = $_POST["textBody"];
		$subtitle = utf8_encode($subtitle);
		//Construíndo mensagem=======================
		$filename = $sender;
		$msgContent = 	"			<div align=\"center\" style=\"margin-left:50px; margin-right:50px; margin-top:50px; width:80%; font-family: arial,tahoma,verdana,sans-serif;\">\r\n" .
						"				<div align=\"left\">\r\n" .
						"					<div><h2>" . $subtitle . "</h2></div>\r\n" .
						"					<div>Em: "  . $dias . "/" . $meses . "/" . $anos . " (" . $horas . ":" . $minutos . ")" . "</div>\r\n" .
						"					<div>De: " . $viewSender . "</div>\r\n" .
						"					<div>Para: " . $viewRecipients . "</div>\r\n" .
						"					<hr />\r\n" .
						"					<div><i>N&atilde;o responder esta mensagem.</i></div>\r\n" .
						"					<div style=\"background-color: #f0f0f0; border-radius: 5px; border: 2px solid #e0e0e0; padding-bottom: 10px; padding-top: -20px; margin-top: 20px;\">\r\n" .
						"						<div style=\"padding:10px;\">\r\n".
						"							" .	$textbody. "\r\n" .
						"						</div>\r\n" .
						"						<div style=\"padding:10px; border:2px solid #e0e0e0; border-radius:5px; box-shadow:0px 2px 5px 0px #888; background-color: #f8f8f8;\">\r\n" .
						"							<p><i>Atenciosamente,</i></p>\r\n" .
						"							<p><b>" . $viewSender . "</b></p>\r\n";
							
						
		//===========================================
		$userList = array();
		$subtitle = utf8_decode($subtitle);
		if($msgType == 1){
			$filt = '(&(objectClass=User)(objectCategory=Person))';
		} 
		else if($msgType == 2){
			$filt = '(&(objectClass=User)(objectCategory=Person)(extensionAttribute4=' . $recipients . '))';
		}
		else if($msgType == 3){
			$filt = '(&(objectClass=User)(objectCategory=Person)(extensionAttribute4=' . $recipients . '))';
		} else {
			$filt = '(&(objectClass=User)(sAMAccountname=' . $recipients . '))';
		}
		
		$writeMsg = $msgpath . "/" . $filename . "_" . $anos . "-" . $meses . "-" . $dias . ".html";
		$atrMsg = $filename . "_" . $anos . "-" . $meses . "-" . $dias;
		while(file_exists($writeMsg)){
			$fileCounter++;
			$writeMsg = $msgpath . "/" . $filename . "_" . $anos . "-" . $meses . "-" . $dias . "_-_" . $fileCounter . ".html";
			$atrMsg = $filename . "_" . $anos . "-" . $meses . "-" . $dias . "_-_" . $fileCounter;
		}
		
		$acp = base64_decode($sysPwd);
		$acu = base64_decode($sysAcc);
		
		//Dados de conexão com serviço
		$ldapU = "call\\" . $acu;
		$ldapPw = $acp;
		if($lc){
			//Executa Binding de conta LDAP
			$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die ("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>N&atilde;o foi poss&iacute;vel conectar!<br /> Favor verificar seu usu&aacute;rio e senha e tente novamente.</strong><br /><br /><a href='.\index.php' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
		}
		if($ldapB){
			//Filtro para pesquisa LDAP ================================
			//Search
			$sr = ldap_search($lc, $database, $filt);
			//Organiza
			$sort = ldap_sort($lc, $sr, 'name');
			//Recolhe entradas
			$info = ldap_get_entries($lc, $sr);
			//==========================================================
		}
		for ($i = 0; $i < $info["count"]; $i++) {
			$sendToMail = false;
			$msgCount = 0;
			if(isset($info[$i]["mail"][0])){
				$sendToMail = true;
				$destinationMail = $info[$i]["mail"][0];
			}
			$destinationName = $info[$i]["cn"][0];
			$destinationDn = $info[$i]["distinguishedname"][0];
			
			if($sendToMail && $sendMail == "mail"){
				$mailUser = base64_decode($mailAcc);
				$mailPassword = base64_decode($mailPwd);
				$mailContent = 	$msgContent . 
								"							<img src=\"cid:logo_id\" style=\"width:100px;\" />\r\n".
								"						</div>\r\n".
								"					</div>\r\n".
								"				</div>\r\n".
								"			</div>\r\n";
				$mail = new PHPMailer;
				$mail->SMTPDebug = 0;                               // Enable verbose debug output
				
				$mail->isSMTP();                                    // Set mailer to use SMTP
				$mail->Host = 'svdf07w000014.call.br';  			// Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                             // Enable SMTP authentication
				$mail->Username = $mailUser.'@call.br';             // SMTP username
				$mail->Password = $mailPassword;                    // SMTP password
				$mail->SMTPSecure = 'tls';                          // Enable TLS encryption, `ssl` also accepted
				$mail->Port = 587;                                  // TCP port to connect to

				$mail->setFrom('scl@call.inf.br');
				$mail->addAddress($destinationMail);     			// Add a recipient
				
				//$mail->AddEmbeddedImage('//call.br/servicos/LOGS/LogsForceLogoff/Jornada/MSG/img/logo.png', 'logo_id');
				$mail->AddEmbeddedImage('//call.br/servicos/LOGS/LogsMessages/SCL/img/logo.png', 'logo_id');
				$mail->isHTML(true);                                // Set email format to HTML
				$mail->Subject = $subtitle;
				$mail->Body = utf8_decode($mailContent);
				if(!$mail->send()) {
					//Do nothing...
				}
			}
			if(isset($info[$i]["samaccountname"][0])){
				array_push($userList, $info[$i]["samaccountname"][0]);
			}
			//Se o colaborador não possuir email:
			if(isset($info[$i]["extensionattribute13"][0])){
				if($info[$i]["extensionattribute13"][0] != "0"){
					$splitAtr = explode("|", $info[$i]["extensionattribute13"][0], 2);
					$msgCount = ((Int)$splitAtr[0] + 1);
					$dado["extensionattribute13"] = $msgCount . "|" . $splitAtr[1] . "|" . $atrMsg;
				} else {
					$msgCount = "1";
					$dado["extensionattribute13"] = $msgCount . "|" . $atrMsg;
				}
			} else {
				$dado["extensionattribute13"] = "1|" . $atrMsg;
			}
			//Grava atributos no AD
			$ldapC = ldap_mod_replace($lc, $destinationDn, $dado) or die (header("Location:home.php?filter=*&erro=12"));
				
			
			
		}
		$msgContent =	$msgContent . 
						"							<img src=\"http://virit.call.br/messages/images/logo.png\" style=\"width:100px;\" />\r\n".
						"						</div>\r\n".
						"					</div>\r\n".
						"				</div>\r\n".
						"			</div>\r\n";
		$msgContent = utf8_decode($msgContent);
		//Escreve mensagem
		file_put_contents($writeMsg, $msgContent, FILE_APPEND);
		ldap_close($lc);
		$writeMsg = "//call.br/servicos/LOGS/LogsMessages/SCL/inf/" . $_SESSION['matricula'] . ".inf";
		file_put_contents($writeMsg, "@#" . $atrMsg, FILE_APPEND);
		foreach($userList as $recip){
			file_put_contents($writeMsg, "#0|" . $recip, FILE_APPEND);
		}
		file_put_contents($writeMsg, "#" . count($userList) . "|total", FILE_APPEND);
		header("Location:mensagens.php?result=16");
		
	?>



