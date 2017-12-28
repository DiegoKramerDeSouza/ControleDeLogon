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
				<span id="closebtn" onclick="clearMsgBox()"><i class="fa fa-times fa-2x black"></i></span>
				<span id="msgHeader"></span><br />
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
		
		date_default_timezone_set("America/Sao_Paulo");
		$nao = "<strong style='font-size:18px; color: #ff0000;'>N&atilde;o</strong>";
		$sim = "<strong style='font-size:18px; color: #00ff00;'>Sim</strong>";
		$exTempo = 0;
		
		if(isset($_GET["filter"])){
			$_SESSION['equipeFilter'] = $_GET["filter"];
		} else {
			$_SESSION['equipeFilter'] = "*";
		}
		$equipeFilter = $_SESSION['equipeFilter'];
		if ($equipeFilter != "*"){
			$optMinhaEquipe = true;
		} else {
			$optMinhaEquipe = false;
		}
		//Variáveis de apresentação HTML
		$html = "";
		$resultArray = array();
				
		//Cabeçalho da pesquisa==============================================================
		echo "</div>".
				"<div class='container'>";
				
		$head =	"<div class='row container-fluid'>".
					"<div class='col-xs-12 col-sm-12 col-md-3 col-md-push-9'>".
						"<form class='form-horizontal' role='form' method='post' action='user.php'>".	
							"<div class='form-group'>".
								"<div class='input-group'>".
									"<input type='text' name='search' id='search' class='form-control' autofocus  placeholder=' Matr&iacute;cula (EX: c22123)'></input>".
									"<div class='input-group-btn'>".
										"<button type='submit' id='searchSubmitBtn3' class='btn btn-primary'>".
											"<span class='glyphicon glyphicon-search'></span>".
										"</button>".
									"</div>".
								"</div>".
							"</div>".
						"</form>".
					"</div>".
					"<div class='col-md-6 col-md-pull-3'>".
							"<h3 class='fontPlay'>".
								"<b>".
									"<i class='fa fa-clock-o'></i> Hor&aacute;rio de login dos operadores:".
								"</b>".
							"</h3>".						
					"</div>".
				"</div>".
				"<div class='row userNow'>".
					"<div class='col-xs-3 col-sm-2 col-md-1'>".
						"<span style='padding:35px;'>".
							$photo.
						"</span>".
					"</div>".
					"<div class='col-xs-7 col-sm-7 col-md-6 fontPlay' style='padding-left:25px;'>".
						"<b style='font-size:12px;'>Bem vindo: </b>" . $name . "<b style='font-size:12px;'><br />" . $dataAtual . "</b>".
					"</div>";
					if ($cargo == "Supervisor"){
						if($optMinhaEquipe){
							$opt2Style = "style='border-right: 10px solid rgb(44, 125, 180);'";
							$opt1Style = "";
						} else {
							$opt2Style = "";
							$opt1Style = "style='border-right: 10px solid rgb(44, 125, 180);'";
						}
						$head = $head .
							"<div class='col-xs-12 col-sm-3 col-sm-push-0 col-md-3 col-md-push-2 fontPlay' style='text-align:right; top:30px;'>".
								"<div class='dropdown'>".
									"<button class='btn btn-default dropdown-toggle ddButton' type='button' id='dropMenu1' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>".
										"<span>Filtrar <i class='fa fa-filter fa-lg'></i></span>".
									"</button>".
									"<ul class='dropdown-menu' style='right:0px;' id='dropMenu' aria-labelledby='dropMenu1'>".
										"<li class='filterType'>".
											"<blockquote " . $opt1Style .">".
												"<a href='home.php'>".
													"<p><span class='fa fa-globe'></span> Toda a opera&ccedil;&atilde;o</p>".
												"</a>".
											"</blockquote>".
										"</li>".
										"<li class='filterType'>".
											"<blockquote " . $opt2Style .">".
												"<a href='home.php?filter=equipe'>" .
													"<p><span class='fa fa-users'></span> Apenas minha equipe</p>".
												"</a>".
											"</blockquote>".
										"</li>".
									"</ul>".
								"</div>".
							"</div>";
					}
		$head = $head .	
				"</div>".
				"<br />"/*.
				"<div class='admHead'>".
					"<strong>".
					"<div class='row '>".
						/*"<div class='col-xs-1' align='center'>".
							"&nbsp;".
						"</div>".
						"<div class='col-xs-11'>".
							"<b>".
							"<div class='col-xs-3 col-sm-3 col-md-2'><p>Matr&iacute;cula:</p></div>".
							"<div class='col-xs-4 col-sm-4 col-md-7'><p>Operador:</p></div>".
							"<div class='col-xs-4 col-sm-4 col-md-3' align='right'><p>Hora extra:</p></div>".
							"</b>".
						"</div>".
					"</div>".
					"</strong>".
				"</div>"*/;
		//Pesquisa LDAP======================================================================
		//Filtro para pesquisa LDAP 
		if ($optMinhaEquipe){
			$filt = '(&(objectClass=User)(objectCategory=Person)(extensionAttribute4=' . $login . '))';
		} else {
			$filt = '(&(objectClass=User)(objectCategory=Person))';
		}
		$_SESSION['ldapFilter'] = $filt;
		$returnbase = $dbView;
		$head01 = 	"<h2 class='fontPlay'><b><i class='fa fa-unlock-alt fa-sm'></i> Desbloqueio de contas:</b></h2>".
					"<div class='row fontPlay'>".
						"<div class='col-xs-3 col-sm-2 col-md-1' align='right'>".
							"<span style='padding:35px;'>".
								$photo .
							"</span>".
						"</div>".
						"<div class='col-xs-6 col-sm-6 col-md-6' style='padding-left:25px;'>".
							"<b style='font-size:12px;'>Bem vindo: </b>" . $name . "<b><br />" . $dataAtual . "</b>".
						"</div>".
					"</div>".
					"<form class='form-horizontal' role='form' method='get' action='unlock.php'>".
						"<div class='row' style='padding-bottom:80px;'>".
							"<div class='col-xs-12 col-sm-12 col-md-6 col-md-push-3 fontPlay'>".
								"<div class='form-group'>".
									"<label name='searchBar'><span class='lg'><b class='fontPlay'>Informe a conta do usu&aacute;rio:</b></span></label>".
									"<div class='input-group'>".
										"<input type='text' autofocus name='searchUser' id='searchUser' class='form-control' placeholder=' Matr&iacute;cula (EX: c22123)' for='searchBar'></input>".
										"<input type='hidden' name='database_unlock' id='database_unlock' class='form-control' value='" . $returnbase . "' readonly></input>".
										"<div class='input-group-btn'>".
											"<button type='submit' id='searchSubmitBtn4' class='btn btn-primary'>".
												"<span class='glyphicon glyphicon-search'></span>".
											"</button>".
										"</div>".
									"</div>".
								"</div>".
							"</div>".
						"</div>".
					"</form>";
		$head02 = 	"<h2 class='fontPlay'><b><i class='fa fa-clock-o'></i> Gerenciar tempo de login:</b></h2>".
					"<div class='row fontPlay'>".
						"<div class='col-xs-3 col-sm-2 col-md-1' align='right'>".
							"<span style='padding:35px;'>".
								$photo.
							"</span>".
						"</div>".
						"<div class='col-xs-6 col-sm-6 col-md-6' style='padding-left:25px;'>".
							"<b style='font-size:12px;'>Bem vindo: </b>" . $name . "<b><br />" . $dataAtual . "</b>".
						"</div>".
					"</div>".
					"<form class='form-horizontal' role='form' method='get' action='user.php'>".
						"<div class='row' style='padding-bottom:80px;'>".
							"<div class='col-xs-12 col-sm-12 col-md-6 col-md-push-3 fontPlay'>".
								"<div class='form-group'>".
									"<label name='searchBar'><span class='lg'><b class='fontPlay'>Informe a conta do usu&aacute;rio:</b></span></label>".
									"<div class='input-group'>".
										"<input type='text' autofocus name='search' id='search' class='form-control' placeholder=' Matr&iacute;cula (EX: c22123)' for='searchBar'></input>".
										"<div class='input-group-btn'>".
											"<button type='submit' id='searchSubmitBtn5' class='btn btn-primary'>".
												"<span class='glyphicon glyphicon-search'></span>".
											"</button>".
										"</div>".
									"</div>".
								"</div>".
							"</div>".
						"</div>".
					"</form>";
		if(!$operationAccount && $cargo == "Administrativo"){
			echo $head01;
		}
		if($dbView == "DC=call,DC=br"){
			$returnbase = $dbView;
			$head = "";
			
			if ($cargo == "Especialista" || ($cargo == "Conta Administrativa" || $cargo == "Suporte Tecnico")){
				$headIndex = "<div class='col-sm-12 col-md-8 tabOpt' style='margin-bottom:5px;'>".
								"<div class='navbar-inverse side-collapse in navbarMenu'>
									<nav role='navigation' class='navbar-collapse'>
										<ul class='nav navbar-nav' role='tablist'>".
											"<li class='tab01 active'><a href='#tab1' id='sec' role='tab' data-toggle='tab'><b>Desbloqueio <i class='fa fa-unlock-alt fa-sm'></i></b></a></li>". 
											"<li class='tab02'><a href='#tab2' id='sec' role='tab' data-toggle='tab'><b>Tempo de Login <i class='fa fa-clock-o fa-sm'></i></b></a></li>".
											"<li class='tab05'><a href='relatorios.php' id='sec' role='tab'><b>Relat&oacute;rios <i class='fa fa-file-text fa-sm'></i></b></a></li>".
										"</ul>
									</nav>
								</div>".
								"<div class='tab-content'>".
									"<div class='active tab-pane fade in' id='tab1'>"; //Painel 01--------------------
										echo $headIndex;
						$headIndex = "</div>".//Fim - Painel 01-----------------------------------------------------
								"		<div class='active tab-pane fade' id='tab2'>". //Painel 02--------------------
										$head02.
								"		</div>".//Fim - Painel 02-----------------------------------------------------
								"	</div>
								</div>";
			}

			echo $head01;
			
			if ($cargo == "Especialista" || ($cargo == "Conta Administrativa" || $cargo == "Suporte Tecnico")){
				echo $headIndex;
			}
			
		} else if ($operationAccount) {
			//Exibe página modelada====================================================		
			//Abas de acesso-------------------------------------------------------
			$headIndex = "<div class='col-sm-12 col-md-8 tabOpt' style='margin-bottom:5px;'>".
							"<div class='navbar-inverse side-collapse in navbarMenu'>
								<nav role='navigation' class='navbar-collapse'>
									<ul class='nav navbar-nav' role='tablist'>".
										"<li class='active'><a href='#tab1' id='sec' class='activetab' role='tab' data-toggle='tab'><b>Tempo <i class='fa fa-clock-o fa-sm'></i></b></a></li>". 
										"<li class='tab-list'><a href='desbloqueia.php' id='sec' role='tab'><b>Desbloqueio <i class='fa fa-unlock-alt fa-sm'></i></b></a></li>";
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
							</div>".
						"<div class='tab-content'>".
						"<div class='active tab-pane fade in' id='tab1'>"; //Painel 01-----------------------------------------------------------------
							
			echo $headIndex . $head;
			
			echo "<div id='generateContent' style='display:none;'>";
			echo "</div>";
			echo "<div id='generateContentLoad' style='display:none;'>";
			echo "</div>";
			//Exibe restante da página modelada
			$head = 	"</div>".
					"</div>".
				"</div>".
				"<span style='padding-bottom:100px;'>&nbsp;</span>";
			echo $head;
		}		
	//Fecha conexão LDAP
		ldap_close($lc);
	//=========================================================================
	?>
			</div>
		</div>
		<div id="toTop" align="center">
			<span class="fa fa-chevron-up fa-2x" id="upToTop"></span><br />
		</div>
		<div class="bottomFooter" id="homefooter">
			<?php 
				echo $pageFooter;
			?>
		</div>
		
		<script src='./Scripts/jquery-2.1.4.min.js'></script>
		<script src='./Scripts/bootstrap.min.js'></script>
		<script src='./Scripts/bootstrap-toggle.min.js'></script>
		<script src='./scripts/animated.js'></script>
		<script src='./scripts/jQueryRollPlugin/jRoll.min.js'></script>
		<script src='./scripts/jquery-ui.js'></script>
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
		
		<?php
			if($operationAccount){
				echo "<script>
						fixFooter();
						getContent();
						$(document).ready(function(){
							fixFooter();
							setInterval(function() {
								//getContent();
								location.reload(true);
							}, 60000);
						});
						$('#generateContentLoad').fadeIn(1000);
						function getContent(){
							$('#generateContent').hide();
							var target = 0;
							var xhttp = new XMLHttpRequest();
							xhttp.onreadystatechange = function(){
								if(this.readyState == 4 && this.status == 200){
									$('#generateContentLoad').hide();
									document.getElementById('generateContent').innerHTML = this.responseText;";
									if($_SESSION['equipeFilter'] == "equipe"){
										echo "fixFooter();";
									} else {
										echo "unfixFooter();";
									}
									echo "$('#generateContent').fadeIn(1500);
								} else {
									document.getElementById('generateContentLoad').innerHTML = '<div align=\"center\" style=\"margin-top:50px;\">'+
																								'<span class=\"fontPlay lg\">'+
																									'Carregando listagem...<br />'+
																									'<span class=\"fa-stack fa-lg\">'+
																										'<i class=\"fa fa-stack-2x fa-circle green\"></i>'+
																										'<i class=\"fa fa-stack-1x fa-repeat fa-inverse fa-lg white spinner_fst\"></i>'+
																									'</span>'+
																								'</span>'+
																							'</div>';
								}
							};
							xhttp.open('GET', 'geral.php?target=' + target, true);
							xhttp.send();
						}
					  </script>";
			} else {
				if($depto == "TI" || ($depto == "DAP" || ($depto == "Comercial" || ($depto == "DH" || ($depto == "Financeiro" || $depto == "Superintendencia"))))){
					echo "<script>fixFooter()</script>";
				} else {
					echo "<script src='./scripts/pagereload.js'></script>";
				}
			}
		?>		
	</body>
</html>


