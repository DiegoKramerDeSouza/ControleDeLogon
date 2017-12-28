
	<?php
		require_once("conf.php");
		require_once("getDate.php");
		require_once("userInfo.php");
		require_once("ldapConnection.php");
		
		date_default_timezone_set("America/Sao_Paulo");
				
		$userAcc = $_GET['account'];
		$returned = $_GET['database'];
		$opt = $_GET['opt'];
		$acp = base64_decode($sysPwd);
		$acu = base64_decode($sysAcc);
		
		//Dados de conexão com serviço
		$ldapU = "call\\" . $acu;
		$base = $returned;
		$ldapPw = $acp;
		$ldapC = False;
		
		if($lc){
			//Binding com CONTA DE SERVIÇO
			$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die ("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>N&atilde;o foi poss&iacute;vel conectar!<br /> Favor verificar seu usu&aacute;rio e senha e tente novamente.</strong><br /><br /><a href='.\home.php' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
		}
		
		//Filtro para pesquisa LDAP
		$filt = '(&(objectClass=User)(sAMAccountname=' . $userAcc . '))';

		$sr = ldap_search($lc, $base, $filt);
		$sort = ldap_sort($lc, $sr, 'name');
		$info = ldap_get_entries($lc, $sr);
		$ip = NULL;
		$dnUser = "CN=" . $info[0]["cn"][0]. "," . $base;
		$userName = $info[0]["cn"][0];
		$account = $info[0]["samaccountname"][0];
		
		//if (isset($info[0]["extensionattribute4"][0])){
			//$ip = $info[0]["extensionattribute4"][0];
		//}
		
		//$cmd = "net use \\\\" . $ip . " " . $acp . " /user:" . $acu . "@call.br && shutdown /r /f /t 0 /m \\\\" . $ip;
		//shell_exec($cmd);
		if ($opt == "ok"){
			$dado["extensionAttribute3"] = "Logoff-Now"; //Insere solicitação de logoff OK
			$conteudo = "#" . $horas . ":" . $minutos  . "|-|" . $login . " efetuou o logoff do colaborador " . $userAcc . " - " . $userName . ".\r\n";
			$homeHeader = "Location:home.php?result=5";
		} else {
			$dado["extensionAttribute3"] = "cancel"; //Insere solicitação de logoff CANCEL
			$conteudo = "#" . $horas . ":" . $minutos  . "|-|" . $login . " cancelou a solicitacao de logoff do colaborador " . $userAcc . " - " . $userName . ".\r\n";
			$homeHeader = "Location:home.php?result=6";
		}
		
		$ldapC = ldap_mod_replace($lc, $dnUser, $dado) or die (file_put_contents($filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".txt", "Não foi possível - ", FILE_APPEND)("Location:home.php?result=2"));	//Inserção/modificação no AD
		
		//Escreve log
		$writeFile = $filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log";
		file_put_contents($writeFile, $conteudo, FILE_APPEND);
		ldap_close($lc);
		
		header($homeHeader);
	?>
		


