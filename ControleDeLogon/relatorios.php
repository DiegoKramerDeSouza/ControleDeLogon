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
		<div class='container'>
			<div class='result'>
		
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
		
		$inicialize = false;
		if(isset($_POST["searchName"]) && isset($_POST["searchDate"])){
			$searchUser = $_POST["searchName"];
			$searchDate = $_POST["searchDate"];
			$inicialize = true;
		}
		
	//Cabeçalho de edição============================================================================================
		if ($cargo == "Especialista" || ($cargo == "Conta Administrativa" || $cargo == "Suporte Tecnico")){
			$headIndex = "<div class='container'>
							<div class='navbar-inverse side-collapse in navbarMenu'>
								<nav role='navigation' class='navbar-collapse'>
									<ul class='nav navbar-nav' role='tablist'>".
										"<li class='tab-list'><a href='home.php' id='sec' role='tab'><b>Início <i class='fa fa-home fa-sm'></i></b></a></li>".
										"<li class='active'><a href='relatorios.php' id='sec' class='activetab' role='tab' data-toggle='tab'><b>Relatórios <i class='fa fa-file-text fa-sm'></i></b></a></li>".
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
										"<li class='tab-list'><a href='mensagens.php' id='sec' role='tab'><b>Mensagens <i class='fa fa-envelope fa-sm'></i></b></a></li>".
										"<li class='active'><a href='relatorios.php' id='sec' class='activetab' role='tab' data-toggle='tab'><b>Relatórios <i class='fa fa-file-text fa-sm'></i></b></a></li>".
									"</ul>
								</nav>
							</div>
						</div>";
		}
		$tableXML = "";
		echo $headIndex;
		$head = "<div class='container'>".
					"<h2><b class='fontPlay'><i class='fa fa-file-text'></i> Informe os dados:</b></h2>".
					"<div class='row' style='margin-bottom:30px;'>".
						"<div class='col-xs-3 col-sm-2 col-md-1' align='right'>".
							"<span style='padding:35px;'>".
								$photo.
							"</span>".
						"</div>".
						"<div class='col-xs-6 col-sm-4 col-md-6 fontPlay' style='padding-left:25px;'>".
							"<b style='font-size:12px;'>Bem vindo: </b>" . $name . "<b><br />" . $dataAtual . "</b>".
						"</div>".
					"</div>".
					"<div class='admHead' style='margin-bottom:20px;'>".
						"<div class='row container'>".
							"<div class='col-xs-12 col-sm-12 col-md-12 fontPlay' align='right'>".
							//Formulário de pesquisa envolvendo Matrícula de usuário e Data de pesquisa------------------------------- 
								"<form class='form-horizontal' name='search' role='form' method='post' action='relatorios.php'>".
									"<div class='row'>".
										"<div class='col-xs-12 col-sm-6 col-md-5 form-group has-feedback has-feedback-left' align='left'>".
											"<div class='input-group' id='div_search' style='margin-left:15px; margin-right:15px; margin-top:5px; border-left: 5px solid #eee; padding:5px;'>".
												"<label>Matr&iacute;cula: </label>".
												"<input type='text' required name='searchName' id='search' class='form-control' autofocus placeholder='Matr&iacute;cula do colaborador'></input>".
												"<span class='form-control-feedback feedcolor'>".
													"<span class='fa fa-user fa-lg' style='margin-top:20px;'></span>".
												"</span>".
											"</div>".
										"</div>".
										"<div class='col-xs-12 col-sm-6 col-md-5 form-group has-feedback has-feedback-left' align='left'>".
											"<div class='input-group date' id='div_datepicker' style='margin-left:15px; margin-right:15px; margin-top:5px; border-left: 5px solid #eee; padding:5px;'>".
												"<label>Data:</label>".
												"<div class='input-group' id='datetime'>".
													"<input type='text' name='searchDate' required class='form-control' id='datepicker' data-provide='datepicker' placeholder='dd/mm/aaaa'></input>".
													"<span class='form-control-feedback feedcolor'>".
														"<span class='fa fa-calendar'></span>".
													"</span>".
												"</div>".
											"</div>".
										"</div>".
										"<div class='col-xs-2 col-sm-2 col-md-2'>".
											"<div align='right' style='margin-left:15px; margin-right:15px; margin-top:25px; margin-bottom:10px;'>".
												"<button class='btn btn-info btn-md' type='submit' id='pesquisar'>Pesquisar <i class='fa fa-search fa-lg'></i></button>".
											"</div>".
										"</div>".
									"</div>".
								"</form>".
							//Fim do formulário---------------------------------------------------------------------------------------
							"</div>".
						"</div>".
					"</div>";
		//Foram definidos os valores de Matrícula e Data-----------------------------------------
		if($inicialize){
			$setDate = explode("/", $searchDate, 3);
			$searchDate = (Int)$setDate[2] . "-" . (Int)$setDate[1] . "-" . (Int)$setDate[0];
			$archive = $searchUser . "_" . $searchDate . ".log";
			$path = "//call.br/servicos/LOGS/LogsForceLogoff/" . $archive;
			$pathXML = "//call.br/servicos/LogonLogoff/Usuarios/" . $setDate[2] . "-" . $setDate[1] . "/" . $setDate[0] . "/" . $searchUser . ".xml";
			if(file_exists($path)){
				$filecontent = file_get_contents($path);
			} else {
				$filecontent = "";
			}
			if(file_exists($pathXML)){
				$xmlContent = simplexml_load_file($pathXML);
			} else {
				$xmlContent = "";
			}
							
			$notFoundJornada = "<div class='fontPlay resultTable delayLoad' id='load01' style='margin-bottom:20px;'>".
						"<div class='panel panel-primary'>".
							"<div class='panel-heading'>".
								"<div class='row'>".
									"<div class='col-xs-2 col-sm-2 col-md-1'>".
										"<i class='fa fa-history fa-2x'></i>".
									"</div>".		
									"<div class='col-xs-10 col-sm-10 col-md-10' style='margin-top:5px;'>".
										"<h3 class='panel-title fontPlay'>Registro SCL - " . $setDate[0] . "/" . $setDate[1] . "/" . $setDate[2] . "</h3>".
									"</div>".
								"</div>".
							"</div>".
							"<div class='panel-body' style='background-color:#e0e0e0;'>".
								"<div class='row' align='center'>".
									"<i class='fa fa-times fa-2x red'></i>&nbsp;&nbsp;".
									"N&atilde;o foram encontrados registros no sistema SCL para este colaborador nesta data".
								"</div>".
							"</div>".
						"</div>".
					"</div>";
					
			$notFoundLogon = "<div class='fontPlay resultTable delayLoad' id='load02' style='margin-bottom:20px;'>".
						"<div class='panel panel-primary'>".
							"<div class='panel-heading' style='background-color: #0a0;'>".
								"<div class='row'>".
									"<div class='col-xs-2 col-sm-2 col-md-1'>".
										"<i class='fa fa-sign-in fa-2x'></i>".
									"</div>".		
									"<div class='col-xs-10 col-sm-10 col-md-10' style='margin-top:5px;'>".
										"<h3 class='panel-title fontPlay'>Registro de LOGON em esta&ccedil;&otilde;es de trabalho - " . $setDate[0] . "/" . $setDate[1] . "/" . $setDate[2] . "</h3>".
									"</div>".
								"</div>".
							"</div>".
							"<div class='panel-body' style='background-color:#e0e0e0;'>".
								"<div class='row' align='center'>".
									"<i class='fa fa-times fa-2x red'></i>&nbsp;&nbsp;".
									"N&atilde;o foram encontrados registros de logon em m&aacute;quinas para este colaborador nesta data".
								"</div>".
							"</div>".
						"</div>".
					"</div>";
					
			$notFoundLogoff = "<div class='fontPlay resultTable delayLoad' id='load03' style='margin-bottom:20px;'>".
						"<div class='panel panel-primary'>".
							"<div class='panel-heading' style='background-color: #a00;'>".
								"<div class='row'>".
									"<div class='col-xs-2 col-sm-2 col-md-1'>".
										"<i class='fa fa-sign-out fa-2x'></i>".
									"</div>".		
									"<div class='col-xs-10 col-sm-10 col-md-10' style='margin-top:5px;'>".
										"<h3 class='panel-title fontPlay'>Registro de LOGOFF em esta&ccedil;&otilde;es de trabalho - " . $setDate[0] . "/" . $setDate[1] . "/" . $setDate[2] . "</h3>".
									"</div>".
								"</div>".
							"</div>".
							"<div class='panel-body' style='background-color:#e0e0e0;'>".
								"<div class='row' align='center'>".
									"<i class='fa fa-times fa-2x red'></i>&nbsp;&nbsp;".
									"N&atilde;o foram encontrados registros de logoff em m&aacute;quinas para este colaborador nesta data".
								"</div>".
							"</div>".
						"</div>".
					"</div>";
										
			//Se os valores informados retornaram algo-------------
			if($filecontent != ""){
				$table = "";
				$contentLines = explode("#", $filecontent);
				foreach($contentLines as $lines){
					$horario = explode("|-|", $lines, 2);
					if(isset($horario[1])){
						$table = $table . "<tr><td>" . $horario[0] . "</td><td>" . $horario[1] . "</td></tr>";
					}
				}
				//INSERIR PESQUISA LDAP AQUI!!		<----------------
				$head = $head . 
					"<div class='fontPlay resultTable delayLoad' id='load01' style='margin-bottom:20px;'>".
						"<div class='panel panel-primary'>".
							"<div class='panel-heading'>".
								"<div class='row'>".
									"<div class='col-xs-2 col-sm-2 col-md-1'>".
										"<i class='fa fa-history fa-2x'></i>".
									"</div>".
									"<div class='col-xs-10 col-sm-10 col-md-10' style='margin-top:5px;'>".
										"<h3 class='panel-title fontPlay'>Registro SCL - " . $setDate[0] . "/" . $setDate[1] . "/" . $setDate[2] . "</h3>".
									"</div>".
								"</div>".
							"</div>".
							"<div class='panel-body' style='background-color:#e0e0e0;'>".
								"<div class='row'>".
									"<table class='table table-striped'>".
										"<thead>".
											"<tr>".
												"<th>Hora</th>".
												"<th>Descri&ccedil;&atilde;o</th>".
											"</tr>".
										"</thead>".
										"<tbody>".
											$table .
										"</tbody>".
									"</table>".
								"</div>".
							"</div>".
						"</div>".
					"</div>";
			} else {
				$head = $head . $notFoundJornada;
			}
			//Eventos de logon e logoff
			//===================================================================================
			if($xmlContent != "" && count($xmlContent->logon->evento->host) > 0){
				$table = "";
				for($i = 0; $i < count($xmlContent->logon->evento->host); $i++){
					$table = $table . "<tr><td>" . $xmlContent->logon->evento->host[$i] . "</td><td>" . $xmlContent->logon->evento->data[$i] . "</td><td>" . $xmlContent->logon->evento->IP[$i] . "</td></tr>";
				}
				$tableXML = $tableXML . 
					"<div class='fontPlay resultTable delayLoad' id='load02' style='margin-bottom:20px;'>".
						"<div class='panel panel-primary'>".
							"<div class='panel-heading' style='background-color: #0a0;'>".
								"<div class='row'>".
									"<div class='col-xs-2 col-sm-2 col-md-1'>".
										"<i class='fa fa-sign-in fa-2x'></i>".
									"</div>".
									"<div class='col-xs-10 col-sm-10 col-md-10' style='margin-top:5px;'>".
										"<h3 class='panel-title fontPlay'>Registro de LOGON em esta&ccedil;&otilde;es de trabalho - " . $setDate[0] . "/" . $setDate[1] . "/" . $setDate[2] . "</h3>".
									"</div>".
								"</div>".
							"</div>".
							"<div class='panel-body' style='background-color:#e0e0e0;'>".
								"<div class='row'>".
									"<table class='table table-striped'>".
										"<thead>".
											"<tr>".
												"<th>M&aacute;quina</th>".
												"<th>Hora/Data</th>".
												"<th>IP</th>".
											"</tr>".
										"</thead>".
										"<tbody>".
											$table .
										"</tbody>".
									"</table>".
								"</div>".
							"</div>".
						"</div>".
					"</div>";
			//Se não-----------------------------------------------
			} else {
				$tableXML = $tableXML . $notFoundLogon;
			}
			if($xmlContent != "" && count($xmlContent->logoff->eventooff->host) > 0){
				$table = "";
				for($i = 0; $i < count($xmlContent->logoff->eventooff->host); $i++){
					$table = $table . "<tr><td>" . $xmlContent->logoff->eventooff->host[$i] . "</td><td>" . $xmlContent->logoff->eventooff->data[$i] . "</td><td>" . $xmlContent->logoff->eventooff->IP[$i] . "</td></tr>";
				}	
				$tableXML = $tableXML . 
					"<div class='fontPlay resultTable delayLoad' id='load03' style='margin-bottom:100px;'>".
						"<div class='panel panel-primary'>".
							"<div class='panel-heading' style='background-color: #a00;'>".
								"<div class='row'>".
									"<div class='col-xs-2 col-sm-2 col-md-1'>".
										"<i class='fa fa-sign-out fa-2x'></i>".
									"</div>".
									"<div class='col-xs-10 col-sm-10 col-md-10' style='margin-top:5px;'>".
										"<h3 class='panel-title fontPlay'>Registro de LOGOFF em esta&ccedil;&otilde;es de trabalho - " . $setDate[0] . "/" . $setDate[1] . "/" . $setDate[2] . "</h3>".
									"</div>".
								"</div>".
							"</div>".
							"<div class='panel-body' style='background-color:#e0e0e0;'>".
								"<div class='row'>".
									"<table class='table table-striped'>".
										"<thead>".
											"<tr>".
												"<th>M&aacute;quina</th>".
												"<th>Hora/Data</th>".
												"<th>IP</th>".
											"</tr>".
										"</thead>".
										"<tbody>".
											$table .
										"</tbody>".
									"</table>".
								"</div>".
							"</div>".
						"</div>".
					"</div>";
			//Se não-----------------------------------------------
			} else {
				$tableXML = $tableXML . $notFoundLogoff;
			}
		}		
		echo $head;
		echo $tableXML;
		
	//Fim - Cabeçalho de edição=======================================================================================
	
	
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
		<footer class="bottomFooter" id="homefooter">
			<?php 
				echo $pageFooter;
			?>
		</footer>

		<script src='./Scripts/jquery-2.1.4.min.js'></script>
		<script src='./Scripts/bootstrap.min.js'></script>
		<script src='./scripts/animated.js'></script>
		<script src='./scripts/jQueryRollPlugin/jRoll.min.js'></script>
		<script src='./scripts/jquery-ui.js'></script>
		<script>
			$(function(){
				$("#datepicker").datepicker({
					dateFormat: 'dd/mm/yy',
					changeMonth: true,
					changeYear: true
				});
			});
			$("#load01").fadeIn(500);
			$("#load02").fadeIn(1000);
			$("#load03").fadeIn(1500);
		</script>
		
				
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


