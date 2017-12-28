<!DOCTYPE html>
<html>
	<?php
		require_once("pageInfo.php");
		echo $htmlHeader;
	?>
	<body style="margin-top:80px;">
		<?php
			$isSessionUp = false;
			if(session_id() == '') {
				session_start();
			}
			if (isset($_SESSION['matricula']) && isset($_SESSION['senha'])){
				$isSessionUp = true;
			}
			echo $htmlloading;
		?>
		<!-- Mensagens de tela-->
		<div id="msgDiv" align="right">
			<div id="dialog" align="center">
				<span id="closebtn" onclick="clearMsgBox()"><i class="fa fa-times fa-2x"></i></span>
				<span id="msgHeader"></span><br />
				<hr />
				<span id="msgBody"></span><br />
			</div>
		</div>
		<!-- Fim Mensagens de tela -->
		<div class="container">
			<div class="row">
				<div class="access col-xs-12 col-sm-8 col-md-6 col-sm-push-2 col-md-push-3">
					<div id="loginForm" align="center">
						<div class="indexPhoto">
							<?php 
								if($isSessionUp){
									echo $_SESSION['initphoto'];
								} else {
									echo "<img class='img-circle' src='./Images/user_icon.png' style='width:100px; height:100px;' />";
								}
							?>
						</div>
						<?php
							if($isSessionUp){
								echo "	<h1 class='blue fontPlay cardStyle'>
											<span class='fa fa-user'></span> Sua sessão está ativa
											<br />
											<a href='home.php' target='_self' class='green'>Prosseguir <span class='fa fa-arrow-right'></span></a>
										</h1>";
							} else {
								echo "	<form class='form-horizontal'  role='form' name='acessar' method='post' action='Access.php'>
											<label for='matricula' class='control-label'> <span class='glyphicon glyphicon-user feedcolor'></span> Usu&aacute;rio:</label>
											<input type='text' required name='matricula' id='matricula' class='form-control' autofocus placeholder=' Matr&iacute;cula' style='max-width:350px;'>
											
											<label for='senha' class='control-label'> <span class='glyphicon glyphicon-lock feedcolor'></span> Senha:</label>
											<input type='password' required name='senha' id='senha' class='form-control' placeholder=' Senha' style='max-width:350px;'>
												<div class='form-group' style='position:relative; top:90px;'>
												<button type='submit' id='indexLoad' class='btn btn-success bgdgreen btn-lg submitbtn indexLoad' style='margin-right:5px;' onclick='loading()'><i class='fa fa-sign-in'></i> Entrar </button>
												<span id='helpLoad' class='btn btn-info bgblue btn-lg submitbtn' style='margin-left:5px;' data-toggle='modal' data-target='#helpBox'><i class='fa fa-info-circle'></i> Informa&ccedil;&atilde;o </span>
											</div>
										</form>";
							}
						?>
										
					</div>
					<span id="alert_Text" style="display:none"></span>				
				</div>
			</div>
		</div>

		<div class="navbar navbar-inverse navbar-fixed-top" role="navegation">
			<div class="container-fluid">
				<div class="navbar-header container">
					<?php 
						echo $pageHeaderIndex;
					?>
					<div class="collapse navbar-collapse navbar-text navbar-right" id="NavButtons" style="position:fixed; right:20px; top:-10px;">
						<a style="text-decoration: none" href="../Documentos/web/viewer.html?file=Manual_de_Uso_Jornada_REV02.pdf" target="_blank">
							<span id="tutorial" class="btn btn-info btn-sm noDecoration">
								Ajuda <i class="fa fa-question"></i>
							</span>
						</a>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade" id="helpBox" tabindex="-1" role="dialog" aria-labelledby="imgmodal-label" aria-hidden="true">
			<div class="modal-dialog modal-md">
				<div class="modal-content">
					<div class="modal-header modalBorders">
						<h3 class='fontPlay'>Para acessar:</h3>
					</div>
					<div class="modal-body" align="left" style="background-color:#0055af; color:#ffffff;">
						<h4 class='fontPlay'>
						<br />
						<p><span class="glyphicon glyphicon-hand-right"></span> Informe o seu usu&aacute;rio, "C" e a sua matr&iacute;cula, no campo "Usu&aacute;rio". EX:(c22123)</p>
						<p><span class="glyphicon glyphicon-hand-right"></span> Informe a sua senha no campo "Senha".</p>
						<p><span class="glyphicon glyphicon-hand-right"></span> Confirme clicando em "Entrar".</p>
						<p><span class="glyphicon glyphicon-hand-right"></span> Detalhes sobre o sistema podem ser verificados clicando <a style="text-decoration: none" class="red" href="../Documentos/web/viewer.html?file=Manual_de_Uso_Jornada_REV02.pdf" target="_blank">AQUI</a>.</p>
						</h4>
					</div>
					<div class="modal-footer modalBorders">
						<button type="button" id="helpClose" class="close btn btn-info fontPlay" data-dismiss="modal" aria-hidden="true"><span class="fa fa-times fa-lg"></span>Fechar</button>
					</div>
				</div>
			</div>
		</div>
		<div style="padding-bottom:100px;">
			&nbsp;
		</div>
		<footer class="bottomFooter" id="homefooter">
			<?php 
				echo $pageFooter;
			?>
		</footer>
		
		<script src='scripts/jquery-2.1.4.min.js'></script>
		<script src='scripts/bootstrap.min.js'></script>
		<script src='scripts/animated.js'></script>
		<script src='scripts/jQueryRollPlugin/jRoll.min.js'></script>
		
				
	</body>
	
</html>