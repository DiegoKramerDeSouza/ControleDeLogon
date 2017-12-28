<!DOCTYPE html>
<html>
	<?php
		require_once("pageInfo.php");
		echo $htmlHeader;
	?>
	
	<body>
	<?php
		echo $htmlloading;
		
		require_once("conf.php");
		require_once("getDate.php");
		require_once("userInfo.php");
		require_once("restrict.php");
		require_once("ldapConnection.php");
		
		date_default_timezone_set("America/Sao_Paulo");
				
		$UA = $_POST['User'];
		$MT = $_POST['motivacao'];
		
		$userAcc = $UA;
		$motivo = $MT;
		
		$acp = base64_decode($sysPwd);
		$acu = base64_decode($sysAcc);
		
		//Dados de conexão com serviço
		$ldapU = "call\\" . $acu;
		$base = $dbView;
		//$base = $returned;
		$ldapPw = $acp;
		$ldapC = False;
		$count = 0;
		
		if($lc){
			//Binding com CONTA DE SERVIÇO
			$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die ("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>N&atilde;o foi poss&iacute;vel conectar!<br /> Favor verificar seu usu&aacute;rio e senha e tente novamente.</strong><br /><br /><a href='.\home.php?filter=" . $_SESSION['equipeFilter'] . "' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
		}
		if($ldapB){
			
		}
		//Filtro para pesquisa LDAP
		$filt = '(&(objectClass=User)(sAMAccountname=' . $userAcc . '))';

		$sr = ldap_search($lc, $base, $filt);
		$sort = ldap_sort($lc, $sr, 'name');
		$info = ldap_get_entries($lc, $sr);
		
		$dnUser = $info[0]["distinguishedname"][0];
		$userName = $info[0]["cn"][0];
		$account = $info[0]["samaccountname"][0];
		$solicitarTempo = Null;
		$valEx1 = "";
		$valEx10 = "";
		$valEx11 = "";
		$valEx12 = "";
		$valEx2 = "";
		$valEx3 = "";
		
		if (isset($info[0]["extensionattribute1"][0])){
			$valEx1 = $info[0]["extensionattribute1"][0];
			$dado["extensionAttribute1"] = $valEx1;
			$count++;
		}
		if (isset($info[0]["extensionattribute10"][0])){
			$valEx10 = $info[0]["extensionattribute10"][0];
			$dado["extensionAttribute10"] = $valEx10;
			$count++;
		}
		if (isset($info[0]["extensionattribute11"][0])){
			$valEx11 = $info[0]["extensionattribute11"][0];
			$dado["extensionAttribute11"] = $valEx11;
			$count++;
		}
		if (isset($info[0]["extensionattribute12"][0])){
			$valEx12 = $info[0]["extensionattribute12"][0];
			$dado["extensionAttribute12"] = $valEx12;
			$count++;
		}
		if (isset($info[0]["extensionattribute2"][0])){
			$valEx2 = $info[0]["extensionattribute2"][0];
			$dado["extensionAttribute2"] = $valEx2;
			$count++;
		}
		if (isset($info[0]["extensionattribute3"][0])){
			$valEx3 = $info[0]["extensionattribute3"][0];
			$dado["extensionAttribute3"] = $valEx3;
			$count++;
		}
		
		if ($count > 0){
			//Executa a remoção de entradas
			$ldapC = ldap_mod_del($lc, $dnUser, $dado) or die (file_put_contents($filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log", "Não foi possível - ", FILE_APPEND)("Location:user.php?search=" . $userAcc . "&result=13"));	//Inserção/modificação no AD
			
			//Escreve log
			$conteudo = "#" . $horas . ":" . $minutos  . "|-|" . $login . " Limpou os dados de entrada e saida do colaborador " . $userAcc . " - " . $userName . ".\r\n||Motivo: " . $motivo . ".\r\n||Valores Anteriores||\r\n||Horas Extras: " . $valEx1 . "\r\n||Entrada: " . $valEx12 . "\r\n||Ultimo Logon: " . $valEx10 . "\r\n||Saida: " . $valEx11 . "\r\n||Solicitacao de tempo: " . $valEx2 . "\r\n||Solicitacao de logoff: " . $valEx3 . "\r\n";
			$writeFile = $filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log";
			file_put_contents($writeFile, $conteudo, FILE_APPEND);
		}
		
		//Fecha conexão LDAP
		ldap_close($lc);
		
		//Redireciona para a página com a resposta
		header("Location:user.php?search=" . $userAcc . "&result=12");
				
		?>
		
		<script src='scripts/jquery-2.1.4.min.js'></script>
		<script src='scripts/bootstrap.min.js'></script>
		<script src='scripts/animated.js'></script>
		
	</body>
	
</html>


