<?php
	if(session_id() == '') {
		session_start();
	}
	if (isset($_POST['matricula']) && isset($_POST['senha'])){
		
		$login = $_POST['matricula'];
		$senha = $_POST['senha'];
		$allowedUser = false;
		$name = "";
		
		//Dados de Conexão LDAP================================================================================
		$ldapU = "call\\" . $login;
		$ldapPw = $senha;
		$base = "DC=call,DC=br";
		$ldapH = "LDAP://SVDF07W000010.call.br";
		$ldapP = "389";
		//=====================================================================================================
		//Conexão LDAP
		$lc = ldap_connect($ldapH, $ldapP) or die (header("Location:index.php?erro=1"));
		ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($lc, LDAP_OPT_REFERRALS, 0);
		
		if($lc){
			$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die (header("Location:index.php?erro=2"));
		}
		if($ldapB){
			$filt = '(&(objectClass=User)(sAMAccountname=' . $login . '))';
			$sr = ldap_search($lc, "DC=call,DC=br", $filt);
			$userInfo = ldap_get_entries($lc, $sr);
			
			if (isset($userInfo[0]["displayname"][0])){
				$name = $userInfo[0]["displayname"][0];
				$groups = $userInfo[0]["memberof"];
				foreach($groups as $member){
					$member = explode(",", $member, 2);
					if ($member[0] == "CN=G.GTI.DESBLOQUEIO.CONTA.ADM"){
						$allowedUser = true;
					}
				}
			}
			if (isset($userInfo[0]["thumbnailphoto"])){
				$photos = $userInfo[0]["thumbnailphoto"][0];
				$photo = "<img class='img-circle' src='data:image/jpeg;base64," . base64_encode($photos) . "' style='width:50px; height:50px;'/>";
				
				$tempPhoto = "<img class='img-circle' src='data:image/jpeg;base64," . base64_encode($photos) . "' style='width:100px; height:100px;'/>";
				$tempPhotoHome = $tempPhoto;
			} else {
				$photo = "<img class='img-circle' src='Images/user_icon.png' style='width:50px; height:50px;'>";
				
				$tempPhoto = "<img class='img-circle' src='Images/user_icon.png' style='width:100px; height:100px;'>";
				$tempPhotoHome = "<img class='img-circle' src='ControleDeLogon/Images/user_icon.png' style='width:100px; height:100px;'>";
			}
								
			$_SESSION['matricula'] = $login;
			$_SESSION['senha'] = $senha;
			$_SESSION['name'] = $name;
			$_SESSION['photo'] = $photo;
			$_SESSION['initphoto'] = $tempPhoto;
			$_SESSION['initphotoHome'] = $tempPhotoHome;
			$_SESSION['admPermission'] = $allowedUser;
			$_SESSION['equipeFilter'] = "*";
			
			//Coleta UserInfo==================================================
			for ($i = 0; $i < $userInfo["count"]; $i++) {
		
				$distinguishedname = $userInfo[$i]["distinguishedname"][0];
				$account = $userInfo[$i]["samaccountname"][0];
				$cn = $userInfo[$i]["cn"][0];
				$userPath = explode(',', $distinguishedname, 2);
				$userLocation = $userPath[1];
				$depto = "";
				$operationAccount = true;
				if(isset($userInfo[$i]["extensionattribute3"][0])){
					$_SESSION['msgInfo'] = $userInfo[$i]["extensionattribute3"][0];
				} else {
					$_SESSION['msgInfo'] = '0|0|0';
				}
				//Identifica Operação--------------
				if(strpos($userLocation, "Departamento de Tecnologia") != ""){
					$dbView = "DC=call,DC=br";
					$depto = "TI";
					$operationAccount = false;
					$const_url = "*";
				}
				elseif(strpos($userLocation, "5tech") != ""){
					$dbView = "DC=call,DC=br";
					$depto = "TI";
					$operationAccount = false;
					$const_url = "*";
				}
				elseif(strpos($userLocation, "01-Administrativo") != ""){
					$dbView = ",OU=01-Administrativo,OU=07-SIA,OU=DF,OU=Call,DC=call,DC=br";
					//$operationAccount = false;
					if(strpos($userLocation, "AdministracaoDePessoal") != ""){
						$dbView = "OU=AdministracaoDePessoal" . $dbView;
						$depto = "DAP";
						$const_url = "*";
					}
					elseif(strpos($userLocation, "Comercial") != ""){
						$dbView = "OU=Comercial" . $dbView;
						$depto = "Comercial";
						$const_url = "*";
					}
					elseif(strpos($userLocation, "DesenvolvimentoHumano") != ""){
						$dbView = "OU=DesenvolvimentoHumano" . $dbView;
						$depto = "DH";
						$const_url = "*";
					}
					elseif(strpos($userLocation, "Financeiro") != ""){
						$dbView = "OU=Financeiro" . $dbView;
						$depto = "Financeiro";
						$const_url = "*";
					}
					elseif(strpos($userLocation, "SuperintendenciaDeOperacoes") != ""){
						$dbView = "OU=SuperintendenciaDeOperacoes" . $dbView;
						$depto = "Superintendencia";
						$const_url = "*";
					} else {
						header("Location:index.php?erro=4");
						exit;
					}
				}
				elseif(strpos($userLocation, "06-Senado") != ""){
					$dbView = "OU=06-Senado,OU=CallCenter,OU=01-SENADO,OU=DF,OU=Call,DC=call,DC=br";
					$depto = "Senado";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "02-SPM") != ""){
					$dbView = "OU=02-SPM,OU=CallCenter,OU=05-SIBSQ2,OU=DF,OU=Call,DC=call,DC=br";
					$depto = "SPM";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "05-SAU") != ""){
					$dbView = "OU=05-SAU,OU=CallCenter,OU=07-SIA,OU=DF,OU=Call,DC=call,DC=br";
					$depto = "SAU";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "06-AtivosSA") != ""){
					$dbView = "OU=06-AtivosSA,OU=CallCenter,OU=07-SIA,OU=DF,OU=Call,DC=call,DC=br";
					$depto = "Ativos";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "29-MEC") != ""){
					$dbView = "OU=29-MEC,OU=CallCenter,OU=07-SIA,OU=DF,OU=Call,DC=call,DC=br";
					$depto = "MEC";
					$const_url = "http://10.61.195.79/json/agents-status";
				}
				elseif(strpos($userLocation, "32-BRCAP") != ""){
					if(strpos($userLocation, "OU=DF") != ""){
						$dbView = "OU=32-BRCAP,OU=CallCenter,OU=07-SIA,OU=DF,OU=Call,DC=call,DC=br";
					}
					elseif(strpos($userLocation, "OU=BA") != ""){
						$dbView = "OU=32-BRCAP,OU=CallCenter,OU=01-CamDasArvores,OU=BA,OU=Call,DC=call,DC=br";
					}
					$depto = "BRCAP";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "33-MTE") != ""){
					$dbView = "OU=33-MTE,OU=CallCenter,OU=07-SIA,OU=DF,OU=Call,DC=call,DC=br";
					$depto = "MTE";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "34-CEUMA") != ""){
					$dbView = "OU=34-CEUMA,OU=CallCenter,OU=07-SIA,OU=DF,OU=Call,DC=call,DC=br";
					$depto = "CEUMA";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "04-Eletrobras") != ""){
					if(strpos($userLocation, "OU=DF") != ""){
						$dbView = "OU=04-Eletrobras,OU=CallCenter,OU=08-SQB,OU=DF,OU=Call,DC=call,DC=br";
					}
					elseif(strpos($userLocation, "OU=AC") != ""){
						$dbView = "OU=04-Eletrobras,OU=CallCenter,OU=01-RioBranco,OU=AC,OU=Call,DC=call,DC=br";
					}
					elseif(strpos($userLocation, "OU=AL") != ""){
						$dbView = "OU=04-Eletrobras,OU=CallCenter,OU=01-Maceio,OU=AL,OU=Call,DC=call,DC=br";
					}
					elseif(strpos($userLocation, "OU=AM") != ""){
						$dbView = "OU=04-Eletrobras,OU=CallCenter,OU=01-Manaus,OU=AM,OU=Call,DC=call,DC=br";
					}
					elseif(strpos($userLocation, "OU=PI") != ""){
						$dbView = "OU=04-Eletrobras,OU=CallCenter,OU=01-Teresina,OU=PI,OU=Call,DC=call,DC=br";
					}
					elseif(strpos($userLocation, "OU=RO") != ""){
						$dbView = "OU=04-Eletrobras,OU=CallCenter,OU=01-PortoVelho,OU=RO,OU=Call,DC=call,DC=br";
					}
					elseif(strpos($userLocation, "OU=RR") != ""){
						$dbView = "OU=04-Eletrobras,OU=CallCenter,OU=01-BoaVista,OU=RR,OU=Call,DC=call,DC=br";
					}
					$depto = "Eletrobras";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "08-MDS") != ""){
					$dbView = "OU=08-MDS,OU=CallCenter,OU=08-SQB,OU=DF,OU=Call,DC=call,DC=br";
					$depto = "MDS";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "30-SERPRO") != ""){
					$dbView = "OU=30-SERPRO,OU=CallCenter,OU=08-SQB,OU=DF,OU=Call,DC=call,DC=br";
					$depto = "SERPRO";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "03-SDH") != ""){
					$dbView = "OU=03-SDH,OU=CallCenter,OU=01-CamDasArvores,OU=BA,OU=Call,DC=call,DC=br";
					$depto = "SDH";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "10-BahiaGas") != ""){
					$dbView = "OU=10-BahiaGas,OU=CallCenter,OU=01-CamDasArvores,OU=BA,OU=Call,DC=call,DC=br";
					$depto = "BahiaGas";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "11-EMBASA") != ""){
					$dbView = "OU=11-EMBASA,OU=CallCenter,OU=01-CamDasArvores,OU=BA,OU=Call,DC=call,DC=br";
					$depto = "EMBASA";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "21-ITAU") != ""){
					if(strpos($userLocation, "OU=BA") != ""){
						$dbView = "OU=21-ITAU,OU=CallCenter,OU=01-CamDasArvores,OU=BA,OU=Call,DC=call,DC=br";
					}
					elseif(strpos($userLocation, "OU=SP") != ""){
						$dbView = "OU=21-ITAU,OU=CallCenter,OU=02-LUZ,OU=SP,OU=Call,DC=call,DC=br";
					}
					$depto = "ITAU";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "14-PMBV") != ""){
					$dbView = "OU=14-PMBV,OU=CallCenter,OU=01-BoaVista,OU=RR,OU=Call,DC=call,DC=br";
					$depto = "PMBV";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "15-PMP") != ""){
					$dbView = "OU=15-PMP,OU=CallCenter,OU=01-PAUL,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "PMP";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "25-SAMU") != ""){
					$dbView = "OU=25-SAMU,OU=CallCenter,OU=01-PAUL,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "SAMU";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "20-AntiFumo") != ""){
					$dbView = "OU=20-AntiFumo,OU=CallCenter,OU=02-LUZ,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "AntiFumo";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "20-PoupaTempo") != ""){
					$dbView = "OU=20-PoupaTempo,OU=CallCenter,OU=02-LUZ,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "PoupaTempo";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "18-SEE") != ""){
					$dbView = "OU=18-SEE,OU=CallCenter,OU=03-BRESSER,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "SEE";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "23-IMESP") != ""){
					$dbView = "OU=23-IMESP,OU=CallCenter,OU=03-BRESSER,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "IMESP";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "24-SPPREV") != ""){
					$dbView = "OU=24-SPPREV,OU=CallCenter,OU=03-BRESSER,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "SPPREV";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "25-PMSP") != ""){
					$dbView = "OU=25-PMSP,OU=CallCenter,OU=03-BRESSER,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "PMSP";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "26-DEPESP") != ""){
					$dbView = "OU=26-DEPESP,OU=CallCenter,OU=03-BRESSER,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "DEPESP";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "27-SOCICAM") != ""){
					$dbView = "OU=27-SOCICAM,OU=CallCenter,OU=03-BRESSER,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "SOCICAM";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "28-SESI") != ""){
					$dbView = "OU=28-SESI,OU=CallCenter,OU=03-BRESSER,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "SESI";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "31-DPESP") != ""){
					$dbView = "OU=31-DPESP,OU=CallCenter,OU=03-BRESSER,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "DPESP";
					$const_url = "http://10.61.195.132/json/agents-status";
				}
				elseif(strpos($userLocation, "36-Detran") != ""){
					$dbView = "OU=36-Detran,OU=CallCenter,OU=03-BRESSER,OU=SP,OU=Call,DC=call,DC=br";
					$depto = "Detran";
					$const_url = "http://10.61.195.132/json/agents-status";
				} else {
					header("Location:index.php?erro=4");
					exit;
				}
				//Identifica Função/Cargo---------------------------------
				if(strpos($userLocation, "Especialistas") != ""){
					$cargo = "Especialista";
				}
				elseif(strpos($userLocation, "01-Administrativo") != ""){
					$cargo = "Administrativo";
				}
				elseif(strpos($userLocation, "Suporte Tecnico") != ""){
					$cargo = "Suporte Tecnico";
				}
				elseif(strpos($userLocation, "5tech") != ""){
					$cargo = "Suporte Tecnico";
				}
				elseif(strpos($userLocation, "Contas Administrativas") != ""){
					$cargo = "Conta Administrativa";
				}
				elseif(strpos($userLocation, "Supervisao") != ""){
					$cargo = "Supervisor";
					if($depto != "MEC"){
						$dbView = "OU=Operacao," . $dbView;
					}
				}
				elseif(strpos($userLocation, "Operacao") != ""){
					header("Location:index.php?erro=4");
					exit;
				}
				elseif(strpos($userLocation, "Coordenacao") != ""){
					$cargo = "Coordenador";
				}
				elseif(strpos($userLocation, "Backoffice") != ""){
					$cargo = "Backoffice";
					$dbView = "OU=Operacao," . $dbView;
				}
				elseif(strpos($userLocation, "Gerencia") != ""){
					$cargo = "Gerente";
				}
				elseif(strpos($userLocation, "Monitoria") != ""){
					$cargo = "Monitor";
					$dbView = "OU=Operacao," . $dbView;
				} else {
					header("Location:index.php?erro=4");
					exit;
				}
				
			}
			$_SESSION['cargo'] = $cargo;
			$_SESSION['dbView'] = $dbView;
			$_SESSION['depto'] = $depto;
			$_SESSION['const_url'] = $const_url;
			$_SESSION['operationAccount'] = $operationAccount;
			//============================================================
			ldap_close($lc);
			header("Location:home.php");
		}
	} else {
		header("Location:logout.php");
		exit();
	}
?>



