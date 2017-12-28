<?php
	
	require_once("conf.php");
	require_once("getDate.php");
	require_once("ldapConnection.php");
	
	date_default_timezone_set("America/Sao_Paulo");
	
	$usersList = $_POST["usersCodes"];
	$usersDb = $_POST["usersDatabase"];
	$usersTime = $_POST["timeNeeded"];
	
	$users = explode('_Input', $usersList);
	$numbers = count($users);
	
	$acp = base64_decode($sysPwd);
	$acu = base64_decode($sysAcc);
	
	//Dados de conexão com serviço
	$ldapUser = "call\\" . $acu;
	$ldapPassword = $acp;
	//$Database = $usersDb;
	$Database = "DC=call,DC=br";

	//Binding com conta de serviço	
	if($lc){
		$ldapB = ldap_bind($lc, $ldapUser, $ldapPassword) or die (header("Location:home.php?filter=" . $_SESSION['equipeFilter'] . "&result=2"));
	}

	for ($i = 0; $i < ($numbers - 1); $i++) {
		if($ldapB){
			$exTempo = 0;
			$solicitarTempo = Null;
			$tempo = $usersTime;
			$totalTime = 0;
			$filt = '(&(objectClass=User)(sAMAccountname=' . $users[$i] . '))';
			$sr = ldap_search($lc, $Database, $filt);
			$info = ldap_get_entries($lc, $sr);
			
			//$dnUser = "CN=" . $info[0]["cn"][0]. "," . $Database;
			$dnUser = $info[$i]["distinguishedname"][0];
			$userName = $info[$i]["cn"][0];
			$account = $info[$i]["samaccountname"][0];
			
			if (isset($info[$i]["extensionattribute1"][0])){
				$exTempo = $info[$i]["extensionattribute1"][0];
			}
			if (isset($info[$i]["extensionattribute10"][0])){
				$arrayDiaEnt = explode('|', $info[$i]["extensionattribute10"][0]);
				$diaEnt = $arrayDiaEnt[0];
				$mesEnt = $arrayDiaEnt[1];
				$horaEnt = $arrayDiaEnt[2];
				$minEnt = $arrayDiaEnt[3];
			}
			if (isset($info[$i]["extensionattribute11"][0])){
				$arrayDiaSaida = explode('|', $info[0]["extensionattribute11"][0]);
				$diaSaida = $arrayDiaSaida[0];
				$mesSaida = $arrayDiaSaida[1];
				$horaSaida = $arrayDiaSaida[2];
				$minSaida = $arrayDiaSaida[3];
			}
			if (isset($info[$i]["extensionattribute2"][0])){
				$solicitarTempo = $info[$i]["extensionattribute2"][0];
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
			
			$restante = (120 - $exTempo);
			$totalTime = ($exTempo + $tempo);
			
			if($restante <= $tempo){
				$tempo = ($restante);
			} else {
				$tempo = ($tempo);
			}
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
				$addH = 2;
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
			
			$ldapC = ldap_mod_replace($lc, $dnUser, $dado) or die (file_put_contents($filepath . "/" . $account . "_" . $anos . "-" . $meses . "-" . $dias . ".txt", "Não foi possível - ", FILE_APPEND));	//Inserção/modificação no AD
			
			//Escreve log
			$conteudo = "#" . $horas . ":" . $minutos  . "|-|" . $login . " forneceu " . $tempo . " minutos ao colaborador " . $account . " - " . $userName . ".\r\n";
			$writeFile = $filepath . "/" . $account . "_" . $anos . "-" . $meses . "-" . $dias . ".log";
			file_put_contents($writeFile, $conteudo, FILE_APPEND);
			
			header("Location:home.php?filter=" . $_SESSION['equipeFilter'] . "&result=1");
		}
	}
	ldap_close($lc);
	
	
	//DEBUG==========================
	//echo $numbers;
	//echo $users . "<br /><br />";
	//echo $usersDb . "<br /><br />";
	//echo $usersTime;
	//===============================
	//INSERÇÃO COLETIVA DE VALORES DE TEMPO

?>