<!DOCTYPE html>
<html>
	<?php
		require_once("pageInfo.php");
		echo $htmlHeader;
	?>
	<body style='margin-top: 10px;'>
		<?php
			echo $htmlloading;
			echo $pageNavbar;
		?>		
		<div id="msgDiv" align="right">
			<div id="dialog" align="center">
				<span id="closebtn" onclick="clearMsgBox()"><i class="fa fa-times fa-2x"></i></span>
				<span id="msgHeader"></span><br />
				<hr />
				<span id="msgBody"></span><br />
			</div>
		</div>
		<div class="container">
			<div class="result">
	<?php
		
		require_once("restrict.php");
		require_once("ldapConnection.php");
		require_once("getDate.php");
		require_once("userInfo.php");
		require_once("json_consume.php");
		
		if($dbView != "DC=call,DC=br"){
			require_once("autocomplete.php");
		}
		$base = $dbView;
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
		$headIndex = "<div class=''>
						<div class='col-sm-12 col-md-8 tabOpt' style='margin-bottom:5px;'>".
							"<div class='navbar-inverse side-collapse in navbarMenu'>
								<nav role='navigation' class='navbar-collapse'>
									<ul class='nav navbar-nav' role='tablist'>
										<li class='tab-list'><a href='home.php?filter=" . $_SESSION['equipeFilter'] . "' id='sec' role='tab'><b>Tempo <i class='fa fa-clock-o fa-sm'></i></b></a></li>
										<li class='tab-list'><a href='desbloqueia.php' id='sec' role='tab'><b>Desbloqueio <i class='fa fa-unlock-alt fa-sm'></i></b></a></li>
										<li class='active'><a href='equipe.php' id='sec' class='activetab' role='tab' data-toggle='tab'><b>Equipe <i class='fa fa-users fa-sm'></i></b></a></li>
										<li class='tab-list'><a href='mensagens.php' id='sec' role='tab'><b>Mensagens <i class='fa fa-envelope fa-sm'></i></b></a></li>
										<li class='tab-list'><a href='relatorios.php' id='sec' role='tab'><b>Relat&oacute;rios <i class='fa fa-file-text fa-sm'></i></b></a></li>
									</ul>
								</nav>
							</div>
						</div>
					</div>";
		
		echo $headIndex;
	//Cabeçalho de edição============================================================================================	
		$head = "<div class='container'>".
					"<span><h2><b class='fontPlay'><i class='fa fa-users'></i> Monte sua equipe:</b></h2></span>".
					"<div class='row' style='margin-bottom:30px;'>".
						"<div class='col-xs-3 col-sm-2 col-md-1' align='right'>".
							"<span style='padding:35px;'>".
								$photo.
							"</span>".
						"</div>".
						"<div class='col-xs-6 col-sm-4 col-md-6 fontPlay' style='padding-left:25px;'>".
							"<b style='font-size:12px;'>Bem vindo: </b>" . $name . "<b><br />" . $dataAtual . "</b>".
						"</div>".
						"<div class='col-xs-12 col-sm-12 col-md-5 fontPlay' align='right'>".
							"<form class='form-horizontal' role='form' method='post' action='insertEquipe.php'>".
								"<div class='form-group'>".
									"<div class='input-group' style='margin-left:15px; margin-right:15px; margin-top:5px;'>".
										"<input type='text' name='search' id='search' class='form-control' autofocus  placeholder='Matr&iacute;cula do operador a ser adicionado'></input>".
										"<div class='input-group-btn'>".
											"<button class='btn btn-info btn-md' type='submit' id='EditModal'>Incluir <i class='fa fa-user-plus fa-lg'></i></button>".
										"</div>".
									"</div>".
								"</div>".
							"</form>".
						"</div>".
					"</div>".
					"<div class='admHead'>".
						"<strong>".
						"<div class='row container'>".
							"<span id='allChecked' style='display:none;'></span>".
							"<span id='mycheckbox' style='display:none;'></span>".
							"<strong>".
							"<div class='col-xs-3 col-sm-3 col-md-2'><p>Matr&iacute;cula:</p></div>".
							"<div class='col-xs-4 col-sm-4 col-md-8'><p>Operador:</p></div>".
							"<div class='col-xs-4 col-sm-4 col-md-2' align=''><p>Remover:</p></div>".
							"</strong>".
						"</div>".
						"</strong>".
					"</div>";
				
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
								"<div class='progress-bar progress-bar " . $progressColor . " active' id='progressView' role='progressbar' aria-valuenow='" . $progress . "' aria-valuemin='0' aria-valuemax='" . $progressTotal . "' style='width:" . $progresspercent . "%'>".
								"</div>".
							"</div>".
						"</div>".
						//Botão para esconder informações de usuário
						"<div align='center' class='row' style='widht:102%;'>".
							"<span id='" . $account . "_hideInfo' class='btn-info btn-xs btnInfo'><i class='fa fa-angle-double-up'></i></span>".
						"</div>".
						"<span id='" . $account . "_maisTempo' class='maisTempo' style='display:none;'>".
						"</span>".
					//-------------------------------------
						"<div class='informativoUser' id='" . $account . "'>".
							"<div class='row' style='padding:5px;'>".
								"<div class='col-xs-12 col-sm-12 col-md-12'>".
									"<div class='row'>".
										"<div class='col-xs-3 col-sm-3 col-md-2' align='left'>".
											"<strong>".
											"<input type='text' name='search' id='search' class='form-control' value='" . $account . "' readonly style='max-height:40px;'></input>".
											"<input style='display:none;' type='text' name='database' id='database' class='form-control' value='" . $returnbase . "' readonly></input>".
											"</strong>".
										"</div>".
										"<div class='col-xs-4 col-sm-4 col-md-7 accountStatus' id='popup" . $account . "'>".
										//Ícone de exibição de status do operador----
											"<span class='fa-stack fa-3x' id='Stpopup" . $account . "' style='position:absolute; display:none;'>".
												"<i class='fa fa-comment fa-stack-2x' style='color:#404040;'></i>".
												"<strong style='color:#ffffff; font-size:13px;' class='fa-stack-1x fa-stack-text fa-inverse'>" . $popup . "</strong>".
											"</span>".
										//-------------------------------------------
											"<p><i id='_popup" . $account . "' style='color:" . $operatorStatus . ";' class='fa fa-phone-square fa-lg '></i> <u>" . $colaborador . "</u>&nbsp;&nbsp;".
											"<b><br />(Tempo restante: </b>" . $tempoRestante . ")</p>".
										"</div>".
										"<div class='col-xs-4 col-sm-4 col-md-3 accountStatus' align='center'>".
											"<form class='form-horizontal removeOperator' role='form' name='removeOperator' method='post' action='removeEquipe.php'>".
												"<input style='display:none;' type='text' name='userName' id='userName' class='form-control' value='" . $account . "' readonly></input>".
												"<button type='submit' id='removebtn' class='btn btn-danger btn-sm' style='margin-top:7px;'>Remover <i class='fa fa-user-times'></i></button>".
											"</form>".
										"</div>".
									"</div>".
									"<div class='row mouseEnterView' id=" . $account . "Details style='padding:10px; margin-top:10px;'>".
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
		echo $head;
		echo "<div class='resultBody'>";
		for ($j = 0; $j < $i; $j++) {
			echo $resultArray[$j];
		}
		echo "</div>";
		echo "<span class='explainOff'></span>";
	} else {
		echo $head;
		echo "<div class='fontPlay noUserGroup'>".
					"<b><p><i class='fa fa-times fa-lg'></i> N&atilde;o h&aacute; operadores listados para sua equipe!</p></b><hr style='border:0.2px solid #ccc; width:75%;' />".
					"<p class='explainOff'>Informe a matr&iacute;cula do operador na barra acima</p><p class='explainOff'>e clique em &quot;Incluir&quot; para adiciona-lo a sua equipe de operadores.".
			"</div><br />";
	}
	ldap_close($lc);
?>
		</div>
		</div>
		
		</div>
		</div>
		</div>
		</div>
		</div>
		</div>
		<br />
		
		<div class='return-left' align='center'>
			<strong>
			<a href='home.php?filter=<?php echo $_SESSION['equipeFilter']; ?>' class='hoverLight return'>
				<i class='fa fa-chevron-left fa-4x'></i>
			</a>
			</strong>
		</div>
		<div class='active-return'>
			&nbsp;
		</div>
		<div id="toTop" align="center">
			<span class="fa fa-chevron-up fa-2x" id="upToTop"></span><br />
		</div>
		
		<?php
			echo $htmlFooter;
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


