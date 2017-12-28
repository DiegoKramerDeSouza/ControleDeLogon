	<?php
		require_once("conf.php");
		require_once("restrict.php");
		require_once("getDate.php");
		require_once("ldapConnection.php");
		require_once("userInfo.php");
		
		date_default_timezone_set("America/Sao_Paulo");
						
		$userAcc = $_POST['search'];
		$returned = $dbView;
								
		$acp = base64_decode($sysPwd);
		$acu = base64_decode($sysAcc);

		if($lc){
			//Dados de conexão com serviço
			$ldapUser = "call\\" . $acu;
			$Database = $returned;
			$ldapPassword = $acp;
			//Binding com conta de serviço
			$ldapB = ldap_bind($lc, $ldapUser, $ldapPassword) or die (header("Location:home.php?filter=*&result=2"));
		}
		if($ldapB){
			//Filtro para pesquisa LDAP
			$filt = '(&(objectClass=User)(sAMAccountname=' . $userAcc . '))';
			//LDAP search $base com filtro $filt
			$sr = ldap_search($lc, $Database, $filt);
			//Grava resultados em $info
			$info = ldap_get_entries($lc, $sr);
			if($info["count"] > 0){
			//if(isset($info[0]["samaccountname"][0])){
				//Define DistinguishedName do usuário pesquisado
				$dnUser = $info[0]["distinguishedname"][0];
				//Define atributo de equipe
				$dado["extensionAttribute4"] = $login;
				
				//Inserção desbloqueio
				$ldapC = ldap_mod_replace($lc, $dnUser, $dado) or die (header("Location:equipe.php?erro=6"));
				
				ldap_close($lc);
				header("Location:equipe.php?result=14");
				
			} else {
				ldap_close($lc);
				header("Location:equipe.php?erro=8");
			}
		}
		
		

	?>



