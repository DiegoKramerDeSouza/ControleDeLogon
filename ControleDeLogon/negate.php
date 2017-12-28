
		<?php
			require_once("conf.php");
			require_once("getDate.php");
			require_once("userInfo.php");
			require_once("ldapConnection.php");
				
			date_default_timezone_set("America/Sao_Paulo");
				
			$acp = base64_decode($sysPwd);
			$acu = base64_decode($sysAcc);
			
			$userAcc = $_GET['User'];
			$opeType = $_GET['opt'];
			
			//Dados de conexão com serviço
			$ldapU = "call\\" . $acu;
			$ldapPw = $acp;
			$base = "DC=call,DC=br";
			$ldapC = False;
				
			if($lc){
				//Binding com CONTA DE SERVIÇO
				$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die ("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>N&atilde;o foi poss&iacute;vel conectar!<br /> Favor verificar seu usu&aacute;rio e senha e tente novamente.</strong><br /><br /><a href='.\home.php?filter=" . $_SESSION['equipeFilter'] . "' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
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
			$tempoExtra = Null;
				
			if (isset($info[0]["extensionattribute2"][0])){
				$solicitarTempo = $info[0]["extensionattribute2"][0];
			}
			if (isset($info[0]["extensionattribute1"][0])){
				$tempoExtra = $info[0]["extensionattribute1"][0];
			}
			if (isset($info[0]["extensionattribute12"][0])){
				$arrayDiaEnt = explode('|', $info[0]["extensionattribute12"][0]);
				$diaEnt = (Int)$arrayDiaEnt[0];
				$mesEnt = (Int)$arrayDiaEnt[1];
				$horaEnt = (Int)$arrayDiaEnt[2];
				$minEnt = (Int)$arrayDiaEnt[3];
			}
			if (isset($info[0]["extensionattribute11"][0])){
				$arrayDiaSaida = explode('|', $info[0]["extensionattribute11"][0]);
				$diaSaida = (Int)$arrayDiaSaida[0];
				$mesSaida = (Int)$arrayDiaSaida[1];
				$horaSaida = (Int)$arrayDiaSaida[2];
				$minSaida = (Int)$arrayDiaSaida[3];
			}
			
			if ($opeType == "cancel"){
				$horaSaida = ($horaEnt + 6);
				if ($horaSaida >= 24){
					$horaSaida = ((Int)$horaEnt + 6) - 24;
					$diaSaida = ((Int)$diaSaida + 1);
					if ($diaSaida > $mesPadrao){
						$mesSaida = ((Int)$mesSaida + 1);
						if($mesSaida > 12){
							$mesSaida = 1;
						}
					}
				}
				/*
				if ($tempoExtra == 60){
					$horaSaida = $horaSaida - 1;
				}
				else if ($tempoExtra == 120){
					$horaSaida = $horaSaida - 2;
				}
				if ($horaSaida < 0){
					$horaSaida = (24 + (Int)$horaSaida);
					$diaSaida = ((Int)$diaSaida - 1);
					if ($diaSaida < 0){
						$mesSaida = ((Int)$mesSaida - 1);
						$diaSaida = $mesPadrao;
					}
				}
				*/
				$dado["extensionattribute1"] = 0;
				$dado["extensionattribute11"] = $diaSaida . "|" . $mesSaida . "|" . $horaSaida . "|" . $minSaida;
			}
			$dado["extensionattribute1"] = 0;
			$solicitarTempo = "false"; //Apaga a solicitação
			$dado["extensionattribute2"] = $solicitarTempo; //Insere a solicitação desativada
			
			$ldapC = ldap_mod_replace($lc, $dnUser, $dado) or die (header("Location:home.php?filter=" . $_SESSION['equipeFilter'] . "&result=4"));
			
			
			//Escreve log
			$conteudo = "#" . $horas . ":" . $minutos  . "|-|" . $login . " - negou a solicitação do colaborador " . $userAcc . " - " . $userName . ".\r\n";
			$writeFile = $filepath . "/" . $userAcc . "_" . $anos . "-" . $meses . "-" . $dias . ".log";
			file_put_contents($writeFile, $conteudo, FILE_APPEND);
						
			ldap_close($lc);
			
			header("Location:user.php?search=" . $userAcc . "&result=3");

		?>
