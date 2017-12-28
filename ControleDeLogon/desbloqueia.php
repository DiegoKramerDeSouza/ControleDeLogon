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
							//$colablen = strlen($colaborador);
							//$returnex = explode(',', $dn, 2);
							//$returnbase = $returnex[1];
							$returnbase = $dbView;
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
													"<li class='active'><a href='#' id='sec' class='activetab' role='tab' data-toggle='tab'><b>Desbloqueio <i class='fa fa-unlock-alt fa-sm'></i></b></a></li>";
						if ($cargo == "Coordenador"){
								$headIndex = $headIndex.
													"<li class='tab-list'><a href='gerenciar_grupos.php' id='sec' role='tab'><b>Grupos <i class='fa fa-bookmark fa-sm'></i></b></a></li>";
						}
						else if ($cargo == "Supervisor"){
								$headIndex = $headIndex.
													"<li class='tab-list'><a href='equipe.php' id='sec' role='tab'><b>Equipe <i class='fa fa-users fa-sm'></i></b></a></li>";
						}
						$headIndex = $headIndex.
													"<li class='tab-list'><a href='mensagens.php' id='sec' role='tab'><b>Mensagens <i class='fa fa-envelope fa-sm'></i></b></a></li>".
													"<li class='tab-list'><a href='relatorios.php' id='sec' role='tab'><b>Relat&oacute;rios <i class='fa fa-file-text fa-sm'></i></b></a></li>".
												"</ul>
											</nav>
										</div>
									</div>";
					}
					echo $headIndex;
					echo "<div class='container'>".
							"<span><h2><b class='fontPlay'><i class='fa fa-unlock-alt'></i> Desbloqueio de contas:</b></h2></span>".
							"<div class='row'>".
								"<div class='col-xs-3 col-sm-2 col-md-1' align='right'>".
									"<span style='padding:35px;'>".
										$photo.
									"</span>".
								"</div>".
								"<div class='col-xs-6 col-sm-6 col-md-6 fontPlay' style='padding-left:25px;'>".
									"<b style='font-size:12px;'>Bem vindo: </b>" . $name . "<b><br />" . $dataAtual . "</b>".
								"</div>".
							"</div>".
							"<form class='form-horizontal' role='form' method='get' action='unlock.php'>".
								"<div class='row'>".
									"<div class='col-xs-12 col-sm-12 col-md-6 col-md-push-3 fontPlay'>".
										"<div class='form-group'>".
											"<label name='searchBar'><h3><b class='fontPlay'>Informe a conta do usu&aacute;rio:</b></h3></label>".
											"<div class='input-group'>".
												"<input type='text' name='searchUser' id='searchUser' class='form-control' placeholder=' Matr&iacute;cula (EX: c22123)' for='searchBar'></input>".
												"<input type='hidden' name='database_unlock' id='database_unlock' class='form-control' value='" . $returnbase . "' readonly></input>".
												"<div class='input-group-btn'>".
													"<button type='submit' id='searchSubmitBtn1' class='btn btn-primary'>".
														"<span class='glyphicon glyphicon-search'></span>".
													"</button>".
												"</div>".
											"</div>".
										"</div>".
									"</div>".
								"</div>".
							"</form>".
						"</div>";
						
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
		<div style="padding-top:80px;">&nbsp;</div>	
		<?php
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