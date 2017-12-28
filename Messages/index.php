<!DOCTYPE html>
<html>
	<header>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>SCL</title>
		<link rel='stylesheet' href='./styles/bootstrap_free.css' />
		<link rel='stylesheet' href='./styles/font-awesome.css' />
		<link rel='stylesheet' href='./styles/style.css' />
		<link rel='stylesheet' href='scripts/jQueryRollPlugin/jRoll.css' />
		<link href="./Images/hexagon.png" rel="icon" type="image/png" />
	</header>
	
	<body style='margin-top: 10px;'>
		<div id="loading">
			<div id="dialogLoading">
				<div>
					<span class="fontPlay">Aguarde...</span>
				</div>
				<div id="loadGif"></div>
			</div>
		</div>
		<!-- Barra de navegação - menu op001.-->
		<div class="navbar navbar-inverse navbar-fixed-top" role="navegation" >
			<div class="container-fluid">
			
				<div class="navbar-header container">
					<?php 
						require_once("pageInfo.php");
						echo $pageHeader;
					?>
				</div>
			</div>
		</div>
		<!-- Fim da Barra de navegação cl001.-->
		
		<!-- DISPOSIÇÃO DOS DEMAIS CONTAÚDOS NOS LIMITES "CONTAINER"-->
		
		<!--Mensagem de Retorno -->
		<div id="msgDiv" align="right">
			<div id="dialog" align="center">
				<span id="closebtn" onclick="clearMsgBox()"><i class="fa fa-times fa-2x"></i></span>
				<span id="msgHeader"></span><br />
				<hr />
				<span id="msgBody"></span><br />
			</div>
		</div>
		<!--Fim - Mensagem de Retorno -->
		
		<div class="container">
			<div class="result">
	<?php
		require_once("conf.php");
		require_once("restrict.php");
		require_once("getDate.php");
		require_once("ldapConnection.php");
		header('Content-Type: text/html; charset=utf-8');
		
		if(isset($_GET["act"])){
			$account = $_GET["act"];
		} else {
			$account = $_POST["act"];
		}
		$index = base64_decode($account);
		$account = base64_decode($index);
		
		//Set time zone
		date_default_timezone_set("America/Sao_Paulo");
		
		$page = 1;
		$menubar = '<div class="menu-left-bg">
						&nbsp;
					</div>
					<div class="menu-left fontPlay" align="left">
						<div style="padding-top:80px;">
						<strong>';
		$readed = "";
		
		if($ldapB){
			$filt = '(&(objectClass=User)(sAMAccountname=' . $account . '))';
			$dbView = "DC=call,DC=br";
			//Search
			$sr = ldap_search($lc, $dbView, $filt);
			//Organiza
			$sort = ldap_sort($lc, $sr, 'name');
			//Recolhe entradas
			$info = ldap_get_entries($lc, $sr);
		}
		//==========================================================================================================================
		//Início do laço FOR para cada resultado obtdo em pesquisa LDAP=============================================================
			$msgBox = "<div class='confirmationBG'>
							&nbsp;
						</div>";
			echo $msgBox;
			for ($i = 0; $i < $info["count"]; $i++) {
				$number = 0;
				if(isset($info[$i]["extensionattribute13"][0])){
					$messages = explode("|", $info[$i]["extensionattribute13"][0]);					
					$number = (Int)$messages[0];
					$pageCount = intval($number / 10);
					$pageCount++;
					$menubar = $menubar . 
								'<blockquote class="blockquoteMenu">
									<span class="lblue">' . $info[$i]["displayname"][0] . '</span><br />
									Minhas mensagems:
								</blockquote>';
					$menubar = $menubar . "<div class='panel-group' id='accinbox' role='tablist' aria-multiselectable='true' style='background-color:transparent;'>
												<div style='padding-left:30px; background-color:transparent; border:none;' class='panel panel-default'>
													<div class='panel-heading' role='tab' id='_inbox' style='background-color:transparent; border:none; color:white;'>
														<h4 class='panel-title fontPlay white'>
															<a role='button' data-toggle='collapse' data-parent='#accinbox' href='#ul1' aria-expanded='true' aria-controls='ul1'>
																<i class='fa fa-inbox'></i><b> N&atilde;o lidas (<span class='lblue'>" . $number . "</span>)</b>
															</a>
														</h4>
													</div>
													<div id='ul1' class='panel-collapse collapse in' role='tabpanel' aria-labelledby='_inbox'>
														<div class='panel-body' style='border:none;'>
															<div id='pagep_1' class='msgPage' style='list-style: none;'>";
					if($number > 0){
						for($j = 1; $j <= $number; $j++){
							if($j == 1){
								$msgDisplay = "block";
								$btnMenu = "btn-menu-in";
								$msgPrev = "none";
							} else {
								$msgDisplay = "none";
								$btnMenu = "btn-menu";
								$msgPrev = "";
							}
							if($j == $number){
								$msgNext = "none";
							} else {
								$msgNext = "";
							}
							
							if(file_exists("\\\\call.br\\servicos\\LOGS\\LogsMessages\\SCL\\" . $messages[$j] . ".html")){
								$htmlmessage = file_get_contents("\\\\call.br\\servicos\\LOGS\\LogsMessages\\SCL\\" . $messages[$j] . ".html");
								$conteudo = explode("<div><h2>", $htmlmessage, 2);
								$conteudofiltrado = explode("</h2></div>", $conteudo[1], 2);
								$subject = $conteudofiltrado[0];
								$menubar = $menubar . '<div class="texto"><div id="_' . $j . '" class="btn btn-sm ' . $btnMenu . ' showmsgbody"><span title="' . $subject . '">' . $j . '. <i class="fa fa-envelope"></i>&nbsp; ' . $subject . '</span></div></div>';
								
								if($j == $number || ($j % 10 == 0 && $number > 10)){
									if($number > 10){
										if($page == $pageCount){
											$pageStart = "";
											$pageEnd = "none";
										} else if ($page == 1){
											$pageStart = "none";
											$pageEnd = "";
										} else {
											$pageStart = "";
											$pageEnd = "";
										}
										$menubar = $menubar . "
															<div align='center' style=''>
																<a href=# style='position:relative; top:5px; color:#fff; display:" . $pageStart . "' class='turnPage' id='p_" . ($page - 1) . "'>
																	<i class='fa fa-angle-left fa-2x'></i>
																</a>
																&nbsp;&nbsp;". $page . "/" . $pageCount . "&nbsp;&nbsp;
																<a href=# style='position:relative; top:5px; color:#fff; display:" . $pageEnd . "' id='p_" . ($page + 1) . "' class='turnPage'>
																	<i class='fa fa-angle-right fa-2x'></i>
																</a>
															</div>
														</div>
													</div>
													<div id='pagep_" . ($page + 1) . "' class='msgPage' style='list-style:none; display:none;'>";
										$page++;
									} else {
										$menubar = $menubar . "<div></div></div></div>";
									}
								}
								//---View Message----------------------------------------------------------------
								$messagePage = file_get_contents($filepath . "/" . $messages[$j] . ".html");
								$messagePage = "<div id='message_" . $j . "' class='bodymsg' style='display:" . $msgDisplay . "'>
													<div style='position:fixed; top:90px; right:20px; text-shadow: 1px 0px #aaa;' class='fontPlay'><b>".
														$j . "/" . $number .
													"</b></div>
													<div align='right' class='toppagemenu'>
														<span class='btn btn-text btn-sm prevOrNextMsg' id='" . ($j - 1) . "' style='display: " . $msgPrev . "'><i class='fa fa-angle-left fa-lg'></i></span>
														<span class='btn btn-text btn-sm prevOrNextMsg' id='" . ($j + 1) . "' style='display: " . $msgNext . "'><i class='fa fa-angle-right fa-lg'></i></span>
														<span class='btn btn-text btn-sm' onclick='callAlertBlock(\"" . $j . "\")'>Li esta mensagem <i class='fa fa-check-square-o'></i></span>
													</div>
													<hr class='menuBar' />".
													$messagePage .
												"<span style='padding-bottom: 100px;'>&nbsp;</span>
											</div>";
								echo $messagePage;
								//-------------------------------------------------------------------------------
								$msgBox = 	"<div class='confirmDiv' id='alert_" . $j . "'>
												<div class='msgBoxDiv fontPlay'>
													<i class='fa fa-eye fa-lg'></i>&nbsp; Visualização
												</div>
												<hr />
												<div class='msgBoxBody fontPlay' id='text_" . $j . "'>
													<p>Após vizualizada a mensagens será exibida na aba \"Lidas\".</p>
													<p>Confirma a leitura desta mensagem?</p>
												</div>
												<div class='btnConfirma fontPlay' align='right' id='btn_" . $j . "'>
													<span class='btn btn-text btn-sm' onclick='alertBoxFade(\"" . $j . "\");'>Não <i class='fa fa-times'></i></span>
													<span class='btn btn-text btn-sm' onclick='readMsg(\"" . $account . "\",\"" . $j . "\")'>Sim <i class='fa fa-check'></i></span>
												</div>
											</div>";
								echo $msgBox;
							}
						}
					} else {
						$menubar = $menubar . "<span style='padding-top:50%;'><span style='border-left:5px solid #a00; padding:20px;'>Sem novas mensagens!</span></span>
											</div>
										</div>";
						echo "<div class='emptyMessage' id='emptyMessage'>
									<div class='row'>
										<div class='col-xs-2 col-sm-2 col-md-1'>
											<span class='fa fa-times fa-5x'></span>
										</div>
										<div class='col-xs-10 col-sm-10 col-md-11' style='bottom:10px;'>
											<h1 class='fontPlay'>Voc&ecirc; n&atilde;o possui novas mensagens!</h1>
											<hr style='border: 1px solid #a00; margin-top:-5px;' />
										</div>
									</div>
								</div>";
					}
					
					
					
					
					$msgsPath = "//call.br/servicos/LOGS/LogsMessages/SCL/logs/" . $account . "*";
					$arrayFiles = glob($msgsPath);
					arsort($arrayFiles);
					$pageCount = intval(count($arrayFiles) / 10);
					$pageCount++;
					if(count($arrayFiles) >= 0){
						$menubar = 	$menubar . "</div>
											</div>
											
											<div style='padding-left:30px; background-color:transparent; border:none;' class='panel panel-default'>
												<div class='panel-heading' role='tab' id='_read' style='background-color:transparent; border:none;'>
													<h4 class='panel-title fontPlay white'>
														<a role='button' data-toggle='collapse' data-parent='#accinbox' href='#ul2' aria-expanded='true' aria-controls='ul2'>
															<i class='fa fa-eye'></i><b> Lidas (<span class='lblue'>" . count($arrayFiles) . "</span>)</b>
														</a>
													</h4>
												</div>
											<div id='ul2' class='panel-collapse collapse' role='tabpanel' aria-labelledby='_read'>
												<div class='panel-body' style='border:none;'>
													<div id='' class='' style='list-style:none;'>";
						if(count($arrayFiles) > 10){
							if($page == $pageCount){
								$pageStart = "";
								$pageEnd = "none";
							} else if ($page == 1){
								$pageStart = "none";
								$pageEnd = "";
							} else {
								$pageStart = "";
								$pageEnd = "";
							}
							$countpage = 1;
							$counteritem = 0;
							$pages = array();
							array_push($pages, 0);
							array_push($pages, 1);
							for($k = 0; $k < count($arrayFiles); $k++){
								$logcontent = file_get_contents($arrayFiles[$k]);
								$htmlName = explode("->", $logcontent);
								if(isset($htmlName[1])){
									$counteritem++;
									if($countpage > 1){
										$hiddentype = "otherpage";
									} else {
										$hiddentype = "";
									}
									$filetoget = $htmlName[1];
									$menubar = $menubar . "<div class='" . $hiddentype . " texto li_panel _pg" . $countpage . "' ><div onclick='getContents(\"_" . $k . "Pointer\")' data-pointer='" . $htmlName[1] . "' data-index='" . $k . "' id='_" . $k . "Pointer' class='btn btn-menu btn-sm showreadmsgbody' >" . ($k + 1) . ". <i class='fa fa-envelope-open'></i>&nbsp; " . $htmlName[1] . "</div></div>";
									if($counteritem % 10 == 0){
										$countpage++;
										$counteritem = 1;
										array_push($pages, $countpage);
									}
									$messagePage = "<div id='readbodymsg" . $k . "' class='readbodymsg' style='display:none;'>".
														"<div id='message_" . $k . "'>
															<div style='position:fixed; top:90px; right:20px; text-shadow: 1px 0px #aaa;' class='fontPlay'>
																<b>". ($k + 1) . "/" . count($arrayFiles) . "</b>
															</div>
															<div align='right' class='toppagemenu'>
																<span class='btn btn-text btn-sm' onclick='callExclusionBlock(\"" . $k . "\")'>Excluir esta mensagem <i class='fa fa-times'></i></span>
															</div>
															<hr class='menuBar' />
															<div id='R_" . $k . "'>
															</div>
															<span style='padding-bottom: 100px;'>&nbsp;</span>
														</div>
													</div>";
									$msgBox = 	"<div class='confirmDiv' id='exclusion_" . $k . "'>
													<div class='msgBoxDiv fontPlay'>
														<i class='fa fa-exclamation-triangle fa-lg'></i>&nbsp; Exclusão
													</div>
													<hr />
													<div class='msgBoxBody fontPlay' id='exclusiontext_" . $k . "' data-index='" . base64_encode($arrayFiles[$k]) . "'>
														<p>Após a exclusão esta mensagem deixará de ser exibida nesta listagem.</p>
														<p>Confirma a exclusão desta mensagem?</p>
													</div>
													<div class='btnConfirma fontPlay' align='right' id='exclusionbtn_" . $k . "'>
														<span class='btn btn-text btn-sm' onclick='exclusionBoxFade(\"" . $k . "\");'>Não <i class='fa fa-times'></i></span>
														<span class='btn btn-text btn-sm' onclick='excldMsg(\"" . $account . "\",\"" . $k . "\")'>Sim <i class='fa fa-check'></i></span>
													</div>
												</div>";
									echo $msgBox;
									echo $messagePage;
								}
								
							}
												
							$menubar = $menubar . "</div>
												</div>
												<div id='pageRp_" . ($page + 1) . "' class='' style='list-style:none; display:;'>
													<div align='center' style=''>";
														for($l=1; $l < count($pages); $l++){
															$menubar = $menubar . 
															"<u><a href=# style='position:relative; top:5px; color:#fff;' id='pg" . $pages[$l] . "' class='nextPageR fontPlay' onclick='changepage(\"pg" . $pages[$l] . "\")'>" . 
																$pages[$l] . 
															"</a></u> ";
														}
							$menubar = $menubar . "</div>";
						} else {
							for($k=0; $k < count($arrayFiles); $k++){
								if(file_exists($arrayFiles[$k])){
									$logcontent = file_get_contents($arrayFiles[$k]);
									$htmlName = explode("->", $logcontent);
									if(isset($htmlName[1])){
										$menubar = $menubar . '<div class="li_panel" ><div onclick="getContents(\'_' . $k . 'Pointer\')" data-pointer="' . $htmlName[1] . '" data-index="' . $k . '" id="_' . $k . 'Pointer" class="btn btn-menu btn-sm showreadmsgbody">' . ($k + 1) . '. <i class="fa fa-envelope-open"></i>&nbsp; ' . $htmlName[1] . '</div></div>';
										$messagePage = "<div id='readbodymsg" . $k . "' class='readbodymsg' style='display:none;'>".
															"<div id='message_" . $k . "'>
																<div style='position:fixed; top:90px; right:20px; text-shadow: 1px 0px #aaa;' class='fontPlay'>
																	<b>". ($k + 1) . "/" . count($arrayFiles) . "</b>
																</div>
																<div align='right' class='toppagemenu'>
																	<span class='btn btn-text btn-sm' onclick='callExclusionBlock(\"" . $k . "\")'>Excluir esta mensagem <i class='fa fa-times'></i></span>
																</div>
																<hr class='menuBar' />
																<div id='R_" . $k . "'>
																</div>
																<span style='padding-bottom: 100px;'>&nbsp;</span>
															</div>
														</div>";
										$msgBox = 	"<div class='confirmDiv' id='exclusion_" . $k . "'>
														<div class='msgBoxDiv fontPlay'>
															<i class='fa fa-exclamation-triangle fa-lg'></i>&nbsp; Exclusão
														</div>
														<hr />
														<div class='msgBoxBody fontPlay' id='exclusiontext_" . $k . "' data-index='" . base64_encode($arrayFiles[$k]) . "'>
															<p>Após a exclusão esta mensagem deixará de ser exibida nesta listagem.</p>
															<p>Confirma a exclusão desta mensagem?</p>
														</div>
														<div class='btnConfirma fontPlay' align='right' id='exclusionbtn_" . $k . "'>
															<span class='btn btn-text btn-sm' onclick='exclusionBoxFade(\"" . $k . "\");'>Não <i class='fa fa-times'></i></span>
															<span class='btn btn-text btn-sm' onclick='excldMsg(\"" . $account . "\",\"" . $k . "\")'>Sim <i class='fa fa-check'></i></span>
														</div>
													</div>";
										echo $msgBox;
										echo $messagePage;
									}
								}
							}
						}
						$menubar = $menubar . "		</div>
												</div>
											</div>
										</div>
									</div>";
					}
					$menubar = $menubar . 
								'	<div id="indicatorLeft" style="position:absolute; top:49%; left:288px;">
									</div>
								</strong>
							</div>
						</div>
						<div class="active-menu">
							&nbsp;
						</div>
						<div style="padding-bottom: 600px;">&nbsp;</div>';
					
					
					
					
				} else {
					$menubar = $menubar . "<span style='padding-top:50%;'><span style='border-left:5px solid #a00; padding:20px;'>Sem novas mensagens!</span></span>";
					echo "<div style='padding: 100px; color:#a00;' class='emptyMessage' id='emptyMessage'>
							<div class='row'>
								<div class='col-xs-2 col-sm-2 col-md-1'>
									<span class='fa fa-times fa-5x'></span>
								</div>
								<div class='col-xs-10 col-sm-10 col-md-11' style='bottom:10px;'>
									<h1 class='fontPlay'>Voc&ecirc; n&atilde;o possui mensagens!</h1>
									<hr style='border: 1px solid #a00; margin-top:-5px;' />
								</div>
							</div>
						</div>";
				}
				
			}

		echo $menubar;
	?>
			</div>
		</div>
				
		<div class="bottomFooter" id="homefooter" style="position:fixed; bottom:0px; width:100%; display:none;">
			<?php 
				echo $pageFooter;
			?>
		</div>
		
		<script src='./Scripts/jquery-2.1.4.min.js'></script>
		<script src='./Scripts/bootstrap.min.js'></script>
		<script src='./Scripts/bootstrap-toggle.min.js'></script>
		<script src='./scripts/animated.js'></script>
		<script src='./scripts/jQueryRollPlugin/jRoll.min.js'></script>
		<script>
			document.getElementById('indicatorLeft').innerHTML = '<span class="fa fa-chevron-right fa-1x"></span>';
			$(document).ready(function(){
				$(".pointerClick2").click(function(){
					var elemId = this.id;
					var file = $("#"+elemId).attr("data-pointer");
					console.log(file);
					$("#readbodymsg").fadeIn(300);
					$countcls = document.querySelectorAll('.bodymsg').length;
					for(var $i = 0; $i < $countcls; $i++){
						var elem = document.getElementsByClassName('bodymsg')[$i];
						$('#'+elem.id).hide();
					}
					var target = file;
					var xhttp = new XMLHttpRequest();
					xhttp.onreadystatechange = function(){
						if(this.readyState == 4 && this.status == 200){
							document.getElementById('readbodymsg').innerHTML = this.responseText;
						}
					};
					xhttp.open('GET', "collectMsg.php?index=" + target, true);
					xhttp.send();
				});
			});
		</script>
		
			
	</body>
	
</html>


