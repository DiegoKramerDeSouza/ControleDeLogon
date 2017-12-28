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
		<?php 
			echo $pageNavbar;
		?>
		
		<!-- Fim da Barra de navegação cl001.-->
		<!-- Mensagens de tela-->
		<div id="msgDiv" align="right">
			<div id="dialog" align="center">
				<span id="closebtn" onclick="clearMsgBox()"><i class="fa fa-times fa-2x"></i></span>
				<span id="msgHeader"></span><br />
				<hr />
				<span id="msgBody"></span><br />
			</div>
		</div>
		<!-- DISPOSIÇÃO DOS DEMAIS CONTAÚDOS NOS LIMITES "CONTAINER"-->
		<div class="container" style="margin-top:20px;">
			
		
		<!-- INÍCIO DA CONEXÃO PHP-LDAP-->

	<?php
		
		require_once("restrict.php");
		require_once("getDate.php");
		require_once("userInfo.php");
		require_once("ldapConnection.php");
		require_once("json_consume.php");
		if($dbView != "DC=call,DC=br"){
			require_once("autocomplete.php");
		}
		
		$strUser = $_POST['search'];
		
		if(!isset($strUser)){
			$strUser = $_GET['search'];
		}
		
		date_default_timezone_set("America/Sao_Paulo");
		
		//Variáveis de controle 
		$nao = "<span class='btn btn-danger btn-xs' style='opacity:1;' disabled><b>N&atilde;o</b></span>";
		$sim = "<span class='btn btn-success btn-xs' style='opacity:1;' disabled><b>Sim</b></span>";
		$exTempo = 0;
		$base = $dbView;
		$userFound = false;
		
		echo "<div class='return-left' align='center'>
				<strong>
					<a href='home.php?filter=" . $_SESSION['equipeFilter'] . "' class='hoverLight return'>
						<i class='fa fa-chevron-left fa-4x'></i>
					</a>
				</strong>
			</div>
			<div class='active-return'>
				&nbsp;
			</div>";
				
		//Variáveis de apresentação HTML
		$html = "";
		$resultArray = array();

		if($lc){
			//Executa Binding de conta LDAP
			$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die ("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>N&atilde;o foi poss&iacute;vel conectar!<br /> Favor verificar seu usu&aacute;rio e senha e tente novamente.</strong><br /><br /><a href='.\index.php' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
		}
	//Cabeçalho da pesquisa============================================================================================	
		$head = "</div></div></div>".
				"<div class='container resultUser'>".
					"<form class='form-horizontal' role='form' method='post' action='user.php'>".
						"<div class='row'>".
							"<div class='col-xs-12 col-sm-12 col-md-3 col-md-push-9'>".
								"<div class='form-group'>".
									"<div class='input-group'>".
										"<input type='text' name='search' id='search' autofocus class='form-control' placeholder=' Matr&iacute;cula (EX: c22123)'></input>".
										"<div class='input-group-btn'>".
											"<button id='searchBar' type='submit' class='btn btn-primary'>".
												"<span class='glyphicon glyphicon-search'></span>".
											"</button>".
										"</div>".
									"</div>".
								"</div>".
					"</form>".
				"</div>".
				"<spam class='col-md-6 col-md-pull-3'>".
					"<h2><b class='fontPlay'><i class='fa fa-clock-o'></i> Hor&aacute;rio de login dos operadores:</b></h2>".
				"</spam></div>".
				"<div class='row'>".
					"<div class='col-xs-3 col-sm-2 col-md-1'>".
						"<span style='padding:35px;'>".
							$photo.
						"</span>".
					"</div>".
					"<div class='col-xs-6 col-sm-6 col-md-6 fontPlay' style='padding-left:25px;'>".
						"<b style='font-size:12px;'>Bem vindo: </b>" . $name . "<b><br />" . $dataAtual . "</b>".
					"</div>".
				"</div>".
				"<h4 style='color:#a22;' class='fontPlay'><b>ATEN&Ccedil;&Atilde;O:</b> No m&aacute;ximo 1 hora e 45 minutos para cada operador por dia.</h4>".
				"<br />".
				"<div class='container'>".
					"<div class='row'> <strong>".
						"<div class='col-xs-1 col-sm-1 col-md-1 ' align='center'>".
						"</div>".
						"<div class='col-xs-11 col-sm-11 col-md-11 admHead'>".
							"<div class='col-xs-3 col-sm-3 col-md-3'><p>Matr&iacute;cula:</p></div>".
							"<div class='col-xs-4 col-sm-4 col-md-4'><p>Operador:</p></div>".
							"<div class='col-xs-4 col-sm-4 col-md-5' align='center'><p>Hora extra:</p></div>".
						"</div>".
					"</strong></div>".
				"</div>";
		
		echo $head;
	//Fim - Cabeçalho da pesquisa=======================================================================================
		
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
	
	//Consome JSON==============================================
		if($depto != "TI"){
			$new_object = json_read($const_url);
		}
	//==========================================================
		
	//Listagem de usuários retornados ==========================
	//Início do laço FOR para cada resultado obtdo em pesquisa LDAP==============================================================
		for ($i = 0; $i < $info["count"]; $i++) {
			$permitido = $nao;
			$account = $info[$i]["samaccountname"][0];
			$colaborador = $info[$i]["cn"][0];
			$dn = $info[$i]["distinguishedname"][0];
			$colablen = strlen($colaborador);
			$returnex = explode(',', $dn, 2);
			$returnbase = $returnex[1];
			$exTempo = NULL;
			$solicitarTempo = NULL;
			$menosHoras = NULL;
			$plusIcon = "";
			$upperStr = False;
			$inExtraTime = "";
			$extraTimeSet = "";
			$btnSet = "";
			$resetSet = "disabled";
			$solicitante = "&nbsp;&nbsp;<i style='color:#505050; margin-top:20px;' class='fa fa-minus fa-2x'></i>";
			$tempoRestante = "<b>--:--</b>";
			$popup = "";
			$userFound = false;
			
			if(strpos($dn, ",OU=Call,") != ""){
				if($depto != "TI"){
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
				}
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
				$sameday = 0;
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
							if($diaSaida == $dias){
								$restaHoraDes = (11 + $restaHora);
								if($restaHoraDes <= 0){
									if($minSaida <= $minutos){
										$permitido = $sim;
										$sameday = 1;
									} else {
										$permitido = $nao;
										$sameday = 2;
									}
								}
							} else {
								$restaHora = (24 - $restaHora);
							}
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
							if($restaMinutos <= 10 || $restaHora < 0 ){
								$colorAlert = "ff0000";
								$progressColor = "progress-bar-danger";
								if($sameday == 1){
									$progress = 0;
									$progressTotal = "0";
									$tempoRestante = "<b>--:--</b>";
								}
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
						if($sameday != 1){
							$tempoRestante = "<span style='color:#" . $colorAlert . "; font-size: 18px;'><b>" . $plusIcon . $restaHora . ":" . $restaMinutos . "</b></span>";
						}
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
				if (($diaSaida != "--" && $diaEnt != "--") && $exTempo > 0){
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
									$resetSet = "disabled";
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
									$resetSet = "disabled";
								}
							}
						}
					}
				}
				if ($solicitarTempo == "true" && $permitido != $nao){
					if ($diaSaida == $dias || $diaEnt ==$dias){
						$solicitante = $solicitante = "<span class='blink'><i class='fa fa-clock-o fa-2x' style='border-radius:20px; box-shadow:0px 0px 20px 10px #ff0000; background-color:#ff0000; color:#fff; margin-top:20px;'></i></span>";
						$upperStr = True;
					}
				}
				
			//==============================================================
				$html = $html.
					//Barra de tempo dos colaboradores-----
						"<div class='row container' style='position:relative; left:0px; max-height:6px; width:105%'>".
							"<div class='progress' style='position:relative; top:-4px; max-height:10px; background-color:#d0d0d0;'>".
								"<div class='progress-bar progress-bar-striped " . $progressColor . " active' id='progressView' role='progressbar' aria-valuenow='" . $progress . "' aria-valuemin='0' aria-valuemax='" . $progressTotal . "' style='width:" . $progresspercent . "%'>".
								"</div>".
							"</div>".
						"</div>".
					//-------------------------------------
						"<div class='container infUser' id='" . $account . "' style='margin-bottom:100px;'>".
							"<div class='row'>".
								"<div class='col-xs-1 col-sm-1 col-md-1' align='center'>".
									"<span class='maisTempo'>".
										$solicitante.
									"</span><br />".
								"</div>".
								"<div class='col-xs-11 col-sm-11 col-md-11'>".
									"<form class='form-horizontal' role='form' method='post' action='insert.php'>".
										"<div class='row'>".
											"<div class='col-xs-4 col-sm-4 col-md-3' align='left'>".
												"<strong>".
												"<input type='text' name='search' id='search' class='form-control' value='" . $account . "' readonly style='max-height:40px;'></input>".
												"<input style='display:none;' type='text' name='database' id='database' class='form-control' value='" . $returnbase . "' readonly></input>".
												"</strong>".
											"</div>".
											"<div class='col-xs-8 col-sm-3 col-md-5 accountStatus' id='popup" . $account . "'>";
												if($depto != "TI"){
													//Ícone de exibição de status do operador----
													$html = $html.
														"<span class='fa-stack fa-3x' id='Stpopup" . $account . "' style='position:absolute; display:none;'>".
															"<i class='fa fa-comment fa-stack-2x' style='color:#404040;'></i>".
															"<strong style='color:#ffffff; font-size:14px;' class='fa-stack-1x fa-stack-text fa-inverse'>" . $popup . "</strong>".
														"</span>".
														"<p><i id='_popup" . $account . "' style='color:" . $operatorStatus . ";' class='fa fa-phone-square fa-lg '></i> ";
													//-------------------------------------------
												}
												$html = $html.
													"<u>" . $colaborador . "</u>&nbsp;&nbsp;".
													"<b>(Tempo restante: </b>" . $tempoRestante . ")</p>".
											"</div>".
											"<div class='col-xs-12 col-sm-5 col-md-4' style='margin-top:10px;'>";
												
							
				if($permitido == $sim){
					$html = $html . "<div  class='col-xs-12' id=" . $account . "Extra style='color:#000;'>";
					
					if($exTempo < 60){
						$html = $html . "<select id='tempo' name='tempo' class='form-control' style='position:relative; margin-left:4px;'>".
											"<option value='60'>45 minutos</option>".
											"<option value='120'>1 hora e 45 minutos</option>".
										"</select>";
					}
					if($exTempo == 60){
						$html = $html . "<select id='tempo' name='tempo' class='form-control' style='position:relative; margin-left:4px;'>".
											"<option value='60'>1 hora</option>".
										"</select>";
					}
					
					if($exTempo > 0){
						if($operationAccount){
							if($exTempo == 60 && $restaHora < 1){
								$html = $html .
									"<button id='cancelExTm' style='margin-top:5px; margin-left:4px;' " . $inExtraTime . " class='btn btn-info btn-sm' disabled>Cancelar Hora Extra <i class='fa fa-ban'></i></button>".
									"<br />";
							}
							elseif ($exTempo == 120 && $restaHora < 2){
								$html = $html .
									"<button id='cancelExTm' style='margin-top:5px; margin-left:4px;' " . $inExtraTime . " class='btn btn-info btn-sm' disabled>Cancelar Hora Extra <i class='fa fa-ban'></i></button>".
									"<br />";
							} else {
								$html = $html .
									"<button id='cancelExTm' type='button' style='margin-top:5px; margin-left:4px;' " . $inExtraTime . " class='btn btn-info btn-sm' onclick='negaSolicitacao(\"" . $account . "\", \"cancel\")'>Cancelar Hora Extra <i class='fa fa-ban'></i></button>".
									"<br />";
							}
						} else {
							$html = $html .
									"<button id='cancelExTm' type='button' style='margin-top:5px; margin-left:4px;' " . $inExtraTime . " class='btn btn-info btn-sm' onclick='negaSolicitacao(\"" . $account . "\", \"cancel\")'>Cancelar Hora Extra <i class='fa fa-ban'></i></button>".
									"<br />";
						}
					} else {
						$html = $html . 
								"<br/>";
					}
					
					if(($permitido == $sim && $diaSaida != "--") && $exTempo < 120){
						$btnSet = "";
						$resetSet = "";
						if(($diaEnt != $diaSaida) && (($diaSaida != $dias) && ($diaEnt != $dias))){
							$btnSet = "disabled";
						}
						if(($diaEnt == $diaSaida) && ($diaSaida != $dias)){
							$btnSet = "disabled";
						}
						if(!$operationAccount){
							$btnSet = "";
						}
						$html = $html . "<button type='submit' " . $btnSet . " style='position:relative; margin-left:4px; margin-top:2px;' class='noAct btn btn-success bgdgreen btn-sm adicionar' id='" . $account . "btnADD'>Adicionar <i class='fa fa-plus'></i></button>";
						if($exTempo == 60 && $restaHora < 1){
							$btnSet = "disabled";
						}
						if($exTempo == 120 && $restaHora < 2){
							$btnSet = "disabled";
						}
						$html = $html . "<button type='button' " . $btnSet . " id='negate' style='position:relative; margin-left:4px; margin-top:2px;' onclick='negaSolicitacao(\"" . $account . "\", \"negate\")' class='btn btn-danger btn-sm'>Negar <i class='fa fa-times'></i></button>";					
										
					} elseif(($diaSaida == "--") && $exTempo < 120){
						$btnSet = "disabled";
						if(!$operationAccount && $diaSaida != "--"){
							$btnSet = "";
						}
						$html = $html . "<button " . $btnSet . " style='position:relative; margin-left:4px; margin-top:2px;' class='btn btn-success bgdgreen btn-sm adicionar' id='" . $account . "btnADD'>Adicionar <i class='fa fa-plus'></i></button>".
										"<button type='button' " . $btnSet . " type='button' id='negate' style='position:relative; margin-left:4px; margin-top:2px;' onclick='negaSolicitacao(\"" . $account . "\", \"negate\")' class='btn btn-danger btn-sm'>Negar <i class='fa fa-times'></i></button>";
					} elseif($exTempo >= 120){
						$html = $html . "<button disabled class='btn btn-danger btn-sm proibido' style='margin-top:5px; margin-left:4px;'>M&aacute;ximo de 2 horas atingido</button>";
					}
					else{
						$html = $html . "<button disabled class='btn btn-danger btn-sm proibido' style='margin-top:5px; margin-left:4px;'> Adicionar <i class='fa fa-close'></i></button>";
					}
					$html = $html . "</div>";
					
				} elseif($permitido == $nao){
					if(!$operationAccount){
						$btnSet = "";
						
						if($exTempo != 120){
							if($exTempo == 60){
								$html = $html . "<select id='tempo' name='tempo' class='form-control' style='position:relative; margin-left:4px;'>".
												"<option value='120'>1 hora e 45 minutos</option>".
												"</select>";
							} else {
								$html = $html . "<select id='tempo' name='tempo' class='form-control' style='position:relative; margin-left:4px;'>".
												"<option value='60'>45 minutos</option>".
												"<option value='120'>1 hora e 45 minutos</option>".
												"</select>";
							}
							if($exTempo > 0){
								$html = $html . "<button type='button' style='margin-top:5px; margin-left:4px;' class='btn btn-info btn-sm' onclick='negaSolicitacao(\"" . $account . "\", \"cancel\")'>Cancelar Hora Extra <i class='fa fa-ban'></i></button>";
							}
							$html = $html . 
											"<button type='submit' " . $btnSet . " style='position:relative; margin-left:4px; margin-top:2px;' class='btn btn-success bgdgreen btn-sm adicionar' id='" . $account . "btnADD'>Adicionar <i class='fa fa-plus'></i></button>".
											"<button type='button' " . $btnSet . " id='negate' style='position:relative; margin-left:4px; margin-top:2px;' onclick='negaSolicitacao(\"" . $account . "\", \"negate\")' class='btn btn-danger btn-sm'>Negar <i class='fa fa-times'></i></button>";
						} else {
							$html = $html . "<button type='button' style='margin-top:5px; margin-left:4px;' class='btn btn-info btn-sm' onclick='negaSolicitacao(\"" . $account . "\", \"cancel\")'>Cancelar Hora Extra <i class='fa fa-ban'></i></button>";
							$html = $html . "<button type='submit' disabled class='btn btn-danger btn-sm proibido' style='margin-top:5px; margin-left:4px;'>M&aacute;ximo de 2 horas atingido</button>";
						}
						
					} else {
						$html = $html.
							"<div align='left' class='col-xs-12 col-sm-3 col-md-10' id=" . $account . "Extra style='color:#000; position:relative; top:0px; font-size:12px;'>".
								"<button type='submit' style='margin-left:25px; margin-top:25px;' disabled class='btn btn-danger btn-md proibido' id='" . $account . "btnADD'>Logon Negado</button>".
							"</div>";
					}
				}
							
				$html = $html.
						"</div>".
					"</div>".
					"<br />".
					"<div class='row userinformation container' id=" . $account . "Details style='margin-top:20px;'>".
						"<div class='col-xs-6 col-sm-4 col-md-3'>".
							"<b class='green'><u>Entrada </u><i class='fa fa-sign-in'></i></b><br/> Dia <strong>" . $viewDiaEnt . "</strong> &agrave;s ".
							"<strong>" . $viewHoraEnt . "</strong>:".
							"<strong>" . $viewMinEnt . "</strong>".
						"</div>".
						"<div class='col-xs-6 col-sm-4 col-md-3'>".
							"<b class='red'><u>Saida </u><i class='fa fa-sign-out'></i></b><br /> Dia <strong>" . $viewDiaSaida . "</strong> &agrave;s ".
							"<strong>" . $viewHoraSaida . "</strong>:".
							"<strong>" . $viewMinSaida . "</strong>".
						"</div>".
						"<div class='col-xs-12 col-sm-4 col-md-6'>".
							"<b>Logon Permitido:</b> " . $permitido .
						"</div>".
					"</form>".
				"</div>".  
			"</div>".
		"</div>".
	"</div>";

			//Cobre lacunas de valores totais em minutos restantes de cada operador
			//Isso para chamar a função sort do PHP e manter o resultado em ordem de acordo com o tempo restante de cada operador.
				/*
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
				}*/
			//=====================================================================================================================
			
			//Guarda tempo restante + HTML(resultado de pesquisa de 1 colaborador)
				$pageView = "<p style='display:none;'>" . (String)$totalEmMinutos . "</p>" . $html .
							"<div class='return-left' align='center'>
								<strong>
									<a href='home.php?filter=" . $_SESSION['equipeFilter'] . "' class='hoverLight return'>
										<i class='fa fa-chevron-left fa-4x'></i>
									</a>
								</strong>
							</div>
							<div class='active-return'>
								&nbsp;
							</div>";
			//Limpa variável HTML
				$html = "";
			//Insere dados gravados no array
				array_push($resultArray, $pageView);
			}
		}
		
	//Fim do laço FOR para cada resultado obtdo em pesquisa LDAP=================================================================
	
		if ($i == 0) {
			$userFound = true;
			$resetSet = "disabled";
			$operationAccount = true;
			echo "<div align='center'><h2 style='color:#bb0000;' class='fontPlay'>Usu&aacute;rio n&atilde;o encontrado!</h2></div><br />".
					"<div class='return-left' align='center'>
						<strong>
							<a href='home.php?filter=" . $_SESSION['equipeFilter'] . "' class='hoverLight return'>
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
		if(!$userFound){
			if(strpos($dn, ",OU=Call,") != ""){
				for ($j = 0; $j < $i; $j++) {
					echo $resultArray[$j];
				}
			}
		}
		if(!$operationAccount){
			if($resetSet != "disabled"){
				$html = $html . 
					"<div align='center' class='marginlimiter'>
						<form name='cleanUser' class='form-horizontal' role='form' method='post' action='eraser.php'>
							<div class='row' style='width:80%; margin-bottom:5px;'>
								<div class='col-md-3' align='right' style:'top:50px;'><label class='fontPlay'>Informe o Motivo:</label></div>
								<div class='col-md-9'><input type='text' name='motivacao' id='motivacao' class='form-control' required placeholder='N&uacute;mero do chamado e justificativa do solicitante.'></input></div>
								<input type='text' name='User' style='display:none;' value='" . $account . "'></input>
							</div>
								<button type='submit' id='clean' class='btn btn-success bgdgreen btn-md clean'>Resetar registros de logon e logoff do colaborador <i class='fa fa-edit'></i></button>
							</div>
						</form>";
			} else {
				$html = $html . 
					"<div align='center' class='marginlimiter'>
						<div class='row' style='width:80%; margin-bottom:5px;'>
							<div class='col-md-3' align='right'><label class='fontPlay'>Informe o Motivo:</label></div>
							<div class='col-md-9'><input type='text' readonly name='motivacao' id='motivacao' class='form-control' placeholder='N&uacute;mero do chamado e justificativa do solicitante.' style='background-color:#d0d0d0;'></input></div>
						</div>
							<button id='cleanNot' disabled class='btn btn-success bgdgreen btn-md clean'>Resetar registros de logon e logoff do colaborador <i class='fa fa-edit'></i></button>
					</div>";
			}		
					
			echo $html . "</div>";
		}
								
	?>
		<!-- FIM DA CONEXÃO PHP-LDAP-->
		</div>
		<!--Início do footer op014.-->
		<footer class="fixed-botom bottomFooter" id="homefooter">
			<?php 
				echo $pageFooter;
			?>
		</footer>
		<!--Fim do footer cl014.-->

		<script src='./Scripts/jquery-2.1.4.min.js'></script>
		<script src='./Scripts/bootstrap.min.js'></script>
		<script src='./scripts/animated.js'></script>
		<script src='./scripts/jQueryRollPlugin/jRoll.min.js'></script>
		<script src='./scripts/jquery-ui.js'></script>
				
	<?php
		if($base != "DC=call,DC=br"){
			echo '<script>
					$(function(){
						$("#search").autocomplete({
							source: ' . json_encode($_SESSION['users']) . ', minLength: 3
						});
					});
				</script>';
		}
	?>
				
	</body>
	
</html>


