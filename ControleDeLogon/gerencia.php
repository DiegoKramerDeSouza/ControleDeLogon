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
		if($dbView != "DC=call,DC=br"){
			require_once("autocomplete.php");
		}
		
		$strUser = $_GET['searchUser'];
		$returnbase = $_GET['database_user'];
		$base = $returnbase;
		
		date_default_timezone_set("America/Sao_Paulo");
		
	//Variáveis de apresentação HTML=====================================
		$html = "";
		$resultArray = array();
	
	//Filtro para pesquisa LDAP - Usuário ================================
		//Filtro = Todos os usuários com a matrícula $strUser
		$filt = '(&(objectClass=User)(sAMAccountname=' . $strUser . '))';
		//Search
		$sr = ldap_search($lc, $base, $filt);
		//Organiza
		$sort = ldap_sort($lc, $sr, 'name');
		//Recolhe entradas
		$info = ldap_get_entries($lc, $sr);
	//==========================================================
		
	//Cabeçalho da pesquisa============================================================================================	
		$head = "<div class='container resultManegarUser'>".
						"<h3><b class='fontPlay'>Ger&ecirc;ncia de contas:</b></h3>".
						"<div class='row'>".
							"<div class='col-xs-2 col-sm-2 col-md-1' align='right'>".
								"<span style='padding:35px;'>".
									$photo.
								"</span>".
							"</div>".
							"<div class='col-xs-6 col-sm-4 col-md-5 fontPlay' style='padding-left:25px;'>".
								"<b style='font-size:12px;'>Bem vindo: </b>" . $name . "<b><br />" . $dataAtual . "</b>".
							"</div>".
							
							"<div class='col-xs-12 col-sm-6 col-md-5 col-md-push-1' align='center'>".
								"<form class='form-horizontal' role='form' method='get' action='gerencia.php'>".
									"<div class='row'>".
										"<div class='col-xs-12 col-sm-12 col-md-12'>".
											"<div class='form-group'>".
												"<h2><b class='fontPlay'>Informe a conta a gerenciar:</b></h2>".
												"<div class='input-group'>".
													"<input type='text' name='searchUser' id='searchUser' autofocus class='form-control' placeholder=' Matr&iacute;cula (EX: c22123)'></input>".
													"<input style='display:none;' type='text' name='database_user' id='database_user01' class='form-control' value='" . $returnbase . "' readonly></input>".
													"<div class='input-group-btn'>".
														"<button type='submit' id='sendSearch' class='btn btn-primary'>".
															"<span class='glyphicon glyphicon-search'></span>".
														"</button>".
													"</div>".
												"</div>".
											"</div>".
										"</div>".
									"</div>".
								"</form>".
							"</div>".
							
						"</div>";
		
		echo $head;
	//Fim - Cabeçalho da pesquisa=======================================================================================
	if(isset($info[0]["samaccountname"][0])){
		
		if($ldapB){
			//Listagem de usuários retornados ==========================
			//Início do laço FOR para cada resultado obtdo em pesquisa LDAP==============================================================
			$groupLogic = "";
			if(isset($info[0]["memberof"])){
				$userGroupControl = $info[0]["memberof"];
			}
			
			//Filtro para pesquisa LDAP - Grupos ================================
			//Filtro = todos os grupos onde o nome deste seja L.<nome do departamento>.*
			$groupfilt = '(&(objectClass=Group)(sAMAccountname=L.' . $depto . '.*))';
			//Search
			$groupsr = ldap_search($lc, "DC=call,DC=br", $groupfilt);
			//Organiza
			$groupsort = ldap_sort($lc, $groupsr, 'name');
			//Recolhe entradas
			$groupinfo = ldap_get_entries($lc, $groupsr);
		//==========================================================
			$allGroups = "";
			$allGroupsDN = "";
			$selectGroups = "<select name='select-groups' id='select-groups'>";
			
			//Filtro para eliminar da seleção os grupos queo o colaborador já pertence.==========================
			for ($i = 0; $i < $groupinfo["count"]; $i++) {
				if (isset($groupinfo[$i]["distinguishedname"][0])){
					$allGroupsDN = $allGroupsDN . $groupinfo[$i]["distinguishedname"][0] . "||";
					if (isset($groupinfo[$i]["samaccountname"][0])){
						$countBlock = 0;
						if(isset($info[0]["memberof"])){
							foreach($userGroupControl as $groupControl){
								$control = explode(",OU=", $groupControl, 2);
								$control = explode("CN=", $control[0], 2);
								if(isset($control[1])){
									if(substr($control[1], 0, 2) === "L."){
										if($control[1] == $groupinfo[$i]["samaccountname"][0]){
											$countBlock++;
										}
									}
								}
							}
						}
						if($countBlock == 0){
							$selectGroups = $selectGroups . "<option value='" . $groupinfo[$i]["distinguishedname"][0] . "'>" . $groupinfo[$i]["samaccountname"][0] . "</option>";
						}					
					}
				}
			}
			$selectGroups = $selectGroups . "</select>";
			
		//========================================================================================================
			
			for ($i = 0; $i < $info["count"]; $i++) {		
				$unlock = 0;
				$lock = "";
				$userMemberOf = "";
				$usergroups = "<i class='fa fa-times fa-lg' style='color:#ff0000;'></i> N&atilde;o h&aacute; grupos registrados para este colaborador.";
				$mygroups = "";
				$email = "N&atilde;o registrado.";
				$selectRmvGroups = "<select name='select-rmvgroups' id='select-rmvgroups'>";
				$denied = false;
				$verifyDenied = strpos("_" . $login, 'a');
				$contaAdm = false;
				$blocked = false;
				$account = $info[$i]["samaccountname"][0];
				$colaborador = $info[$i]["cn"][0];
				$dn = $info[$i]["distinguishedname"][0];
				$userDN = $dn;
				
				//Filtra Grupos por L.-----------------------------
				if (isset($info[$i]["memberof"])){
					$memberOf = $info[$i]["memberof"];
					$userMemberOf = $info[$i]["memberof"];
					foreach($memberOf as $member){
						$memberDiv = explode(",OU=", $member, 2);
						$memberDiv = explode("CN=", $memberDiv[0], 2);
						if(isset($memberDiv[1])){
							if(substr($memberDiv[1], 0, 2) === "L."){
								$mygroups = $mygroups . $memberDiv[1] . "||";
								$usergroups = $usergroups . "<i class='fa fa-bookmark' style='color:#ffb000;'></i> " . $memberDiv[1] . "<br />" ;
								$selectRmvGroups = $selectRmvGroups . "<option value='" . $member . "'>" . $memberDiv[1] . "</option>";
							}
						}
					}
				}
				$selectRmvGroups = $selectRmvGroups . "</select>";
				
				if ($usergroups != "<i class='fa fa-times fa-lg' style='color:#ff0000;'></i> N&atilde;o h&aacute; grupos registrados para este colaborador."){
					$memberOf = explode("N&atilde;o h&aacute; grupos registrados para este colaborador.", $usergroups, 2);
					$usergroups = $memberOf[1];
				}
				//Coleta e-mail-----------------------------------
				if (isset($info[$i]["mail"])){
					$email = $info[$i]["mail"][0];
				}
				//Coleta foto-------------------------------------
				if (isset($info[$i]["thumbnailphoto"])){
					$picture = $info[$i]["thumbnailphoto"][0];
					$picture = "<img class='img-circle' src='data:image/jpeg;base64," . base64_encode($picture) . "' style='width:100px; height:100px;'/>";
				} else {
					$picture = "<img class='img-circle' src='Images/user_icon.png' style='width:100px; height:100px;'>";
				}
				//Verifica se é conta administrativa----------------
				if(strpos($dn, "Contas Administrativas") != ""){
					$denied = true;
					$contaAdm = true;
				}
				if ($verifyDenied === 1 && $allowedUser){
					$denied = false;
				}
				$colablen = strlen($colaborador);
				$returnex = explode(',', $dn, 2);
				$returnbase = $returnex[1];
				$predescription = explode(',', $dn, 2);
				$description = explode('DC=', $predescription[1]);
				$description = explode('OU=', $description[0]);
				if(isset($description[3])){
					$origin = str_replace(',', "/", $description[3] . $description[2] . $description[1]);
				}
				else{
					$origin = str_replace(',', "/", $description[2] . $description[1]);
				}
				
				if (isset($info[$i]["lockouttime"][0])){
					$unlock = $info[$i]["lockouttime"][0];
				}
				if (isset($info[$i]["useraccountcontrol"][0])){
					$accountControl = $info[$i]["useraccountcontrol"][0];
				}
				if ($unlock >= 3){
					if ($accountControl == 66050){
						$status = "<a href='#' id='userDisabled' style='color:#555;'><b>Desabilitada</b></a> e <a href='./unlock.php?searchUser=" . $strUser . "&database_unlock=" . $returnbase . "' style='color:#e00;'>Bloqueada</a></span>";
					} else {
						$status = "Habilitada e <a href='./unlock.php?searchUser=" . $strUser . "&database_unlock=" . $returnbase . "' style='color:#e00;'>Bloqueada</a></span>";
					}
				} else {
					if ($accountControl == 66050){
						$status = "<a href='#' id='userDisabled' style='color:#555;'><b>Desabilitada</b></a> e <span style='color:green'>Desbloqueada</span>";
					} else {
						$status = "Habilitada e <span style='color:green'>Desbloqueada</span>";
					}
				}
				$editBtn = "<button type='button' id='EditModal' class='btn btn-success editBtn' data-toggle='modal' data-target='#editModal' onclick='clearValues()'>".
								"Editar <span class='fa fa-pencil'></span>".
							"</button>";
				$textarea = "style='color:black;'";
				
				
			//==============================================================
			
			}
				$html = $html.
						"<div class='container information bgdblue' id='" . $account . "_unlock'>".
							"<div class='row' style='position:relative; padding-top:5px;'>".
								"<div class='col-xs-12 col-sm-12 col-md-12'>".
									//Informações do colaborador--------------------------------------
										"<div class='row'>".
											"<div class='col-xs-12 col-sm-12 col-md-5' align='left'>".
												"<div class='manegerUser'>".
													"<div class='manegerUserPhoto' align='center'>".
														"<br />".
														$picture . "<br />".
														"<b>" . $colaborador . "</b>". 
														"<hr />".
													"</div>".
													"<div class='manegerUserField' align='left'>".
														"<span class='fa fa-user fa-lg'></span> <b>Conta: </b>" . $account . "<br />".
														"<span class='fa fa-envelope'></span> <b>E-mail: </b>" . $email . "<br />".
														"<span class='fa fa-tasks'></span> <b>Situa&ccedil;&atilde;o: </b>" . $status . "<br />".
														"<br />".
													"</div>".
												"</div>".
											"</div>".
											"<div class='col-xs-7 col-sm-8 col-md-5'>".
									//----------------------------------------------------------------------
									//Grupos L. do colaborador----------------------------------------------
												"<head><h4 class='fontPlay'>Grupos:</h4></head>". 
												$usergroups.
											"</div>".
											"<div class='col-xs-5 col-sm-4 col-md-2' align='right' style='position:relative; margin-top:10px; margin-botton:10px;'>".
												"<p>" . $editBtn . "</p>".
											"</div>".
										"</div>".
									//----------------------------------------------------------------------
								"</div>".
								"<br />".
							"</div>".
						"</div>".
					"</div>";

			//=====================================================================================================================
			
			//Guarda tempo restante + HTML(resultado de pesquisa de 1 colaborador)
				$pageView = $html;
			//Limpa variável HTML	
				$html = "";
			//Insere dados gravados no array
				array_push($resultArray, $pageView);
				
				if ($mygroups != ""){
					$mygroups = explode($mygroups, "||");
				}
				$modal = '<div class="modal fade fontPlay" id="editModal" tabindex="-1" role="dialog" aria-labelledby="imgmodal-label" aria-hidden="true">
							<div class="modal-dialog modal-md">
								<div class="modal-content">
									<div class="modal-header modalBorders">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 align="left" class="fontPlay">Selecione a op&ccedil;&atilde;o desejada:</h3>
										<br />
										<h4 align="left"><b>' . $colaborador . '</b> pertence aos seguintes grupos:</h4>
									</div>
									<div class="modal-body" align="center" style="background-color:#efefef;">
										<div align="left">' . $usergroups . '</div>
									</div>
									<div class="modal-footer modalBorders">
										<div align="center">
											<span id="modalAdd" class="btn btn-warning inputAdd" data-toggle="modal" data-target="#ModalAdd">Adicionar a grupos <i class="fa fa-plus fa-lg"></i></span>
											<span id="modalRmv"class="btn btn-danger" data-toggle="modal" data-target="#ModalRemove">Remover de grupos <i class="fa fa-times fa-lg"></i></span>
										</div>
									</div>
								</div>
							</div>
						</div>';
						
				echo $modal;
				
				$modaladd = '<div class="modal fade fontPlay" id="ModalAdd" tabindex="-1" role="dialog" aria-labelledby="imgmodal-label" aria-hidden="true">
							<div class="modal-dialog modal-md">
								<div class="modal-content">
									<div class="modal-header modalBorders">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 align="left" class="fontPlay"><span class="fa fa-plus fa-lg" style="color:green;"></span> Selecione os grupos que deseja adicionar para o colaborador.<br /></h3>
									</div>
									<div class="modal-body" align="center">
										<form class="form-horizontal" name="selectGroupsToUser" id="addGroups" role="form" method="post" action="insertGroups.php">
											<input style="display:none;" type="text" name="toinsertDNGroups" id="toinsertDNGroups" class="form-control" readonly></input>
											<strong>
												<input style="display:none;" type="text" name="searched" id="searched" class="form-control" readonly value=' . $account . '></input>
												<input style="display:none;" type="text" name="database" id="database" class="form-control" value="' . $dn . '" readonly></input>
												<input style="display:none;" type="text" name="database_user" id="database_user" class="form-control" value="' . $userDN . '" readonly></input>
											</strong>'.
											$selectGroups.
											'&nbsp;
											<button type="button" id="selectAdd" class="btn btn-info btn-sm inputAdd" onclick="inputGroups()">Adicionar <span class="fa fa-plus"></span></button>
											<br />
											<br />
											<b>Adicionar a:</b><br />
											<u><textarea readonly required rows="8" cols="50" id="toinsertGroups" name="toinsertGroups"></textarea></u>
											<div style="margin-top: 50px;">
												<button id="modalAplAdd" type="submit" class="btn btn-success">Aplicar <span class="fa fa-check-circle fa-lg"></span></button>	
												<button id="modalCncAdd" type="button" class="btn btn-danger" data-dismiss="modal">Cancelar <span class="fa fa-times-circle fa-lg"></span></button>
											</div>	
										</form>
									</div>
									<div class="modal-footer modalBorders">
										<i>Para confirmar a <b>inclus&atilde;o</b> clique em "Aplicar".</i>
									</div>
								</div>
							</div>
						</div>';
						
				echo $modaladd;
				
				$modalremove = '<div class="modal fade fontPlay" id="ModalRemove" tabindex="-1" role="dialog" aria-labelledby="imgmodal-label" aria-hidden="true">
							<div class="modal-dialog modal-md">
								<div class="modal-content">
									<div class="modal-header modalBorders">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 align="left" class="fontPlay"><span class="fa fa-times fa-lg" style="color:red;"></span> Selecione os grupos que deseja remover o colaborador.<br /></h3>
									</div>
									<div class="modal-body" align="center">
										<form class="form-horizontal" name="rmvGroupsToUser" id="rmvGroups" role="form" method="post" action="removeGroups.php">
											<input style="display:none;" type="text" name="tormvDNGroups" id="tormvDNGroups" class="form-control" readonly></input>
											<strong>
												<input style="display:none;" type="text" name="searched_rmv" id="searched_rmv" class="form-control" readonly value=' . $account . '></input>
												<input style="display:none;" type="text" name="database_rmv" id="database_rmv" class="form-control" value="' . $dn . '" readonly></input>
												<input style="display:none;" type="text" name="database_user_rmv" id="database_user_rmv" class="form-control" value="' . $userDN . '" readonly></input>
											</strong>'.
											$selectRmvGroups.
											'&nbsp;
											<button type="button" id="selectRmv" class="btn btn-danger btn-sm" onclick="rmvGroups()">Remover <span class="fa fa-times"></span></button>
											<br />
											<br />
											<b>Remover de:</b><br />
											<u><textarea readonly required rows="8" cols="50" id="tormvGroups" name="tormvGroups"></textarea></u>
											<div style="margin-top: 50px;">
												<button id="modalAplRmv" type="submit" class="btn btn-success">Aplicar <span class="fa fa-check-circle fa-lg"></span></button>	
												<button id="modalCncRmv" type="button" class="btn btn-danger" data-dismiss="modal">Cancelar <span class="fa fa-times-circle fa-lg"></span></button>
											</div>	
										</form>
									</div>
									<div class="modal-footer modalBorders">
										<i>Para confirmar a <b>remo&ccedil;&atilde;o</b> clique em "Aplicar".</i>
									</div>
								</div>
							</div>
						</div>';
						
				echo $modalremove;
		}
	//Fim do laço FOR para cada resultado obtdo em pesquisa LDAP=================================================================
		
		if ($i == 0) {
			echo "<b>N&atilde;o foram encontrados registros de operadores para esta opera&ccedil;&atilde;o!</b>";
		}
		ldap_close($lc);
			
	//Ordena valores do array HTML=============================================
		
		sort($resultArray);
		
	//Exibe resultado da pesquisa ordenados====================================
			
		for ($j = 0; $j < $i; $j++) {
			if($denied){
				echo "<div align='center'><h2 style='color:#bb0000;'><b class='fontPlay'>Acesso negado a contas administrativas ou de servi&ccedil;o!</b></h2></div><br />";
			}
			else{
				echo $resultArray[$j];
			}
		}
	} else {
		echo "<div align='center'><h2 style='color:#bb0000;'><b class='fontPlay'>Usu&aacute;rio n&atilde;o encontrado!</b></h2></div><br />";
	}
	
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
				<a href='home.php?filter=<?php echo $_SESSION['equipeFilter']; ?>' class="hoverLight return">
					<i class='fa fa-chevron-left fa-4x'></i>
				</a>
			</strong>
		</div>
		<div class="active-return">
			&nbsp;
		</div>
		
		<!--Início do footer op014.-->
		<footer class="bottomFooter" id="homefooter">
			<?php 
				echo $pageFooter;
			?>
		</footer>
		<!--Fim do footer cl014.-->

		<script src='./Scripts/jquery-2.1.4.min.js'></script>
		<script src='./Scripts/bootstrap.min.js'></script>
		<script src='./scripts/animated.js'></script>
		<script src='scripts/jQueryRollPlugin/jRoll.min.js'></script>
		<script src='./scripts/jquery-ui.js'></script>
				
	<?php
		if($base != "DC=call,DC=br"){
			echo '<script>
					$(function(){
						$("#searchUser").autocomplete({
							source: ' . json_encode($matriculas_arr) . ', minLength: 3
						});
					});
				</script>';
		}
	?>
				
	</body>
	
</html>


