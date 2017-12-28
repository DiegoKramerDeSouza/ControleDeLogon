<!DOCTYPE html>
<html>
	<header>
		<meta charset="uft-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<title>SCL</title>
		
		<link rel="shortcut icon" href="Images/Hexagon.png">
		<link rel='stylesheet' href='./styles/bootstrap_free.css' />
		<link rel='stylesheet' href='./styles/font-awesome.css' />
		<link rel='stylesheet' href='./styles/style.css' />
		<link rel='stylesheet' href='scripts/jQueryRollPlugin/jRoll.css'></link>
	</header>
	
	<body>
		<!-- Mensagens de tela-->
		<div id="msgDiv" align="right">
			<div id="dialog" align="center" style="background-color:rgba(0, 100, 160, 0.8);">
				<span id="closebtn" onclick="clearMsgBox()"><i class="fa fa-times fa-2x"></i></span>
				<span id="msgHeader"></span><br />
				<hr />
				<span id="msgBody"></span><br />
			</div>
		</div>
		<!-- DISPOSIÇÃO DOS DEMAIS CONTAÚDOS NOS LIMITES "CONTAINER"-->
		<div>
		
		<!-- INÍCIO DA CONEXÃO PHP-LDAP-->

	<?php
		include("getDate.php");
		include("ldapConnection.php");
		
		$strUser = $_GET['search'];
		$msgUser = base64_encode($strUser);
		$strUser = base64_decode($strUser);
		
		date_default_timezone_set("America/Sao_Paulo");
		
		//Variáveis de controle 
		$nao = "<strong style='font-size:18px; color: #ff0000;'>N&atilde;o</strong>";
		$sim = "<strong style='font-size:18px; color: #00ff00;'>Sim</strong>";
		$exTempo = 0;
		$base = "DC=call,DC=br";
		$forceDia = 0;
		$forceMes = 0;
		$forceHora = 0;
		$forceMinuto = 0;
						
		//Variáveis de apresentação HTML
		$html = "";
		$resultArray = array();

		if($lc){
			//Executa Binding de conta LDAP
			$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die ("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>N&atilde;o foi poss&iacute;vel conectar!<br /> Favor verificar seu usu&aacute;rio e senha e tente novamente.</strong><br /><br /><a href='.\index.php' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
		}
	
		
	//Filtro para pesquisa LDAP ================================
		//Filtro
		//$filt = '(&(objectClass=User)(objectCategory=Person))';
		$filt = '(&(objectClass=User)(sAMAccountname=' . $strUser . '))';
		//Search
		$sr = ldap_search($lc, $base, $filt);
		//Organiza
		$sort = ldap_sort($lc, $sr, 'name');
		//Recolhe entradas
		$info = ldap_get_entries($lc, $sr);
	//==========================================================
		
	//Listagem de usuários retornados ==========================
	//Início do laço FOR para cada resultado obtdo em pesquisa LDAP==============================================================
		for ($i = 0; $i < $info["count"]; $i++) {
			$solicitaOn = "disabled";
			$permitido = $nao;
			$account = $info[$i]["samaccountname"][0];
			$colaborador = $info[$i]["cn"][0];
			$dn = $info[$i]["distinguishedname"][0];
			$colablen = strlen($colaborador);
			$returnex = explode(',', $dn, 2);
			$returnbase = $returnex[1];
			$exTempo = NULL;
			$solicitarTempo = NULL;
			$solicitaLogoff = NULL;
			$menosHoras = NULL;
			$plusIcon = "";
			$upperStr = False;
			$inExtraTime = "";
			$extraTimeSet = "";
			$btnSet = "";
			$resetSet = "disabled";
			$solicitante = "&nbsp;&nbsp;<i style='color:#505050; margin-top:20px;' class='fa fa-minus fa-2x'></i>";
			$tempoRestante = "<b>--:--</b>";
			$myMessage = "";
			$newMessages = false;
			$countMessages = "";
						
			if(strpos($dn, ",OU=Call,") != ""){
				
				$diaEnt = "--";
				$horaEnt = "--";
				$minEnt = "--";
				$diaSaida = "--";
				$horaSaida = "--";
				$minSaida = "--";
				$restaHora = "--";
				$restaMinutos = "--";
				
				$diaEntFixed = "--";
				$horaEntFixed = "--";
				$minEntFixed = "--";
					
				$tempoRestante = $restaHora . ":" . $restaMinutos;
				
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
					$arrayDiaSaida = explode('|', $info[$i]["extensionattribute11"][0]);
					$diaSaida = $arrayDiaSaida[0];
					$mesSaida = $arrayDiaSaida[1];
					$horaSaida = $arrayDiaSaida[2];
					$minSaida = $arrayDiaSaida[3];
				}
				if (isset($info[$i]["extensionattribute12"][0])){
					$arrayDiaEntFixed = explode('|', $info[$i]["extensionattribute12"][0]);
					$diaEntFixed = $arrayDiaEntFixed[0];
					$mesEntFixed = $arrayDiaEntFixed[1];
					$horaEntFixed = $arrayDiaEntFixed[2];
					$minEntFixed = $arrayDiaEntFixed[3];
				}
				if (isset($info[$i]["extensionattribute2"][0])){
					$solicitarTempo = $info[$i]["extensionattribute2"][0];
				}
				if (isset($info[$i]["extensionattribute3"][0])){
					$solicitaLogoff = $info[$i]["extensionattribute3"][0];
				}
				if (isset($info[$i]["extensionattribute13"][0])){
					$myMessage = $info[$i]["extensionattribute13"][0];
					if($myMessage != "0"){
						$newMessages = true;
						$myMessage = explode("|", $myMessage, 2);
						$countMessages = $myMessage[0];
					}
				}
					
				$exTempo = (Int)$exTempo;
				$viewDiaEnt = $diaEntFixed;
				$viewHoraEnt = $horaEntFixed;
				$viewMinEnt = $minEntFixed;
				$viewDiaSaida = $diaSaida;
				$viewHoraSaida = $horaSaida;
				$viewMinSaida = $minSaida;
				
				if(strlen($viewDiaEnt) == 1){
					$viewDiaEnt = "0" . $diaEntFixed;
				}
				if(strlen($viewHoraEnt) == 1){
					$viewHoraEnt = "0" . $horaEntFixed;
				}
				if(strlen($viewMinEnt) == 1){
					$viewMinEnt = "0" . $minEntFixed;
				}
				if(strlen($viewDiaSaida) == 1){
					$viewDiaSaida = "0" . $diaSaida;
				}
				if(strlen($viewHoraSaida) == 1){
					$viewHoraSaida = "0" . $horaSaida;
				}
				if(strlen($viewMinSaida) == 1){
					$viewMinSaida = "0" . $minSaida;
				}
				
			//Calculo de tempo de login: Permitido ou Não =============
				if($diaSaida == "--"){
					$permitido = $sim;
				}
				else{
					$resetSet = "";
					$diaEnt = (Int)$diaEnt;
					$horaEnt = (Int)$horaEnt;
					$minEnt = (Int)$minEnt;
					$diaSaida = (Int)$diaSaida;
					$horaSaida = (Int)$horaSaida;
					$minSaida = (Int)$minSaida;
					
					if($dias == $diaSaida){
						$diferenca = $horas - $horaSaida;
					}
					else{
						$diferenca = $horas - ($horaSaida + 24);
					}
					
					if($diferenca <= 0 || $diferenca > 10){
						if($diferenca == 0){
							$difMinutos = $minutos - $minSaida;
							if($difMinutos <= 0){
								$permitido = $sim;
							}
							else{
								$permitido = $nao;
							}
						}
						elseif($diferenca >= -6 && $diferenca < 0){
							$permitido = $sim;
						}
						else{
							$permitido = $sim;
						}
					}
				}
			
			//Calculo de tempo restante================================
				//if($diaSaida != "--" && (($diaSaida >= $dias || $mesSaida != $meses) && ($diaSaida == $dias || $diaEnt == $dias))){
				if($diaSaida == $dias || $diaEnt == $dias){
					if ($mesEnt != $meses && $mesSaida != $meses){
						$totalEmMinutos = 370;
						$progresspercent = 0;
						$progress = 0;
						$progressTotal = 100;
						$progressColor = "progress-bar-info";
					} else {
						$restaMinutos = ($minSaida - $minutos);
						if($restaMinutos < 0){
							$restaMinutos = (60 + $restaMinutos);
							$menosHoras = ($horas - 1);
						}
						$restaHora = $horaSaida - $horas;
						if($restaHora < 0){
							$restaHora = (24 - $restaHora);
						}
						if(isset($menosHoras)){
							$restaHora = ($restaHora - 1);
						}
						if ($exTempo == 60){
							$progressTotal = 7 * 60;
						} elseif ($exTempo == 120){
							$progressTotal = 8 * 60;
						} else {
							$progressTotal = 6 * 60;
						}
						
						if($restaHora > 8){
							$restaHora = 6;
							$restaMinutos = 0;
							$plusIcon = "+ ";
							$progress = 0;
							$progressTotal = "100";
							$progressColor = "progress-bar-info";
						}
						if($restaHora < 0){
							$restaHora = 0;
							$restaMinutos = 0;
							$progress = 0;
							$progressTotal = "100";
							$progressColor = "progress-bar-info";
						}
						//Atribui cor ao tempo restante	
						if($restaHora >= 2){
							$colorAlert = "00ff00";
							$progressColor = "progress-bar-success";
						}
						if($restaHora == 1){
							$colorAlert = "ffdd00";
							$progressColor = "progress-bar-warning";
						}
						if($restaHora < 1){
							if($restaMinutos <= 10 || $restaHora < 0){
								$solicitaOn = "";
								$colorAlert = "ff0000";
								$progressColor = "progress-bar-danger";
							} else {
								$colorAlert = "ff7700";
								$progressColor = "progress-bar-danger";
							}
						}
						$tempoTotal = ($restaHora * 60);
						$totalEmMinutos = (Int)$restaMinutos + (Int)$tempoTotal;
						if ($totalEmMinutos < $progressTotal){
							$progress = ($progressTotal - $totalEmMinutos);
							$progresspercent = (($progressTotal - $totalEmMinutos)/$progressTotal) * 100;
							$progresspercent = intval($progresspercent);
						} else {
							$progress = 0;
							$progresspercent = 0;
							$progressColor = "progress-bar-info";
						}
						
						//Formata minutos
						if(strlen($restaMinutos) == 1){
							$restaMinutos = "0" . $restaMinutos;
						}
						//Valor a ser imprimido no HTML
						$tempoRestante = "<span style='color:#" . $colorAlert . "; font-size: 30px;'><b>" . $plusIcon . $restaHora . ":" . $restaMinutos . "</b></span>";
					}
				} else {
					$totalEmMinutos = 370;
					$progresspercent = 0;
					$progress = 0;
					$progressTotal = 100;
					$progressColor = "progress-bar-info";
				}
				if ($permitido == $nao){
					$tempoRestante = "<b>--:--</b>";
				}
				
				if ($solicitaLogoff != NULL && ($solicitaLogoff != "-" && $solicitaLogoff != "cancel")){
						$motivo = "";
						$optLogoff = explode('||', $solicitaLogoff, 2);
						$logoff = $optLogoff[0];
						$basess = $logoff;
						
						if ($logoff == "1"){$motivo = "Ausencia do Atestado de Retorno ao Trabalho";}
						else if($logoff == "2"){$motivo = "Comunix Phone inoperante";}
						else if($logoff == "3"){$motivo = "Direcionado para troca de P.A.";}
						else if($logoff == "4"){$motivo = "Fim do expediente";}
						else if($logoff == "5"){$motivo = "Liberado para audi&ecirc;ncia";}
						else if($logoff == "6"){$motivo = "Liberado para reunião na escola dos filhos";}
						else if($logoff == "7"){$motivo = "Pesquisa de satisfação inoperante";}
						else if($logoff == "8"){$motivo = "Redução Jornada Aviso Prévio";}
						else if($logoff == "9"){$motivo = "Realizar cobertura de posto do externo";}
						else if($logoff == "10"){$motivo = "Realizar treinamento no Externo";}
						else if($logoff == "11"){$motivo = "Serviço Externo";}
						else if($logoff == "12"){
							$optLogoff = explode('!!', $optLogoff[1], 2);
							$motivo = $optLogoff[1];
						}
				}
				if($dias != $diaEnt && $dias != $diaSaida){
					$forceHora = $horas + 6;
					$forceMinuto = $minutos;
					$forceDia = $dias;
					$forceMes = $meses;
					if($forceHora >= 24){
						$forceHora = $forceHora - 24;
						$forceDia++;
						if($forceDia > $mesPadrao){
							$forceDia = 1;
							$forceMes++;
							if($forceMes > 12){
								$forceMes = 1;
							}
						}
					}
					if($permitido = $sim){
						$dado["extensionattribute1"] = "0";
						$dado["extensionattribute2"] = "false";
						$dado["extensionattribute3"] = "-";
						$dado["extensionattribute10"] = $dias . "|" . $meses . "|" . $horas . "|" . $minutos;
						$dado["extensionattribute11"] = $forceDia . "|" . $forceMes . "|" . $forceHora . "|" . $forceMinuto;
						$dado["extensionattribute12"] = $dias . "|" . $meses . "|" . $horas . "|" . $minutos;
						
						$ldapC = ldap_mod_replace($lc, $dn, $dado);
					}
				}
				
			//==============================================================
				$html = $html.
					//Barra de tempo dos colaboradores-----
						"<div class='container'>".
							"<div class='row' style='position:relative; max-height:6px;'>".
								"<div class='progress' style='position:relative; top:5px; max-height:10px; background-color:#d0d0d0;'>".
									"<div class='progress-bar progress-bar-striped " . $progressColor . " active' id='progressView' role='progressbar' aria-valuenow='" . $progress . "' aria-valuemin='0' aria-valuemax='" . $progressTotal . "' style='width:" . $progresspercent . "%'>".
									"</div>".
								"</div>".
							"</div>".
						"</div>".
					//-------------------------------------
						"<div class='container infUser' id='" . $account . "'>".
							"<div class='row'>".
								"<div class='col-xs-12 col-sm-12 col-md-12'>".
									"<form class='form-horizontal' role='form' method='post' action='insert.php'>".
										"<div class='row'>".
											"<div class='col-xs-3 col-sm-3 col-md-3' align='left'>".
												"<b>Tempo restante: <br />" . $tempoRestante . "</b></p>".
											"</div>".
											"<div class='col-xs-3 col-sm-3 col-md-3' align='left'>".
												"<strong>".
												"<input type='text' name='search' id='search' class='form-control' value='" . $account . "' readonly style='max-height:40px;'></input>".
												"<input style='display:none;' type='text' name='database' id='database' class='form-control' value='" . $returnbase . "' readonly></input>".
												"</strong>".
											"</div>".
											"<div class='col-xs-3 col-sm-3 col-md-3 accountStatus' id='popup" . $account . "'>".
													"<p><u>" . $colaborador . "</u>&nbsp;&nbsp;".
											"</div>".
											"<div class='col-xs-3 col-sm-3 col-md-3'>";
												if($permitido == $sim && $diaSaida != "--"){
													$html = $html . "<div style='margin-top:5px;'><button " . $solicitaOn . " type='submit' class='btn btn-info btn-sm'>Solicitar Tempo</button></div>";
												} else {
													$html = $html . "<div style='margin-top:5px;'><button disabled class='btn btn-info btn-sm'>Solicitar Tempo</button></div>";
												}
											$html = $html . 	
											"</div>".
										"</div>".
										"<br />".
										"<div class='row userinformation' id=" . $account . "Details style=''>".
											"<div class='col-xs-4 col-sm-4 col-md-4'>".
												"<b><u>Entrada </u><i class='fa fa-sign-in'></i></b><br/> Dia <strong>" . $viewDiaEnt . "</strong> &agrave;s ".
												"<strong>" . $viewHoraEnt . "</strong>:".
												"<strong>" . $viewMinEnt . "</strong>".
											"</div>".
											"<div class='col-xs-4 col-sm-4 col-md-4'>".
												"<b><u>Saida </u><i class='fa fa-sign-out'></i></b><br /> Dia <strong>" . $viewDiaSaida . "</strong> &agrave;s ".
												"<strong>" . $viewHoraSaida . "</strong>:".
												"<strong>" . $viewMinSaida . "</strong>".
											"</div>".
											"<div class='col-xs-4 col-sm-4 col-md-4'>".
												"<b>Logon Permitido:</b> " . $permitido .
											"</div>".
										"</div>";
										if($newMessages){
											$html = $html . 
											"<a class='msgAlert' href='http://SCL.call.br/messages/index.php?act=" . $msgUser . "' target='_blank'>
												<div class='messages' align='center'>
													<span class='fontPlay'>
														Voc&ecirc; possui <b>" . $countMessages . "</b> nova(s) mensagen(s)!<br />
													</span>
													<div class='blink' style='padding-top:-10;'>
														<i class='fa fa-envelope fa-2x'></i>
													</div>
												</div>
											</a>";
										}
										
									$html = $html . 	
									"</form>".  
								"</div>".
							"</div>".
						"</div>";

			
			//=====================================================================================================================
			
			//Guarda tempo restante + HTML(resultado de pesquisa de 1 colaborador)
				$pageView = "<p style='display:none;'>" . (String)$totalEmMinutos . "</p>" . $html;
			//Limpa variável HTML
				$html = "";
			//Insere dados gravados no array
				array_push($resultArray, $pageView);
			} else {
				echo "<div align='center'><h2 style='color:#bb0000;' class='fontPlay'>Usu&aacute;rio n&atilde;o encontrado!</h2></div><br />";
			}
		}
	//Fim do laço FOR para cada resultado obtdo em pesquisa LDAP=================================================================
		if ($i == 0) {
			echo "<div align='center'><h2 style='color:#bb0000;' class='fontPlay'>Usu&aacute;rio n&atilde;o encontrado!</h2></div><br />".
					"<div class='return-left' align='center'>
						<strong>
							<a href='home.php' class='hoverLight return'>
								<i class='fa fa-chevron-left fa-4x'></i>
							</a>
						</strong>
					</div>
					<div class='active-return'>
						&nbsp;
					</div>";
		}
		ldap_close($lc);
		
	//Ordena valores do array HTML=============================================
	
		sort($resultArray);
	
	//Exibe resultado da pesquisa ordenados====================================
		if(strpos($dn, ",OU=Call,") != ""){
			for ($j = 0; $j < $i; $j++) {
				echo $resultArray[$j];
				#echo $dias . "|" . $meses . "|" . $horas . "|" . $minutos . "<br />";
				#echo $forceDia . "|" . $forceMes . "|" . $forceHora . "|" . $forceMinuto . "<br />";
				#echo $dias . "|" . $meses . "|" . $horas . "|" . $minutos . "<br />";
			}
		}			
		echo $html . "</div>";
		 			
	?>

		<!-- FIM DA CONEXÃO PHP-LDAP-->
		
		</div>
		<!--Início do footer op014.-->
		<footer style='max-height:30px; font-size:12px;'>
			<p>&copy; 2016. Equipe de Colabora&ccedil;&atilde;o de Servi&ccedil;os</p>
		</footer>
		<!--Fim do footer cl014.-->
		<script src='./Scripts/jquery-2.1.4.min.js'></script>
		<script src='./Scripts/bootstrap.min.js'></script>
		<script src='./scripts/animated.js'></script>
		<script src='scripts/jQueryRollPlugin/jRoll.min.js'></script>
		<script>
			setInterval(function() {
				location.reload();
			}, 60000);
		</script>
	</body>
	
</html>


