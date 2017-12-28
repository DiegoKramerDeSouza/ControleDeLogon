	<?php
		
		require_once("restrict.php");
		require_once("conf.php");
		require_once("getDate.php");
		require_once("userInfo.php");
		require_once("ldapConnection.php");
		
		date_default_timezone_set("America/Sao_Paulo");
				
		$userAcc = $_POST['searched'];
		$returned = $_POST['database'];
		$userDN = $_POST['database_user'];
		$groups = $_POST['toinsertDNGroups'];
		
		if ($groups == ""){
			header("Location:gerencia.php?searchUser=" . $userAcc . "&database_user=" . $returned . "&result=9");
			exit;
		}
		
		$acp = base64_decode($sysPwd);
		$acu = base64_decode($sysAcc);
		
		//Dados de conexão com serviço
		$ldapU = "call\\" . $acu;
		$base = $returned;
		$userbase = explode(",", $base, 2);
		$ldapPw = $acp;
		$ldapC = False;
		
		if($lc){
			//Binding com CONTA DE SERVIÇO
			$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die ("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>N&atilde;o foi poss&iacute;vel conectar!<br /> Favor verificar seu usu&aacute;rio e senha e tente novamente.</strong><br /><br /><a href='.\home.php?filter=" . $_SESSION['equipeFilter'] . "' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
		}
		
		//Filtro para pesquisa LDAP
		$toLog = "";
		$conteudo = "";
		$denied = false;
		$groupsToInsert = explode("||", $groups);
		foreach($groupsToInsert as $group){
			$verificacao = explode(",OU=", $group, 2);
			if (isset($verificacao[1])){
				//array_push($userGroups, $newGroup);
				$toLog = $toLog . $verificacao[0] . "; ";
				$filt = '(&(objectClass=Group)(distinguishedName=' . $group . '))';
				$sr = ldap_search($lc, $base, $filt);
				$info = ldap_get_entries($lc, $sr);				
				
				//Insere novos grupos
				$conteudo = $conteudo . "#" . $horas . ":" . $minutos  . "|-|" . $login . " Inseriu o colaborador de conta " . $userAcc . " nos grupos: " . $toLog . ".\r\n";
				$dado["member"] = $userDN;
				if($group != "" || $group != NULL){
					//Inserção/modificação de grupos no AD
					$ldapC = ldap_mod_add($lc, $group, $dado) or die (file_put_contents($filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log", "Não foi possível executar --- " . $conteudo, FILE_APPEND)("Location:gerencia.php?searchUser=" . $userAcc . "&database_user=" . $dbView . "&result=8"));
				}
				//Escreve log
				$writeFile = $filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log";
				file_put_contents($writeFile, $conteudo, FILE_APPEND);
			}
		}
		
		//Escreve log
		//$writeFile = $filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log";
		//file_put_contents($writeFile, $conteudo, FILE_APPEND);
		
		ldap_close($lc);
		
		header("Location:gerencia.php?searchUser=" . $userAcc . "&database_user=" . $dbView . "&result=7");
				
	?>



