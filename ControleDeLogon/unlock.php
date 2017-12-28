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
		
		<!-- DISPOSIÇÃO DOS DEMAIS CONTAÚDOS NOS LIMITES "CONTAINER"-->

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
		$returnbase = $_GET['database_unlock'];
		$base = $returnbase;
		
		date_default_timezone_set("America/Sao_Paulo");
		
		//Variáveis de apresentação HTML
		$html = "";
		$resultArray = array();
	
	//Filtro para pesquisa LDAP ================================
		//Filtro
		$filt = '(&(objectClass=User)(sAMAccountname=' . $strUser . '))';
		//Search
		$sr = ldap_search($lc, $base, $filt);
		//Organiza
		$sort = ldap_sort($lc, $sr, 'name');
		//Recolhe entradas
		$info = ldap_get_entries($lc, $sr);
	//==========================================================
	
	//Cabeçalho da pesquisa============================================================================================	
		$head = "<div class='container result'>".
						"<h2><b class='fontPlay'><i class='fa fa-unlock-alt'></i> Desbloqueio de contas:</b></h2>".
						"<div class='row container'>".
							"<div class='col-xs-3 col-sm-2 col-md-1' align='right'>".
								"<span style='padding:35px;'>".
									$photo.
								"</span>".
							"</div>".
							"<div class='col-xs-6 col-sm-4 col-md-5 fontPlay' style='padding-left:25px;'>".
								"<b style='font-size:12px;'>Bem vindo: </b>" . $name . "<b><br />" . $dataAtual . "</b>".
							"</div>".
							"<div class='col-xs-12 col-sm-6 col-md-5 col-md-push-1' align='center'>".
								"<form class='form-horizontal' role='form' method='get' action='unlock.php'>".
									"<div class='row'>".
										"<div class='col-xs-12 col-sm-12 col-md-12'>".
											"<div class='form-group'>".
												"<h3><b class='fontPlay'>Informe a conta do usu&aacute;rio:</b></h3>".
												"<div class='input-group'>".
													"<input type='text' name='searchUser' id='searchUser' autofocus class='form-control' placeholder=' Matr&iacute;cula (EX: c22123)'></input>".
													"<input style='display:none;' type='text' name='database_unlock' id='database_unlock01' class='form-control' value='" . $returnbase . "' readonly></input>".
													"<div class='input-group-btn'>".
														"<button type='submit' class='search-user btn btn-primary'>".
															"<span class='glyphicon glyphicon-search'></span>".
														"</button>".
													"</div>".
												"</div>".
											"</div>".
										"</div>".
									"</div>".
								"</form>".
							"</div>".
							
						"</div>".
						
						"<div class='container'>".
							"<div class='row'>".
								"<strong>".
									"<div class='col-xs-12 col-sm-12 col-md-12 admHead'>".
										"<div class='col-xs-3 col-sm-3 col-md-3'><p>Matr&iacute;cula:</p></div>".
										"<div class='col-xs-4 col-sm-4 col-md-4'><p>Operador:</p></div>".
										"<div class='col-xs-4 col-sm-4 col-md-5' align='center'><p>Desbloquear Conta:</p></div>".
									"</div>".
								"</strong>".
							"</div>".
						"</div>";
		
		echo $head;
	//Fim - Cabeçalho da pesquisa=======================================================================================
	if(isset($info[0]["samaccountname"][0])){
		
		if($ldapB){
			//Listagem de usuários retornados ==========================
		//Início do laço FOR para cada resultado obtdo em pesquisa LDAP==============================================================
			for ($i = 0; $i < $info["count"]; $i++) {			
				$unlock = 0;
				$lock = "";
				$denied = false;
				$verifyDenied = strpos("_" . $login, 'a');
				$contaAdm = false;
				$blocked = false;
				$account = $info[$i]["samaccountname"][0];
				$colaborador = $info[$i]["cn"][0];
				$dn = $info[$i]["distinguishedname"][0];
				
				if(strpos($dn, "Contas Administrativas") != ""){
					$denied = true;
					$contaAdm = true;
				}
				if ($verifyDenied === 1 && $cargo != "Contas Administrativas"){
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
				if ($unlock != 0 ){
					$blocked = true;
				}
				
				if($blocked){
					$lock = "<span class='blockalert btn btn-primary btn-sm' disabled>Conta bloqueada.</span>".
							"<button type='submit' class='btn btn-info btn-sm' style='margin:5px;'>".
								"Desbloquear <span class='fa fa-unlock-alt'></span>".
							"</button>";
					$textarea = "style='color:black;'";
				} else {
					$lock = "<span class='unblockalert btn btn-primary btn-sm' disabled>Conta n&atilde;o bloqueada.</span>".
							"<button type='submit' disabled class='btn btn-danger btn-sm' style='margin:5px;'>".
								"Desbloquear <span class='fa fa-unlock-alt'></span>".
							"</button>";
					$textarea = "readonly style='color:black; background-color:#c0c0c0;'";
				}
				
				
			//==============================================================
			
			}
				$html = $html.
						"<div class='container informativoUser' id='" . $account . "_unlock'>".
							"<div class='row' style='position:relative; padding-top:5px;'>".
								"<div class='col-xs-12 col-sm-12 col-md-12'>".
									"<form class='form-horizontal' role='form' method='get' action='unlockAccount.php'>".
										"<div class='row'>".
											"<div class='col-xs-4 col-sm-4 col-md-3' align='left'>".
												"<strong>".
												"<input type='text' name='searched' id='searched' class='form-control' value='" . $account . "' readonly></input>".
												"<input style='display:none;' type='text' name='database_unlock' id='database_unlock02' class='form-control' value='" . $returnbase . "' readonly></input>".
												"</strong>".
											"</div>".
											"<div class='col-xs-4 col-sm-3 col-md-6'>".
												"<p><u>" . $colaborador . "</u></p>";
												if ($contaAdm){
													$html = $html . "<p><b>Motivo:<br /><textarea required name='motivo' row='5' cols='40' id='motivo' " . $textarea . " class='motivoTxtArea'></textarea>"; //"OU: " . $origin . "</b></p>".
												}
				$html = $html . "</div>".
											"<div class='col-md-2'>".
												"<p>" . $lock . "</p>".
											"</div>".
										"</div>".
									"</form>".
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
		<div style="padding-top:80px;">&nbsp;</div>	
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
							source: ' . json_encode($_SESSION['users']) . ', minLength: 3
						});
					});
				</script>';
		}
	?>
				
	</body>
	
</html>


