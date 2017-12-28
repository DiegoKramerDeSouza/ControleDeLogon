

	<?php
		require_once("conf.php");
		require_once("restrict.php");
		require_once("getDate.php");
		require_once("ldapConnection.php");
		require_once("userInfo.php");
		
		date_default_timezone_set("America/Sao_Paulo");
						
		$userAcc = $_GET['searched'];
		$returned = $_GET['database_unlock'];
		$motivo = $_GET['motivo'];
		
		if ($motivo == ""){
			$motivo = "-----";
		}
				
		$acp = base64_decode($sysPwd);
		$acu = base64_decode($sysAcc);

		if($lc){
			//Dados de conexão com serviço
			$ldapUser = "call\\" . $acu;
			$Database = $returned;
			$ldapPassword = $acp;
			//Binding com conta de serviço
			$ldapB = ldap_bind($lc, $ldapUser, $ldapPassword) or die (header("Location:home.php?filter=" . $_SESSION['equipeFilter'] . "&result=2"));
		}
		if($ldapB){
			//Filtro para pesquisa LDAP
			$filt = '(&(objectClass=User)(sAMAccountname=' . $userAcc . '))';
			//LDAP search $base com filtro $filt
			$sr = ldap_search($lc, $Database, $filt);
			//Ordena resultados
			$sort = ldap_sort($lc, $sr, 'name');
			//Grava resultados em $info
			$info = ldap_get_entries($lc, $sr);
			if(isset($info[0]["samaccountname"][0])){
				//Define DistinguishedName do usuário pesquisado
				$dnUser = $info[0]["distinguishedname"][0];
				$userName = $info[0]["cn"][0];
				//Desbloqueia
				$dado["lockouttime"] = 0; 
				//Inserção desbloqueio
				$ldapC = ldap_mod_replace($lc, $dnUser, $dado) or die (header("Location:unlock.php?searchUser=$userAcc&database_unlock=$dbView&erro=6"));
				
				//Escreve log
				$conteudo =  "#" . $horas . ":" . $minutos  . "|-|" . $login . " Desbloqueou a conta do colaborador " . $userAcc . " - " . $userName . "\r\nPelo seguinte motivo:" . $motivo . "\r\n";
				$writeLog = $filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log";
				file_put_contents($writeLog, $conteudo, FILE_APPEND);
			}
			else{
				header("Location:unlock.php?searchUser=$userAcc&database_unlock=$dbView&erro=7");
			}
			
		}
		
		ldap_close($lc);
		header("Location:unlock.php?searchUser=$userAcc&database_unlock=$dbView&erro=5");

	?>
	

