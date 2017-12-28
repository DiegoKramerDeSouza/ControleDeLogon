<!DOCTYPE html>
<html>
	<header>
		<meta charset="uft-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<title>SCL</title>
		
		<link rel="shortcut icon" href="Images/Hexagon.png">
		<link rel='stylesheet' href='./Styles/bootstrap_free.css' />
		<link rel='stylesheet' href='./Styles/font-awesome.css' />
		
		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		
	</header>
	
	<body>

		<?php
		
		require_once("getDate.php");
		require_once("ldapConnection.php");
		
		date_default_timezone_set("America/Sao_Paulo");
				
		$userAcc = $_POST['search'];
		$returned = $_POST['database'];		
		$acp = base64_decode($sysPwd);
		$acu = base64_decode($sysAcc);
		$filepath = "//call.br/servicos/LOGS/LogsForceLogoff";
		
		//Dados de conexão com serviço
		$ldapU = "call\\" . $acu;
		$ldapPw = $acp;
		$base = $returned;
		$ldapC = False;
		
		if($lc){
			//Binding com CONTA DE SERVIÇO
			$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die ("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>N&atilde;o foi poss&iacute;vel conectar!<br /> Favor verificar seu usu&aacute;rio e senha e tente novamente.</strong><br /><br /><a href='.\home.php' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
		}
		
		if($ldapB){
			//Filtro para pesquisa LDAP
			$filt = '(&(objectClass=User)(sAMAccountname=' . $userAcc . '))';

			$sr = ldap_search($lc, $base, $filt);
			$sort = ldap_sort($lc, $sr, 'name');
			$info = ldap_get_entries($lc, $sr);
			
			$dnUser = $info[0]["distinguishedname"][0];;
			$userName = $info[0]["cn"][0];
			$account = $info[0]["samaccountname"][0];
				
			$dado["extensionattribute2"] = "true";
			
			$ldapC = ldap_mod_replace($lc, $dnUser, $dado) or die (file_put_contents($filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log", "Não foi possível - ", FILE_APPEND)("Location:index.php?search=" . $userAcc . "&result=2"));	//Inserção/modificação no AD
			
			//Escreve log
			$conteudo = "#" . $horas . ":" . $minutos  . "|-|" . $userAcc . " - " . $userName . " solicitou mais tempo para seu supervisor.\r\n";
			$writeFile = $filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log";
			file_put_contents($writeFile, $conteudo, FILE_APPEND);
			//finaliza conexão com ldap
			ldap_close($lc);
			
			header("Location:index.php?search=" . base64_encode($userAcc) . "&result=0");
		}
		
				
		?>
		
		<script src='scripts/jquery-2.1.4.min.js'></script>
		<script src='scripts/bootstrap.min.js'></script>
		<script src='scripts/animated.js'></script>
		
	</body>
	
</html>


