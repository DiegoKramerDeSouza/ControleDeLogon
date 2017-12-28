
	<?php
		
		require_once("conf.php");
		require_once("getDate.php");
		require_once("userInfo.php");
		require_once("restrict.php");
		require_once("ldapConnection.php");
		
		date_default_timezone_set("America/Sao_Paulo");
				
		$userAcc = $_POST['search'];
		$tempo = $_POST['tempo'];
		$returned = $_POST['database'];
		
		$tempo = (Int)$tempo;
		$addH = 0;
		$addM = 0;
		$mesPadrao = 31;
		
		$acp = base64_decode($sysPwd);
		$acu = base64_decode($sysAcc);
		
		//Dados de conexão com serviço
		$ldapU = "call\\" . $acu;
		//$base = $dbView;
		$base = $returned;
		$ldapPw = $acp;
		$ldapC = False;
		$exTempo = 0;
		
		if($lc){
			//Binding com CONTA DE SERVIÇO
			$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die ("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>N&atilde;o foi poss&iacute;vel conectar!<br /> Favor verificar seu usu&aacute;rio e senha e tente novamente.</strong><br /><br /><a href='.\home.php?filter=" . $_SESSION['equipeFilter'] . "' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
		}
		
		//Filtro para pesquisa LDAP
		$filt = '(&(objectClass=User)(sAMAccountname=' . $userAcc . '))';

		$sr = ldap_search($lc, $base, $filt);
		$sort = ldap_sort($lc, $sr, 'name');
		$info = ldap_get_entries($lc, $sr);
		
		$dnUser = "CN=" . $info[0]["cn"][0]. "," . $base;
		$userName = $info[0]["cn"][0];
		$account = $info[0]["samaccountname"][0];
		$solicitarTempo = Null;
		
		
		if (isset($info[0]["extensionattribute1"][0])){
			$exTempo = $info[0]["extensionattribute1"][0];
		}
		if (isset($info[0]["extensionattribute10"][0])){
			$arrayDiaEnt = explode('|', $info[0]["extensionattribute10"][0]);
			$diaEnt = $arrayDiaEnt[0];
			$mesEnt = $arrayDiaEnt[1];
			$horaEnt = $arrayDiaEnt[2];
			$minEnt = $arrayDiaEnt[3];
		}
		if (isset($info[0]["extensionattribute11"][0])){
			$arrayDiaSaida = explode('|', $info[0]["extensionattribute11"][0]);
			$diaSaida = $arrayDiaSaida[0];
			$mesSaida = $arrayDiaSaida[1];
			$horaSaida = $arrayDiaSaida[2];
			$minSaida = $arrayDiaSaida[3];
		}
		if (isset($info[$i]["extensionattribute2"][0])){
			$solicitarTempo = $info[0]["extensionattribute2"][0];
		}
		if ($solicitarTempo == "true"){
			$solicitante = "<span class='blink'><img src='./Images/redAlert.png'  class='blinkObj' /></span>";
			$upperStr = True;
		}
		
		$diaEnt = (Int)$diaEnt;
		$horaEnt = (Int)$horaEnt;
		$minEnt = (Int)$minEnt;
		$diaSaida = (Int)$diaSaida;
		$horaSaida = (Int)$horaSaida;
		$minSaida = (Int)$minSaida;
		
		if($tempo >= 60 && $tempo < 120){
			$addH = 1;
			$addM = ($tempo - 60);
			//Adição de Minutos 
			$minSaida = $minSaida + $addM;
			while($minSaida >= 60){
				$horaSaida = $horaSaida + 1;
				$minSaida = $minSaida - 60;
			}
			//Adição de Horas
			$horaSaida = $horaSaida + $addH;
			if($horaSaida >= 24){
				$horaSaida = $horaSaida - 24;
				$diaSaida = $diaSaida + 1;
			}
			//Verifica mês
			if($diaSaida > $mesPadrao){
				$diaSaida = 1;
			}
						
		}
		elseif($tempo == 120){
			if($exTempo > 0){
				$addH = 1;
			} else {
				$addH = 2;
			}
			//Adição de Horas
			$horaSaida = $horaSaida + $addH;
			if($horaSaida >= 24){
				$horaSaida = $horaSaida - 24;
				$diaSaida = $diaSaida + 1;
			}
			//Verifica mês
			if($diaSaida > $mesPadrao){
				$diaSaida = 1;
			}
						
		}
		else{
			$addM = $tempo;
			//Adição de Minutos 
			$minSaida = $minSaida + $addM;
			while($minSaida >= 60){
				$horaSaida = $horaSaida + 1;
				$minSaida = $minSaida - 60;
			}
			//Adição de Horas
			$horaSaida = $horaSaida + $addH;
			if($horaSaida >= 24){
				$horaSaida = $horaSaida - 24;
				$diaSaida = $diaSaida + 1;
			}
			//Verifica mês
			if($diaSaida > $mesPadrao){
				$diaSaida = 1;
			}
			
		}
		$tempo = $exTempo + $tempo;
		$dado["extensionAttribute1"] = $tempo; //Insere tempo extra
		
		$newLogoffTime = $diaSaida . "|" . $mesSaida . "|" . $horaSaida . "|" . $minSaida;
		$dado["extensionAttribute11"] = $newLogoffTime;
		
		if (isset($solicitarTempo) && $solicitarTempo == "true"){
			$solicitarTempo = "confirmado";
		} else {
			$solicitarTempo = "concedido";
		}
		
		$dado["extensionattribute2"] = $solicitarTempo; //Insere a solicitação desativada
		
		$ldapC = ldap_mod_replace($lc, $dnUser, $dado) or die (file_put_contents($filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log", "Não foi possível - ", FILE_APPEND)("Location:home.php?filter=" . $_SESSION['equipeFilter'] . "&result=2"));	//Inserção/modificação no AD
		
		//Escreve log
		$conteudo = "#" . $horas . ":" . $minutos  . "|-|" . $login . " forneceu " . $tempo . " minutos ao colaborador " . $userAcc . " - " . $userName . ".\r\n";
		$writeFile = $filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log";
		file_put_contents($writeFile, $conteudo, FILE_APPEND);
		ldap_close($lc);
		
		header("Location:user.php?search=" . $userAcc . "&result=0");
				
	?>



