<!DOCTYPE html>
<html>
	<header>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</header>
	
	<body>

	<?php
		
		require_once("restrict.php");
		require_once("ldapConnection.php");
		require_once("getDate.php");
		require_once("conf.php");
				
		date_default_timezone_set("America/Sao_Paulo");
		
		$account = $_GET["account"];
		$message = $_GET["msg"];
		
		$message = (Int)$message;
		$msgCounter = 0;
		$readedMsg = "";
		//$logpath = "//call.br/servicos/LOGS/LogsForceLogoff/";
		$logpath = "//call.br/servicos/LOGS/LogsMessages/SCL/";
		$logFolder = "//call.br/servicos/LOGS/LogsMessages/SCL/logs/";
		
		$acp = base64_decode($sysPwd);
		$acu = base64_decode($sysAcc);
		
		//Dados de conexão com serviço
		$ldapU = "call\\" . $acu;
		$ldapPw = $acp;
		$filt = '(&(objectClass=User)(sAMAccountname=' . $account . '))';
		$database = "DC=call,DC=br";
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
			if(isset($_GET['excld'])){
				if(isset($_GET['file'])){
					$expFile = base64_decode($_GET['file']);
					unlink($expFile);
				}
			} else {
				$accountDn = $info[$i]["distinguishedname"][0];
				$msgCounter = explode("|", $info[$i]["extensionattribute13"][0]);
				$msgCount = (Int)$msgCounter[0];
				$atrContent = ($msgCount - 1);
				if($atrContent < 0){
					$atrContent = 0;
				}
				for ($j = 1; $j <= $msgCount; $j++){
					if($j == $message){
						$content = "";
						$readedMsg = $msgCounter[$j];
					} else {
						$content = "|" . $msgCounter[$j];
					}
					$atrContent = $atrContent . $content;
				}
				$dado["extensionattribute13"] = $atrContent;
				$ldapC = ldap_mod_replace($lc, $accountDn, $dado);
				//Grava .inf
				$toSearchInf = "@#" . $readedMsg;
				$expSender = explode("_", $readedMsg, 2);
				$sender = $expSender[0];
				$infFile = "//call.br/servicos/LOGS/LogsMessages/SCL/inf/" . $sender . ".inf";
				$infContent = file_get_contents($infFile);
				$filter = explode($toSearchInf, $infContent, 2);
				$delimiter = explode("total", $filter[1], 2);
				$confirmation = str_replace("0|" . $account, "1|" . $account, $delimiter[0]);
				$toWriteInf = $filter[0] . $toSearchInf . $confirmation . "total" . $delimiter[1];
				echo $toWriteInf;
				
				file_put_contents($infFile, $toWriteInf);
				
				
				//Escreve log
				$conteudo = "#" . $horas . ":" . $minutos  . "|-|" . $account . " Confirmou a leitura da mensagem->" . $readedMsg . "\r\n";
				$writeFile = $logFolder . "/" . $account . "_" . $anos . "-" . $meses . "-" . $dias;
				$fileCounter = 0;
				while(file_exists($writeFile . ".log")){
					$fileCounter++;
					$writeFile = $logFolder . "/" . $account . "_" . $anos . "-" . $meses . "-" . $dias . "_-_" . $fileCounter;
				}
				$writeFile = $writeFile . ".log";
				file_put_contents($writeFile, $conteudo, FILE_APPEND);
			}
		}
		ldap_close($lc);
		$account = base64_encode($account);
		
		header("Location:index.php?act=" . base64_encode($account));
		  
	?>

	</body>
	
</html>


