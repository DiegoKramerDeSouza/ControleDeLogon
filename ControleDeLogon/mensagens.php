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
					require_once("getDate.php");
					require_once("userInfo.php");
					require_once("ldapConnection.php");
					require_once("json_consume.php");					
					$filt = '(&(objectClass=User)(objectCategory=Person))';
					$returnbaseUser = $dbView;
						//Search
						$sr = ldap_search($lc, $dbView, $filt);//$base
						//Organiza
						$sort = ldap_sort($lc, $sr, 'name');
						//Recolhe entradas
						$info = ldap_get_entries($lc, $sr);
					
					//==========================================================================================================================
					
					//Consome JSON==============================================
						$new_object = json_read($const_url);
					//==========================================================
						
					//Listagem de usuários retornados ==========================
					//Início do laço FOR para cada resultado obtdo em pesquisa LDAP==============================================================
					for ($i = 0; $i < $info["count"]; $i++) {
							$colaborador = $info[$i]["cn"][0];
							$dn = $info[$i]["distinguishedname"][0];
							$colablen = strlen($colaborador);
							$returnex = explode(',', $dn, 2);
							$returnbase = $returnex[1];
							$matriculas_arr[] = array("label" => $info[$i]["samaccountname"][0] . " - " . $info[$i]["name"][0], "value" => $info[$i]["samaccountname"][0]);
					}
					if ($cargo == "Especialista" || ($cargo == "Conta Administrativa" || $cargo == "Suporte Tecnico")){
						$headIndex = "<div class='container'>	
										<div class='navbar-inverse side-collapse in navbarMenu'>
											<nav role='navigation' class='navbar-collapse'>
												<ul class='nav navbar-nav' role='tablist'>".
													"<li class='active'><a href='#tab1' id='sec' role='tab'><b>Desbloqueio <i class='fa fa-unlock-alt fa-sm'></i></b></a></li>". 
													"<li><a href='#tab2' id='sec' role='tab'><b>Tempo de Login <i class='fa fa-clock-o fa-sm'></i></b></a></li>".
													"<li><a href='relatorios.php' id='sec' role='tab'><b>Relat&oacute;rios <i class='fa fa-file-text fa-sm'></i></b></a></li>".
												"</ul>
											</nav>
										</div>
									</div>";
					} else {
						$headIndex = "<div class='container'>
										<div class='navbar-inverse side-collapse in navbarMenu'>
											<nav role='navigation' class='navbar-collapse'>
												<ul class='nav navbar-nav' role='tablist'>".
													"<li class='tab-list'><a href='home.php?filter=" . $_SESSION['equipeFilter'] . "' id='sec' role='tab'><b>Tempo <i class='fa fa-clock-o fa-sm'></i></b></a></li>". 
													"<li class='tab-list'><a href='desbloqueia.php' id='sec' role='tab'><b>Desbloqueio <i class='fa fa-unlock-alt fa-sm'></i></b></a></li>";
						if ($cargo == "Coordenador"){
							$headIndex = $headIndex.
													"<li class='tab-list'><a href='gerenciar_grupos.php' id='sec' role='tab'><b>Grupos <i class='fa fa-bookmark fa-sm'></i></b></a></li>";
						} else if ($cargo == "Supervisor"){
							$headIndex = $headIndex.
													"<li class='tab-list'><a href='equipe.php' id='sec' role='tab'><b>Equipe <i class='fa fa-users fa-sm'></i></b></a></li>";
						}
						$headIndex = $headIndex.
													"<li class='active'><a href='mensagens.php' id='sec' class='activetab' role='tab' data-toggle='tab'><b>Mensagens <i class='fa fa-envelope fa-sm'></i></b></a></li>".
													"<li class='tab-list'><a href='relatorios.php' id='sec' role='tab'><b>Relat&oacute;rios <i class='fa fa-file-text fa-sm'></i></b></a></li>".
												"</ul>
											</nav>
										</div>
									</div>";
					}
					echo $headIndex;
					
					$msgdata = explode("|", $_SESSION['msgInfo']);
					$msgsPath = "//call.br/servicos/LOGS/LogsMessages/SCL/inf/" . $_SESSION['matricula'] . ".inf";
					$msgName = array();
					if(file_exists($msgsPath)){
						$qtdTargetsRead = 0;
						$qtdTargetsNotRead = 0;
						foreach(glob($msgsPath) as $file){
							$read = array();
							$notRead = array();
							$filecontent = file_get_contents($file);
							$mesagescount = explode("@", $filecontent);
							for($i=1; $i<count($mesagescount);$i++){
								$exMSG  = explode("#", $mesagescount[$i]);
								//$msgName['name' . $i] = $exMSG[1];
								for($j=2; $j<(count($exMSG)-1); $j++){
									$exDetails = explode("|", $exMSG[$j], 2);
									if($exDetails[0] != "0"){
										$msgName[$exMSG[1]]['read'][] = $exDetails[1];
										$qtdTargetsRead++;
									} else {
										$msgName[$exMSG[1]]['notread'][] = $exDetails[1];
										$qtdTargetsNotRead++;
									}
								}
								unset($read);
								unset($notRead);
							}
						}
						$sends = (count($mesagescount) - 1);
						$qtdRead = $qtdTargetsRead;
						$qtdNotRead = $qtdTargetsNotRead;
					} else {
						$sends = 0;
						$qtdRead = 0;
						$qtdNotRead = 0;
					}
					
					
					$mydata = 	"<div class='row fontPlay' align='left'>".
									"<div class='col-xs-12'>".
										"<span class='md btn btn-default btn-sm bggreen' id='mymessages' data-toggle='modal' data-target='#myMSGBox'><span class='fa fa-comments'></span> Minhas Mensagens</span> <br />".
									"</div>".
								"</div>";
					
					$head = "<div class='container'>".
								"<span><h2><b class='fontPlay'><i class='fa fa-envelope'></i> Escolha a op&ccedil;&atilde;o de mensagem:</b></h2></span>".
								"<div class='row' style='margin-top:10px;'>".
									"<div class='col-xs-3 col-sm-2 col-md-1' align='right'>".
										"<span style='padding:35px;'>".
											$photo.
										"</span>".
									"</div>".
									"<div class='col-xs-6 fontPlay' style='padding-left:25px;'>".
										"<b style='font-size:12px;'>Bem vindo: </b>" . $name . "<b><br />" . $dataAtual . "</b>".
									"</div>".
								"</div>".
								"<div class='container'>".
								"<form class='form-horizontal fontPlay' name='sendMessage' role='form' method='post' action='sendMessage.php'>".
									"<div class='row'>".
										"<div class='col-xs-12 col-sm-5 col-md-5 msgSelection'>";
											if($cargo == "Coordenador"){
												$head = $head . 
												"<div class='checkbox'>".
													"<label class='checkbox' onclick='clickedCKBtn(1, 0)'>".
														"<div class='row'>".
															"<div class='col-xs-2 col-sm-2 col-md-1'>".
																"<input type='radio' id='checkboxBtn1' data-toggle='toggle' class='messageopt' data-on='<i class=\"fa fa-check fa-lg checkOkIcon\"></i>' data-off='<i class=\"fa fa-times fa-lg\"></i>'>".
															"</div>".
															"<div class='col-xs-9 col-sm-9 col-md-10' style='padding-top:5px; margin-left:10px;'>".
																"<b><i class='fa fa-globe'></i> &nbsp;Enviar para toda opera&ccedil;&atilde;o.</b>".
															"</div>".
														"</div>".
													"</label>".
												"</div>".
												"<div class='checkbox'>".
												"<label class='checkbox' onclick='clickedCKBtn(2, " . json_encode($_SESSION['users']) . ")'>".
													"<div class='row'>".
														"<div class='col-xs-2 col-sm-2 col-md-1'>".
															"<input type='radio' id='checkboxBtn2' data-toggle='toggle' class='messageopt' data-on='<i class=\"fa fa-check fa-lg checkOkIcon\" />' data-off='<i class=\"fa fa-times fa-lg\" />'>".
														"</div>".
														"<div class='col-xs-9 col-sm-9 col-md-10' style='padding-top:5px; margin-left:10px;'>".
															"<b><i class='fa fa-users'></i> &nbsp;Enviar para uma equipe.</b>".
														"</div>".
													"</div>".
												"</label>".
											"</div>";
											}
											if($cargo == "Supervisor"){
												$head = $head . 
												"<div class='checkbox'>".
													"<label class='checkbox' onclick='clickedCKBtn(3, 0)'>".
														"<div class='row'>".
															"<div class='col-xs-2 col-sm-2 col-md-1'>".
																"<input type='radio' id='checkboxBtn2' data-toggle='toggle' class='messageopt' data-on='<i class=\"fa fa-check fa-lg checkOkIcon\" />' data-off='<i class=\"fa fa-times fa-lg\" />'>".
															"</div>".
															"<div class='col-xs-9 col-sm-9 col-md-10' style='padding-top:5px; margin-left:10px;'>".
																"<b><i class='fa fa-users'></i> &nbsp;Enviar para minha equipe.</b>".
															"</div>".
														"</div>".
													"</label>".
												"</div>";
											}
											$head = $head . 
												"<div class='checkbox'>".
													"<label class='checkbox' onclick='clickedCKBtn(4, " . json_encode($_SESSION['users']) . ")'>".
														"<div class='row'>".
															"<div class='col-xs-2 col-sm-2 col-md-1'>".
																"<input type='radio' id='checkboxBtn3' data-toggle='toggle' class='messageopt' data-on='<i class=\"fa fa-check fa-lg checkOkIcon\" />' data-off='<i class=\"fa fa-times fa-lg\" />'>".
															"</div>".
															"<div class='col-xs-9 col-sm-9 col-md-10' style='padding-top:5px; margin-left:10px;'>".
																"<b><i class='fa fa-user'></i> &nbsp;Enviar para um colaborador.</b>".
															"</div>".
														"</div>".
													"</label>".
												"</div>".
												"<hr style='border:1px solid rgba(80,80,80,0.3);' />".
												$mydata .
												"<input type='hidden' name='optMessage' id='optMessage' style='display:;' value='1' />".
											"</div>".
											"<div class='col-xs-12 col-sm-6 col-md-6 col-sm-push-1 col-md-push-1' id='divViewOpt' style='display:none; margin-right:50px; padding-bottom:80px;'>".
												"<div class='container-fluid'>".
													"<span id='viewOpt'>".
													"</span>".
													"<div align='right'>".
														"<button type='submit' class='btn btn-success' id='gotoMsgEditor'>Criar mensagem <i class='fa fa-envelope-o'></i></button>".
													"</div>".
												"</div>".
											"</div>".
										"</div>".
									"</form>".
								"</div>".
							"</div>";
					echo $head;
					
					ldap_close($lc);
				?>
			</div>
		</div>

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
		<?php
			$modalMSG = "	<div class='modal fade' id='myMSGBox' tabindex='-1' role='dialog' aria-labelledby='imgmodal-label' aria-hidden='true'>
								<div class='modal-dialog modal-md'>
									<div class='modal-content'>
										<div class='modal-header'>
											<h3 class='fontPlay'><span class='fa fa-send'></span> Mensagens Enviadas:</h3>
										</div>
										<div class='modal-body'>
											<div class='panel-group' id='accordion' role='tablist' aria-multiselectable='true' style='margin:5px;'>";
											$countKeys = 0;
											$pageReset = 0;
											$pageNumber = 1;
											$pages = array();
											if(count($msgName) > 0){
												array_push($pages, $pageNumber);
												$arrayKey = array_keys($msgName);
												arsort($arrayKey);
												$countPages = count($arrayKey);
											} else {
												$countPages = 0;
											}
											if($countPages > 0){
												foreach($arrayKey as $value){
													$countKeys++;
													$pageReset++;
													$countRead = 0;
													$countNRead = 0;
													$modalMSGBody = "";
													
													if($pageReset > 5){
														$pageNumber++;
														array_push($pages, $pageNumber);
														$pageReset = 1;
													}
													if($pageNumber == 1){
														$nextPage = "_pg" . $pageNumber;
													} else {
														$nextPage = "nextPage _pg" . $pageNumber;
													}
													
													$arrayKeySub = array_keys($msgName[$value]);
													foreach($arrayKeySub as $sub){
														if($sub == "read"){
															for($i=0; $i<count($msgName[$value][$sub]); $i++){
																$modalMSGBody = $modalMSGBody . 
																				"<div class='col-xs-12 col-sm-6'>".
																					"<span class='sm blue' data-toggle='tooltip' data-placement='top' title='Lida'><span class='fa fa-check green'></span> " . $msgName[$value][$sub][$i] . "</span><br />".
																				"</div>";
																$countRead++;
															}
														} else {
															for($i=0; $i<count($msgName[$value][$sub]); $i++){
																$modalMSGBody = $modalMSGBody . 
																				"<div class='col-xs-12 col-sm-6'>".
																					"<span class='sm red' data-toggle='tooltip' data-placement='top' title='Não lida'><span class='fa fa-times red'></span> " . $msgName[$value][$sub][$i] . "</span><br />".
																				"</div>";
																$countNRead++;
															}
														}
													}
													$totalMSG = $countRead + $countNRead;
													if($totalMSG == $countRead){
														$color = "fa-check green";
													} else {
														$color = "fa-eye blue";
													}
													$modalMSG = $modalMSG . "<div class='panel panel-default li_panel " . $nextPage . "'>".
																				"<div class='panel-heading' role='tab' id='Tab-" . $countKeys . "'>".
																					"<h4 class='panel-title'>".
																						"<a role='button' data-toggle='collapse' data-parent='#accordion' href='#col-" . $countKeys . "' aria-expanded='true' aria-controls='col-" . $countKeys . "' style='text-decoration:none;'>".
																							"<span class='sm dblue'><b>" . $countKeys . ".</b> Mensagem: " . $value . " </span>".
																						"</a>".
																						"<span style='position:absolute; right:30px;' class='blue fontPlay sm'><span class='fa " . $color . "'></span>(<b class='dblue'>" . $countRead . "</b>/<b class='dblue'>" . $totalMSG . "</b>)</span>".
																					"</h4>".
																				"</div>".
																				"<div id='col-" . $countKeys . "' class='panel-collapse collapse' role='tabpanel' aria-labelledby='Tab-" . $countKeys . "'>".
																					"<div class='panel-body'>".
																						$modalMSGBody .
																					"</div>".
																				"</div>".
																			"</div>";
												}
											}
											
							$modalMSG = $modalMSG .
										"	</div>
										</div>
										<div class='modal-footer modalBorders'>
											";
											if($pageNumber > 1){
												$modalMSG = $modalMSG . "<div align='center'>";
												foreach($pages as $page){
													$modalMSG = $modalMSG . "<b><a href='#' onclick='changepage(\"pg" . $page . "\")' class='somepage fontPlay' id='pg" . $page . "'>" . $page . "</a></b> ";
												}
												$modalMSG = $modalMSG . "</div>";
											}
							$modalMSG = $modalMSG .
											"<button type='button' id='helpClose' class='btn btn-info fontPlay' data-dismiss='modal' aria-hidden='true'><span class='fa fa-times fa-lg'></span>Fechar</button>
										</div>
									</div>
								</div>
							</div>";
			
			echo $modalMSG;
			//echo $modalRead;
			//echo $modalNRead;
			echo $htmlFooter;
		?>
		<script>
			$(function(){
				$("#search").autocomplete({
					source: <?php echo json_encode($_SESSION['users']);?>, minLength: 3
				});
				$("#searchUser").autocomplete({
					source: <?php echo json_encode($_SESSION['users']);?>, minLength: 3
				});
				$("#searchUserMng").autocomplete({
					source: <?php echo json_encode($_SESSION['users']);?>, minLength: 3
				});
			});
		</script>
		
	</body>
	
</html>