<?php
//Configurações de página---------------------------------------------------------
$htmlHeader = '<header>
					<meta charset="uft-8">
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					
					<title>SCL</title>
					
					<link rel="shortcut icon" href="Images/Hexagon.png">
					<link rel="stylesheet" href="./styles/bootstrap_free.css" />
					<link rel="stylesheet" href="./styles/font-awesome.css" />
					<link rel="stylesheet" href="./styles/style.css" />
					
					<link rel="stylesheet" href="./scripts/jquery-ui.css">
					
					<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
					<!-- WARNING: Respond.js doesnt work if you view the page via file:// -->
					<!--[if lt IE 9]>
						<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
						<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
						<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
					<![endif]-->
				</header>';

$htmlHeaderIndex = '<header>
					<meta charset="uft-8">
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					
					<title>SCL</title>
					
					<link rel="shortcut icon" href="./ControleDeLogon/Images/Hexagon.png">
					<link rel="stylesheet" href="./ControleDeLogon/styles/bootstrap_free.css" />
					<link rel="stylesheet" href="./ControleDeLogon/styles/font-awesome.css" />
					<link rel="stylesheet" href="./ControleDeLogon/styles/style.css" />
					<link rel="stylesheet" href="./ControleDeLogon/scripts/jquery-ui.css">
					
				</header>';
//---------------------------------------------------------				
$pageHeader = '<a href="./home.php" class="navbar-brand bottomUp">
					<span class="logo"><img src="./Images/BCKG_Hexagon.png" id="hexIcon" /></span>
				</a>
				<a href="./home.php" class="navbar-brand">
					<div id="imgLogon" style="font-size:34px; font-family:\'Play\', Berlin Sans FB, Impact, Arial Black;"> <span class="white">S</span><span class="lblue">CL</span></div><br />
					<div class="sm white collapse navbar-collapse" style="margin-left:35px; margin-top:5px;">Sistema de <span class="lblue">Controle de Logon</span></div>
				</a>';
				
$pageHeaderIndex = '<a href="./home.php" class="navbar-brand bottomUp">
					<span class="logo"><img src="./Images/BCKG_Hexagon.png" id="hexIcon" /></span>
				</a>
				<a href="./home.php" class="navbar-brand">
					<div id="imgLogon" style="font-size:34px; font-family:\'Play\', Berlin Sans FB, Impact, Arial Black;"> <span class="white">S</span><span class="lblue">CL</span></div><br />
					<div class="sm white collapse navbar-collapse" style="margin-left:35px; margin-top:5px;">Sistema de <span class="lblue">Controle de Logon</span></div>
				</a>';
//---------------------------------------------------------				
$pageHeaderIndexRoot = '<a href="./home.php" class="navbar-brand bottomUp">
							<span class="logo"><img src="./ControleDeLogon/Images/BCKG_Hexagon.png" id="hexIcon" /></span>
						</a>
						<a href="./home.php" class="navbar-brand">
							<span id="imgLogon" style="font-size:34px; font-family:\'Play\', Berlin Sans FB, Impact, Arial Black;"> <span class="white">S</span><span class="lblue">CL</span></span><br />
							<span class="sm white collapse navbar-collapse" style="margin-left:35px; margin-top:5px;">Sistema de <span class="lblue">Controle de Logon</span></span>
						</a>';
				
$pageHeaderRight = '<div class="collapse navbar-collapse navbar-text navbar-right" id="NavButtons" style="border:none;">
						<a style="text-decoration: none" href="../Documentos/web/viewer.html?file=Manual_de_Uso_Jornada_REV02.pdf" target="_blank">
							<span id="tutorial" class="btn btn-info btn-sm noDecoration">
								<span>Ajuda <i class="fa fa-question"></i></span>
							</span>
						</a>
						<a href="logout.php">
							<button id="leave" type="button" class="btn btn-danger btn-sm logoff">
								<span>Sair <i class="fa fa-sign-out"></i></span>
							</button>
						</a>
						<span id="alert_Text"></span>
					</div>';
//---------------------------------------------------------					
$pageFooter = '<div class="row">
					<div class="col-xs-10 col-sm-10 col-md-6 " style="margin-top:-5px;">
						<span class="collapse navbar-collapse"><span class="sm">&copy; 2016</span> &nbsp; <span class="sm">Equipe de <span class="lblue">Colaboração de Serviços</span></span></span>
					</div>
					<div class="col-xs-12 col-sm-2 col-md-6 footIconDiv" style="margin-right:0px; text-align:right;" align="right">
						<span class="navbar-right">
							<img src="./Images/BCKG_Hexagon.png" class="footIcon"/>
						</span>
					</div>
				</div>';

$pageFooterIndex = '<div class="row">
					<div class="col-xs-10 col-sm-10 col-md-6 " style="margin-top:-5px;">
						<span class="collapse navbar-collapse"><span class="sm">&copy; 2016</span> &nbsp; <span class="sm">Equipe de <span class="lblue">Colaboração de Serviços</span></span></span>
					</div>
					<div class="col-xs-12 col-sm-2 col-md-6 footIconDiv" style="margin-right:0px; text-align:right;" align="right">
						<span class="navbar-right">
							<img src="./ControleDeLogon/Images/BCKG_Hexagon.png" class="footIcon"/>
						</span>
					</div>
				</div>';
//---------------------------------------------------------
$pageNavbar = "	<div class='navbar navbar-inverse navbar-fixed-top' role='navegation' >
					<div class='container-fluid'>
						<span title='Opções' class='navbar-toggle' style='background-color:transparent; color:white; border:2px solid white;' id='NavOpc' data-toggle='collapse' data-target='#NavButtons'><span class='fa fa-power-off'></span></span>
						<span title='Menu' class='navbar-toggle' style='background-color:transparent; color:white; border:2px solid white;' id='NavMen' data-toggle='collapse-side' data-target='.side-collapse'><span class='fa fa-list-ul'></span></span>
						<div class='navbar-header'>" .
							$pageHeader .
						"</div>" .
						 $pageHeaderRight .
					"</div>
				</div>";

$htmlFooter = "	<footer class='bottomFooter' id='homefooter'>
					" . $pageFooter . "
				</footer>
				<script src='./Scripts/jquery-2.1.4.min.js'></script>
				<script src='./Scripts/bootstrap.min.js'></script>
				<script src='./Scripts/bootstrap-toggle.min.js'></script>
				<script src='./scripts/animated.js'></script>
				<script src='./scripts/jquery-ui.js'></script>";
				
$htmlloading = "<div id='loading'>
					<div id='dialogLoading'>
						<span style='position:relative; left:-30px; font-size:48px;' class='fontPlay'>Aguarde...</span>
						<div id='loadGif' align='center'>
							<i class='fa fa-cog fa-spin blue fa-fw' style='text-shadow: 0px 0px transparent; font-size:120px;'></i>
							<span class='sr-only'>Loading...</span>
						</div>
					</div>
				</div>";
				
?>