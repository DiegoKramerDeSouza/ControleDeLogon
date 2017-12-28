
<?php
	require_once("restrict.php");
	require_once("getDate.php");
	require_once("userInfo.php");
	require_once("ldapConnection.php");
	require_once("json_consume.php");
	
	$filt = $_SESSION['ldapFilter'];
	$resultArray = array();
	$html = "";
	$returnbaseUser = $dbView;
	$sr = ldap_search($lc, $dbView, $filt);
	$sort = ldap_sort($lc, $sr, 'name');
	$info = ldap_get_entries($lc, $sr);
	if($depto != "TI"){
		//Consome JSON==============================================
		$new_object = json_read($const_url);
	}
	//Listagem de usuários retornados ==========================
	for ($i = 0; $i < $info["count"]; $i++) {
		$danger = false;
		$permitido = $nao;
		$account = $info[$i]["samaccountname"][0];
		$matriculas_arr[] = array("label" => $info[$i]["samaccountname"][0] . " - " . $info[$i]["name"][0], "value" => $info[$i]["samaccountname"][0]);
		$colaborador = $info[$i]["cn"][0];
		$dn = $info[$i]["distinguishedname"][0];
		$colablen = strlen($colaborador);
		$returnex = explode(',', $dn, 2);
		$returnbase = $returnex[1];
		$exTempo = NULL;
		$solicitarTempo = NULL;
		$menosHoras = NULL;
		$plusIcon = "";
		$inExtraTime = "";
		$btnSet = "onclick='callLoading()'";
		$extraTimeSet = "";
		$upperStr = False;
		$solicitante = "&nbsp;&nbsp;<i style='color:#505050; margin:5px; top:5px;' class='fa fa-minus fa-2x'></i>";
		$tempoRestante = "<b>--:--</b>";
		$popup = "";
		
		//Trata JSON----------------------------------------------
		$operatorStatus = object_treatment($new_object, $account);
		if($operatorStatus == "available"){
			$operatorStatus = "#00ff00;";
		} else if($operatorStatus == "break") {
			$operatorStatus = "#ffe000;";
		} else if($operatorStatus == "busy") {
			$operatorStatus = "#ff0000;";
		} else if($operatorStatus == "ring") {
			$operatorStatus = "#bba000;";
		} else if($operatorStatus == "notfound") {
			$operatorStatus = "#a9a9a9;";
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
		if ($solicitarTempo == "true" && $permitido != $nao){
			if ($diaSaida == $dias || $diaEnt ==$dias){
				if($exTempo < 120){
					$solicitante = "<span class=''><i class='fa fa-clock-o fa-2x red' style='position:relative; margin:5px; top:5px;'></i></span>"; //<img src='./Images/redAlert.png'  class='blinkObj' />
					$upperStr = True;
				}
			}
		}
		//Calculo de tempo restante================================
		$sameday = 0;
		if($diaSaida == $dias || $diaEnt == $dias){
			if ($mesEnt != $meses && $mesSaida != $meses){
				$totalEmMinutos = 370;
				$progresspercent = 0;
				$progress = 0;
				$progressTotal = 100;
				$progressColor = "progress-bar-info bglblue";
			} else {
				$restaMinutos = ($minSaida - $minutos);
				if($restaMinutos < 0){
					$restaMinutos = (60 + $restaMinutos);
					$menosHoras = ($horas - 1);
				}
				$restaHora = $horaSaida - $horas;
				if($restaHora < 0){
					if($diaSaida == $dias){
						//$sameday = 1;
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
					$progressColor = "progress-bar-info bgblue";
				}
				if($restaHora < 0){
					$restaHora = 0;
					$restaMinutos = 0;
					$progress = 0;
					$progressTotal = "100";
					$progressColor = "progress-bar-info bgblue";
				}
				//Atribui cor ao tempo restante	
				if($restaHora >= 2){
					$colorAlert = "00ff00";
					$progressColor = "progress-bar-success bggreen-bar";
				}
				if($restaHora == 1){
					$colorAlert = "ffdd00";
					$progressColor = "progress-bar-warning bgyellow-bar";
				}
				if($restaHora < 1){
					$danger = true;
					if($restaMinutos <= 10 || $restaHora < 0){
						$colorAlert = "ff0000";
						$progressColor = "progress-bar-danger bgred-bar";
						if($sameday == 1){
							$progress = 0;
							$progressTotal = "0";
							$tempoRestante = "<b>--:--</b>";
						}
					} else {
						$colorAlert = "ff7700";
						$progressColor = "progress-bar-danger bgred-bar";
					}
				}
				$tempoTotal = ($restaHora * 60);
				$totalEmMinutos = (Int)$restaMinutos + (Int)$tempoTotal;
				if($sameday == 1){
					$totalEmMinutos = 370;
				}
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
				//Valor a ser impresso no HTML
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
		//==============================================================
		$html = $html.
			//Barra de tempo dos colaboradores-----
			"<div class='row' style='position:relative; left:15px; bottom:-3px; max-height:6px; margin-bottom:0px; width:100%'>".
				"<div class='progress' style='position:relative; max-height:3px; background-color:#d0d0d0;'>".
					"<div class='progress-bar " . $progressColor . " active' id='progressView' role='progressbar' aria-valuenow='" . $progress . "' aria-valuemin='0' aria-valuemax='" . $progressTotal . "' style='width:" . $progresspercent . "%'>".
					"</div>".
				"</div>".
			"</div>".
			//-------------------------------------
			"<div class='informativoUser bgdblue' id='" . $account . "'>".
				"<div class='row'>".
					"<div class='col-xs-1' align='right'>".
						"<span id='" . $account . "_maisTempo' class='maisTempo' style=''>".
							$solicitante.
						"</span>".
					"</div>".
					"<div class='col-xs-11'>".
						"<form class='form-horizontal' role='form' name='returnResults' method='post' action='user.php'>".
							"<div class='row'>".
								"<div class='col-xs-4 col-sm-4 col-md-2' align='left'>".
									"<strong>".
									"<input type='text' name='search' id='search' class='form-control' value='" . $account . "' readonly style='max-height:40px;'></input>".
									"<input style='display:none;' type='text' name='database' id='database' class='form-control' value='" . $returnbase . "' readonly></input>".
									"</strong>".
								"</div>".
								"<div class='col-xs-8 col-sm-8 col-md-5 accountStatus' id='popup" . $account . "'>".
									"<p><i id='_popup" . $account . "' style='color:" . $operatorStatus . ";' class='fa fa-phone-square fa-lg '></i> <u>" . $colaborador . "</u>&nbsp;&nbsp;".
									"<b><br />Tempo restante: </b>" . $tempoRestante . " <b>Logon Permitido:</b> " . $permitido . "</p>" .
								"</div>".
								"<div class='col-xs-12 col-md-5' align='right'>";
								
						if($permitido == $sim){
							$html = $html . "<div align='' class='col-xs-12' id='" . $account . "Extra' style='color:#000; padding-top:5px;'>
												<button onclick='callLoading()' type='submit' id=" . $account . "plusTime class='btn btn-success bgdgreen btn-sm'>Adicionar <span class='fa fa-plus fa-lg'></span></button>";
							if(($permitido == $sim && $diaSaida != "--") && $exTempo < 120){
								$btnSet = "onclick='callLoading()'";
								if(($diaEnt != $diaSaida) && (($diaSaida != $dias) && ($diaEnt != $dias))){
									$btnSet = "disabled";
								}
								if(($diaEnt == $diaSaida) && ($diaSaida != $dias)){
									$btnSet = "disabled";
								}								
								$html = $html . "<button type='submit' " . $btnSet . " style='position:relative; margin-left:4px; margin-top:2px; display:none;' class='btn btn-success bgdgreen btn-xs adicionar' id='" . $account . "btnADD'>Adicionar <i class='fa fa-plus'></i></button>";
								if($exTempo == 60 && $restaHora < 1){
									$btnSet = "disabled";
								}
								if($exTempo == 120 && $restaHora < 2){
									$btnSet = "disabled";
								}
								$html = $html . "<span " . $btnSet . " style='position:relative; margin-left:4px; margin-top:2px; display:none;' onclick='negaSolicitacao(\"" . $account . "\", \"negate\")' class='btn btn-danger btn-xs'>Negar <i class='fa fa-times'></i></span>";					
							} elseif(($diaSaida == "--") && $exTempo < 120){
								$btnSet = "disabled";
								$html = $html . "<button type='submit' " . $btnSet . " style='position:relative; margin-left:4px; margin-top:2px; display:none;' class='btn btn-success bgdgreen btn-xs adicionar' id='" . $account . "btnADD'>Adicionar <i class='fa fa-plus'></i></button>".
												"<span " . $btnSet . " style='position:relative; margin-left:4px; margin-top:2px; display:none;' onclick='negaSolicitacao(\"" . $account . "\", \"negate\")' class='btn btn-danger btn-xs'>Negar <i class='fa fa-times'></i></span>";
							} elseif($exTempo >= 120){
								$html = $html . "<button type='submit' disabled class='btn btn-danger btn-xs proibido adicionar' id='" . $account . "btnADD' style='margin-top:5px; margin-left:4px; display:none;'>M&aacute;ximo de horas</button>";
							} else {
								$html = $html . "<button type='submit' disabled class='btn btn-danger btn-xs proibido adicionar' id='" . $account . "btnADD' style='margin-top:5px; margin-left:4px; display:none;'> Adicionar <i class='fa fa-times'></i></button>";
							}
							$html = $html . "</div>";					
						} elseif($permitido == $nao){
							$html = $html . "<div align='right' class='col-xs-2 col-sm-2 col-md-12' id=" . $account . "Extra style='color:#000; position:relative; top:0px; font-size:12px;'>
												<button type='submit' disabled class='btn btn-danger btn-sm proibido adicionar' id='" . $account . "btnADD'>Logon Negado</button>
											</div>";
						}
							$html = $html.
								"</div>".
							"</div>".		
						"</form>".
					"</div>".
				"</div>".
			"</div>".
		"<br />";

		//Cobre lacunas de valores totais em minutos restantes de cada operador
		//Isso para chamar a função sort do PHP e manter o resultado em ordem de acordo com o tempo restante de cada operador.
		if($permitido == $nao){
			$totalEmMinutos = ($totalEmMinutos + 500);
		} elseif(strlen($totalEmMinutos) < 3){
			if(strlen($totalEmMinutos) == 2){
				$totalEmMinutos = "0" . $totalEmMinutos;
			} elseif(strlen($totalEmMinutos) == 1){
				$totalEmMinutos = "00" . $totalEmMinutos;
			}
		} elseif($upperStr){
			$totalEmMinutos = "000" . $totalEmMinutos;
		} 
		
		if($danger && $upperStr){
			$totalEmMinutos = "0000" . $totalEmMinutos;
		}
		
		//=====================================================================================
		//Guarda tempo restante + HTML(resultado de pesquisa de 1 colaborador)
		$pageView = "<p style='display:none;'>" . (String)$totalEmMinutos . "</p>" . $html;
		//Limpa variável HTML
		$html = "";
		//Insere dados gravados no array
		array_push($resultArray, $pageView);
	}
	//********Fim do laço FOR para cada resultado obtdo em pesquisa LDAP********
	//Ordena valores do array HTML=============================================
	sort($resultArray);
	if(isset($matriculas_arr) && count($matriculas_arr) > 0){
		sort($matriculas_arr);
		$_SESSION['users'] = $matriculas_arr;
	} else {
		echo "<div class='fontPlay noUserGroup'>".
					"<b><p><i class='fa fa-times fa-lg'></i> N&atilde;o h&aacute; operadores listados para sua equipe!</p></b><hr style='border:0.2px solid #ccc; width:75%;' />".
					"<p>Adicione operadores à sua equipe clicando na aba 'Equipe' e seguindo às instruções dadas.</p>".
			"</div><br />";
	}

	//Exibe resultado da pesquisa ordenados================================
	if($dbView != "DC=call,DC=br"){
		for ($j = 0; $j < $i; $j++) {
			echo $resultArray[$j];
		}
	}			
?>