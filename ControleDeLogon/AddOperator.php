<!DOCTYPE html>
<html>
	<?php
		require_once("pageInfo.php");
		echo $htmlHeader;
	?>
	<body>
		<?php
			echo $htmlloading;
		?>
		<!-- Barra de navegação - menu op001.-->
		<div class="navbar navbar-inverse navbar-fixed-top" role="navegation">
			<div class="container-fluid">
			
				<div class="navbar-header container">
					<a href="./home.php" class="navbar-brand bottomUp">
						<span class="logo"><img src="./Images/Hexagon.png" id="hexIcon"></img></span>
					</a>
					<a href="./home.php" class="navbar-brand">
						<span id="imgLogon" style="font-size:34px; font-family: 'Play', Berlin Sans FB, Impact, Arial Black;"> Gest&atilde;o de Equipes </span><br />
					</a>
					<div class="navbar-text" style='position:absolute; right:20px; top:0px;'>
						<button type="button" id="coletiveAdd" class="btn btn-info btn-sm maisTempoChkd" data-toggle="modal" data-target="#extraTimemodal">
							<span>Adicionar tempo para todos os selecionados <span class="glyphicon glyphicon-plus"></span></span>
						</button>
						<a style="text-decoration: none" href="../Documentos/web/viewer.html?file=Manual_de_Uso_Jornada_REV02.pdf" target="_blank">
							<span id="tutorial" class="btn btn-info btn-sm noDecoration">
								<span>Ajuda <i class="fa fa-question"></i><span class="blink msgAlert">&nbsp;<b> ! </b>&nbsp;</span></span>
							</span>
						</a>
						<a href="logout.php">
							<button type="button" class="btn btn-danger btn-sm logoff">
								<span>Sair <span class="fa fa-sign-out"></span></span>
							</button>
						</a>
					</div>
					<!-- Fim do Botão de collapse cl002.-->
				</div>
				<div class="collapse navbar-collapse" id="navegation-collapse">
					<!-- .....  -->
				</div>
			</div>
		</div>
		<!-- Fim da Barra de navegação cl001.-->
		
		<div id="msgDiv" align="right">
			<div id="dialog" align="center">
				<span id="closebtn" onclick="clearMsgBox()"><i class="fa fa-times fa-2x"></i></span>
				<span id="msgHeader"></span><br />
				<hr />
				<span id="msgBody"></span><br />
			</div>
		</div>
		
		<!-- INÍCIO DA CONEXÃO PHP-LDAP-->

<?php
		
		require_once("restrict.php");
		require_once("ldapConnection.php");
		require_once("getDate.php");
		require_once("userInfo.php");
		require_once("json_consume.php");
		
		$returnbase = $_GET['database'];
		$base = $returnbase;
		
		date_default_timezone_set("America/Sao_Paulo");
	
	//Variáveis de controle 
		$nao = "<strong style='font-size:18px; color: #ff0000;'>N&atilde;o</strong>";
		$sim = "<strong style='font-size:18px; color: #00ff00;'>Sim</strong>";
		$exTempo = 0;
		$minhaEquipe = "";
		$minhaEquipeName = "";
		
	//Variáveis de apresentação HTML======================================
		$html = "";
		$resultArray = array();
		
	//Cabeçalho de edição============================================================================================	
		$head = "<div class='container resultManegarUser'>".
					"<h3><b class='fontPlay'>Ger&ecirc;ncia de equipes:</b></h3>".
					"<div class='row' style='margin-bottom:30px;'>".
						"<div class='col-xs-2 col-sm-2 col-md-1' align='right'>".
							"<span style='padding:35px;'>".
								$photo.
							"</span>".
						"</div>".
						"<div class='col-xs-6 col-sm-4 col-md-5 fontPlay' style='padding-left:25px;'>".
							"<b style='font-size:12px;'>Bem vindo: </b>" . $name . "<b><br />" . $dataAtual . "</b>".
						"</div>".
						"<div class='col-xs-4 col-sm-6 col-md-6 fontPlay' align='right'>".
							"<button class='btn btn-info btn-md' type='button'  id='EditModal'>Adicionar operador <i class='fa fa-plus fa-lg'></i></button>".
						"</div>".
					"</div>";
					
		$head =		$head . 
					"<div class='admHead'>".
						"<strong>".
						"<div class='row container'>".
							"<div class='col-xs-3 col-sm-3 col-md-2 ' align='center'>".
								"Solicita Tempo:".
								"<span id='allChecked' style='display:none;'></span>".
								"<span id='mycheckbox' style='display:none;'></span>".
							"</div>".
							"<div class='col-xs-9 col-sm-9 col-md-10'>".
								"<b>".
								"<div class='col-xs-3 col-sm-3 col-md-2'><p>Matr&iacute;cula:</p></div>".
								"<div class='col-xs-4 col-sm-4 col-md-7'><p>Operador:</p></div>".
								"<div class='col-xs-4 col-sm-4 col-md-3' align='left'><p>Hora extra:</p></div>".
								"</b>".
							"</div>".
						"</div>".
						"</strong>".
					"</div>";
				
		
		echo $head;
	//Fim - Cabeçalho de edição=======================================================================================
	
	//Filtro para pesquisa LDAP - Usuário ================================
		//Filtro = Todos os usuários com a o atributo de supervisor definido "extensionAttribute4"
		$filt = '(&(objectClass=User)(extensionAttribute4=' . $login . '))';
		//Search
		$sr = ldap_search($lc, $base, $filt);
		//Organiza
		$sort = ldap_sort($lc, $sr, 'name');
		//Recolhe entradas
		$info = ldap_get_entries($lc, $sr);
	//=================================================================================================================
	if ($info["count"] > 0){
		//Consome JSON==============================================
			$new_object = json_read($const_url);
		//==========================================================
		for ($i = 0; $i < $info["count"]; $i++) {
				$danger = false;
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
				$inExtraTime = "";
				$btnSet = "";
				$extraTimeSet = "";
				$upperStr = False;
				$solicitante = "&nbsp;&nbsp;<i style='color:#505050;' class='fa fa-minus fa-2x'></i>";
				$tempoRestante = "<b>--:--</b>";
				$popup = "";
				$minhaEquipe = $minhaEquipe . $account . "||";
				$minhaEquipeName = $minhaEquipeName . $colaborador . "||";
				
				//Trata JSON----------------------------------------------
				$operatorStatus = object_treatment($new_object, $account);
				if($operatorStatus == "available"){
					$operatorStatus = "#00ff00;";
					$popup = "Dispon&iacute;vel";
				} else if($operatorStatus == "break") {
					$operatorStatus = "#ffe000;";
					$popup = "Em pausa";
				} else if($operatorStatus == "busy") {
					$operatorStatus = "#ff0000;";
					$popup = "Ocupado";
				} else if($operatorStatus == "ring") {
					$operatorStatus = "#bba000;";
					$popup = "Chamando";
				} else if($operatorStatus == "notfound") {
					$operatorStatus = "#a9a9a9;";
					$popup = "Offline";
				}
				//--------------------------------------------------------
				
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
				if (($horaSaida != "--" && $horaEnt != "--") && $exTempo > 0){
					if($exTempo == 60){
						if($diaSaida != $dias){
							$extraTimeSet = $horaSaida + 23;
						} else {
							$extraTimeSet = $horaSaida - 1;
						}
						if($horas > $extraTimeSet){
							if($minutos >= $minSaida){
								$inExtraTime = "disabled";
							}
						}
					}
					else if($exTempo == 120){
						if($diaSaida != $dias){
							$extraTimeSet = $horaSaida + 22;
						} else {
							$extraTimeSet = $horaSaida - 2;
						}
						if($horas > $extraTimeSet){
							if($minutos >= $minSaida){
								$inExtraTime = "disabled";
							}
						}
					}
				}
				if ($solicitaLogoff != NULL && ($solicitaLogoff != "-" && $solicitaLogoff != "cancel")){
					//if ($diaSaida == $dias || $diaEnt ==$dias){
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
														
						$solicitante = "<span class='btn btn-danger btn-sm blinkObj' onclick='justificaLogoff(\"" . $motivo . "\", \"" . $returnbase . "\", \"" . $account . "\")'><span class='blink'><i class='fa fa-power-off fa-lg'></i></span></span>";
					//}
				}
				if ($solicitarTempo == "true" && $permitido != $nao){ //($permitido != $nao && $diaSaida != "--")
					if ($diaSaida == $dias || $diaEnt ==$dias){
						$solicitante = "<span class='blink'><i class='fa fa-clock-o fa-2x' style='border-radius:20px; box-shadow:0px 0px 20px 10px #ff0000; background-color:#ff0000; color:#fff;'></i></span>"; //<img src='./Images/redAlert.png'  class='blinkObj' />
						$upperStr = True;
					}
				}
				
			//Calculo de tempo restante================================
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
							//$permitido = $nao;
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
							$danger = true;
							if($restaMinutos <= 10 || $restaHora < 0){
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
						$tempoRestante = "<span style='color:#" . $colorAlert . "; font-size: 18px;'><b>" . $plusIcon . $restaHora . ":" . $restaMinutos . "</b></span>";
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
				
			//==============================================================
				$html = $html.
					//Barra de tempo dos colaboradores-----
						"<div class='row' style='position:relative; left:15px; max-height:6px; margin-bottom:0px; width:100%'>".
							"<div class='progress' style='position:relative; max-height:5px; background-color:#d0d0d0;'>".
								"<div class='progress-bar progress-bar-striped " . $progressColor . " active' id='progressView' role='progressbar' aria-valuenow='" . $progress . "' aria-valuemin='0' aria-valuemax='" . $progressTotal . "' style='width:" . $progresspercent . "%'>".
								"</div>".
							"</div>".
						"</div>".
						//Botão para esconder informações de usuário
						"<div align='center' class='row' style='widht:102%;'>".
							"<span id='" . $account . "_hideInfo' class='btn-info btn-xs btnInfo'><i class='fa fa-angle-double-up'></i></span>".
						"</div>".
					//-------------------------------------
						"<div class='informativoUser' id='" . $account . "'>".
							"<div class='row'>".
								"<div class='col-xs-1 col-sm-1 col-md-1' align='center'>".
									"<p style='font-size:12px;' class='mouseEnterView'><b>Solicita Tempo:</b></p>".
									"<span id='" . $account . "_maisTempo' class='maisTempo' style='top:20px;'>".
										$solicitante.
									"</span>".
								"</div>".
								"<div class='col-xs-11 col-sm-11 col-md-11'>".
									//"<form class='form-horizontal' role='form' name='returnResults' method='post' action='insert.php'>".
									"<form class='form-horizontal' role='form' name='returnResults' method='post' action='user.php'>".
										"<div class='row'>".
											"<div class='col-xs-2 col-sm-1 col-md-1' align='center'>".
												"<strong>".
												"<input type='checkbox' id='" . $account . "_Input' class='chkBox'></input>".
												"</strong>".
											"</div>".
											"<div class='col-xs-3 col-sm-3 col-md-2' align='left'>".
												"<strong>".
												"<input type='text' name='search' id='search' class='form-control' value='" . $account . "' readonly style='max-height:40px;'></input>".
												"<input style='display:none;' type='text' name='database' id='database' class='form-control' value='" . $returnbase . "' readonly></input>".
												"</strong>".
											"</div>".
											"<div class='col-xs-3 col-sm-3 col-md-5 accountStatus' id='popup" . $account . "'>".
											//Ícone de exibição de status do operador----
												"<span class='fa-stack fa-3x' id='Stpopup" . $account . "' style='position:absolute; display:none;'>".
													"<i class='fa fa-comment fa-stack-2x' style='color:#404040;'></i>".
													"<strong style='color:#ffffff; font-size:13px;' class='fa-stack-1x fa-stack-text fa-inverse'>" . $popup . "</strong>".
												"</span>".
											//-------------------------------------------
												"<p><i id='_popup" . $account . "' style='color:" . $operatorStatus . ";' class='fa fa-phone-square fa-lg '></i> <u>" . $colaborador . "</u>&nbsp;&nbsp;".
												"<b>(Tempo restante: </b>" . $tempoRestante . ")</p>".
											"</div>".
											"<div class='col-md-3' style='position:relative; top:10px;'>";
												
							
				if($permitido == $sim){
					$html = $html . "<div align='right' class='col-xs-2 col-sm-2 col-md-12' id=" . $account . "Extra style='color:#000; position:relative; top:0px; font-size:12px;'>
										<button type='submit' class='btn btn-success btn-sm'>Adicionar <span class='fa fa-plus fa-lg'></span></button>";
					if(($permitido == $sim && $diaSaida != "--") && $exTempo < 120){
						$btnSet = "";
						if(($diaEnt != $diaSaida) && (($diaSaida != $dias) && ($diaEnt != $dias))){
							$btnSet = "disabled";
						}
						if(($diaEnt == $diaSaida) && ($diaSaida != $dias)){
							$btnSet = "disabled";
						}
						
						$html = $html . "<button type='submit' " . $btnSet . " style='position:relative; margin-left:4px; margin-top:2px; display:none;' class='btn btn-success btn-xs adicionar' id='" . $account . "btnADD'>Adicionar <i class='fa fa-plus'></i></button>";
						if($exTempo == 60 && $restaHora < 1){
							$btnSet = "disabled";
						}
						if($exTempo == 120 && $restaHora < 2){
							$btnSet = "disabled";
						}
						$html = $html . "<span " . $btnSet . " style='position:relative; margin-left:4px; margin-top:2px; display:none;' onclick='negaSolicitacao(\"" . $account . "\", \"negate\")' class='btn btn-danger btn-xs'>Negar <i class='fa fa-times'></i></span>";					
					}
					elseif(($diaSaida == "--") && $exTempo < 120){
						$btnSet = "disabled";
						$html = $html . "<button type='submit' " . $btnSet . " style='position:relative; margin-left:4px; margin-top:2px; display:none;' class='btn btn-success btn-xs adicionar' id='" . $account . "btnADD'>Adicionar <i class='fa fa-plus'></i></button>".
										"<span " . $btnSet . " style='position:relative; margin-left:4px; margin-top:2px; display:none;' onclick='negaSolicitacao(\"" . $account . "\", \"negate\")' class='btn btn-danger btn-xs'>Negar <i class='fa fa-times'></i></span>";
					}
										
					elseif($exTempo >= 120){
						$html = $html . "<button type='submit' disabled class='btn btn-danger btn-xs proibido adicionar' id='" . $account . "btnADD' style='margin-top:5px; margin-left:4px; display:none;'>M&aacute;ximo de horas</button>";
					}
					else {
						$html = $html . "<button type='submit' disabled class='btn btn-danger btn-xs proibido adicionar' id='" . $account . "btnADD' style='margin-top:5px; margin-left:4px; display:none;'> Adicionar <i class='fa fa-times'></i></button>";
					}
					
					$html = $html . "</div>";
					
				}
				elseif($permitido == $nao){
					$html = $html . "<div align='right' class='col-xs-2 col-sm-2 col-md-12' id=" . $account . "Extra style='color:#000; position:relative; top:0px; font-size:12px;'>
										<button type='submit' disabled class='btn btn-danger btn-sm proibido adicionar' id='" . $account . "btnADD'>Logon Negado</button>
									</div>";
				}
							
				$html = $html.
						"</div>".
					"</div>".
					"<br />".
					"<div class='row mouseEnterView' id=" . $account . "Details>".
						"<div class='col-md-2'>".
							"<b>Entrada <i class='fa fa-sign-in'></i></b><br/> Dia <strong>" . $viewDiaEnt . "</strong> &agrave;s ".
							"<strong>" . $viewHoraEnt . "</strong>:".
							"<strong>" . $viewMinEnt . "</strong>".
						"</div>".
						"<div class='col-md-2'>".
							"<b>Saida <i class='fa fa-sign-out'></i></b><br /> Dia <strong>" . $viewDiaSaida . "</strong> &agrave;s ".
							"<strong>" . $viewHoraSaida . "</strong>:".
							"<strong>" . $viewMinSaida . "</strong>".
						"</div>".
						"<div class='col-md-3'>".
							"<b>Logon Permitido:</b> " . $permitido .
						"</div>".
						"<div class='col-xs-4 col-sm-4 col-md-4 col-md-push-5' align='right'>".
							"<button type='button' class='btn btn-danger btn-sm'>Remover operador <i class='fa fa-times'></i></button>".
						"</div>".
					"</form>".
				"</div>".  
			"</div>".
		"</div>".
	"</div>".
	"<br />";

			//Cobre lacunas de valores totais em minutos restantes de cada operador
			//Isso para chamar a função sort do PHP e manter o resultado em ordem de acordo com o tempo restante de cada operador.
				if($permitido == $nao){
					$totalEmMinutos = ($totalEmMinutos + 500);
				}
				elseif(strlen($totalEmMinutos) < 3){
					if(strlen($totalEmMinutos) == 2){
						$totalEmMinutos = "0" . $totalEmMinutos;
					}
					elseif(strlen($totalEmMinutos) == 1){
						$totalEmMinutos = "00" . $totalEmMinutos;
					}
				}
				elseif($upperStr){
					$totalEmMinutos = "000" . $totalEmMinutos;
				}
				if($danger && $upperStr){
					$totalEmMinutos = "0000" . $totalEmMinutos;
				}
				
				
			//=====================================================================================================================
			//Guarda tempo restante + HTML(resultado de pesquisa de 1 colaborador)
				$pageView = "<p style='display:none;'>" . (String)$totalEmMinutos . "</p>" . $html;
			//Limpa variável HTML
				$html = "";
			//Insere dados gravados no array
				array_push($resultArray, $pageView);
		}
		sort($resultArray);
				
		//Exibe resultado da pesquisa ordenados================================
			echo "<div class='resultBody'>";
			for ($j = 0; $j < $i; $j++) {
				echo $resultArray[$j];
			}
			echo "</div>";
	} else {
		echo "<div class='noUserGroup'><p style='color:#bb0000;'><b class='fontPlay'><i class='fa fa-times fa-lg'></i> N&atilde;o h&aacute; operadores listados para sua equipe!</b></p></div><br />";
	}
	ldap_close($lc);
?>
		<!-- FIM DA CONEXÃO PHP-LDAP-->
		</div>
		</div>
		</div>
		</div>
		</div>
		</div>
		<br />
		<div class="return-left" align="center">
			<strong>
				<a href='home.php' class="hoverLight return">
					<i class='fa fa-chevron-left fa-4x'></i>
				</a>
			</strong>
		</div>
		<div class="active-return">
			&nbsp;
		</div>
		<div class="modal fade" id="extraTimemodal" tabindex="-1" role="dialog" aria-labelledby="imgmodal-label" aria-hidden="true">
			<div class="modal-dialog modal-md">
				<div class="modal-content">
					<div class="modal-header modalBorders">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>Por favor informe a quantidade de hora extra a ser adicionado para os colaboradores:</h3>
					</div>
					<div class="modal-body" align="center">
						<form class='form-horizontal' name='variosUsers' id='variosUsers' role='form' method='post' action='insertToList.php'>								
							<h4><select name='timeNeeded' id='timeNeeded'>
								<option value='60'>45 Minutos Extras</option>
								<option value='120'>1 Hora e 45 Minutos Extras</option>
							</select></h4>
							<input style='display:none;' type='text' name='usersDatabase' id='usersDatabase' class='form-control' readonly></input>
							<input style='display:none;' type='text' name='usersCodes' id='usersCodes' class='form-control' readonly></input>
							
							<div style="margin-top: 50px;">
								<button type="submit" class="btn btn-success">Confirmar <span class="fa fa-check-circle fa-lg"></span></button>	
								<button type="button" id="cancel-coletiveAdd" class="btn btn-danger" data-dismiss="modal">Cancelar <span class="fa fa-times-circle fa-lg"></span></button>
							</div>	
						</form>
					</div>
					<div class="modal-footer modalBorders">
						<br />
					</div>
				</div>
			</div>
		</div>
		<div id="toTop">
			<span class="fa fa-chevron-up fa-2x" id="upToTop"></span><br />
		</div>
		
		<!--Início do footer op014.-->
		<footer class="bottomFooter" id="homefooter">
			<p>&copy; 2016. Equipe de Colabora&ccedil;&atilde;o de Servi&ccedil;os
			<span class="navbar-right" style="margin-right:15px;" align="right"><i class="fa fa-chrome"></i> <i class="fa fa-internet-explorer"></i> <i class="fa fa-firefox"></i> <i class="fa fa-opera"></i>
			</span></p>
		</footer>
		<!--Fim do footer cl014.-->

		<script src='./Scripts/jquery-2.1.4.min.js'></script>
		<script src='./Scripts/bootstrap.min.js'></script>
		<script src='./scripts/animated.js'></script>
		<script src='scripts/jQueryRollPlugin/jRoll.min.js'></script>
		<script>
			var seconds = 60;
			var miliseconds = 1000;

			//Aguarda o documento ser carregado
			$(document).ready(function(){
				//refresh automático da página a cada 'seconds' segundos
				var wait = (seconds * miliseconds);
				setInterval(function() {
					location.reload();
				}, wait);

			});
		</script>
	</body>
	
</html>


