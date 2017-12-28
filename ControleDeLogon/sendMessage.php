<!DOCTYPE html>
<html>		
		<?php
			require_once("restrict.php");
			require_once("ldapConnection.php");
			require_once("getDate.php");
			require_once("userInfo.php");
			require_once("pageInfo.php");
			
			$database = $dbView;
			$sender = $name;
			$msgToUsers = "";
			$filepath = "";
			$msgType = $_POST["optMessage"];
			if($msgType == 1){
				$recipients = "All";
			} 
			else if($msgType == 2){
				$recipients = $_POST["msgTo"];
			}
			else if($msgType == 3){
				$recipients = $login;
			} else {
				$recipients = $_POST["msgTo"];
			}
			
			if($msgType != 1){
				if($msgType == 4){
					$filt = '(&(objectClass=User)(sAMAccountname=' . $recipients . '))';
				} else {
					$filt = '(&(objectClass=User)(objectCategory=Person)(extensionAttribute4=' . $recipients . '))';
				}
				
				if($lc){
					//Executa Binding de conta LDAP
					$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die ("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>N&atilde;o foi poss&iacute;vel conectar!<br /> Favor verificar seu usu&aacute;rio e senha e tente novamente.</strong><br /><br /><a href='.\index.php' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
				}
				if($ldapB){
					//Filtro para pesquisa LDAP ================================
					//Search
					$sr = ldap_search($lc, $database, $filt);
					//Organiza
					$sort = ldap_sort($lc, $sr, 'name');
					//Recolhe entradas
					$info = ldap_get_entries($lc, $sr);
					//==========================================================
				}
				for ($i = 0; $i < $info["count"]; $i++) {
					$msgToUsers = $msgToUsers . $info[$i]["cn"][0] . "; ";
				}
				if ($i == 0){
					if($msgType == 2){
						header("Location:mensagens.php?erro=9");
					} else if ($msgType == 3){
						header("Location:mensagens.php?erro=10");
					} else if ($msgType == 4){
						header("Location:mensagens.php?erro=11");
					}
				}
			} else {
				$msgToUsers = "Toda a opera&ccedil;&atilde;o";
			} 	
			//Monta Header-----------------------------------------------------
			echo $htmlHeader;
		?>	
		<script language="javascript" type="text/javascript" src='./scripts/tinymce/tinymce.min.js'></script>
		<script language="javascript" type="text/javascript">
		  tinyMCE.init({
			selector: '#elm1',
			plugins: 'textcolor table',
			toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | forecolor backcolor | table ',
			menubar: false,
			height:"300px",
			width:"100%",
			resize: false
		});
		tinyMCE.init({
			selector: '#subtitle',
			toolbar: false,
			menubar: false,
			resize: false,
			inline: true,
			width:"100%"
		});
		</script>
	
	<body>
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
		date_default_timezone_set("America/Sao_Paulo");
		$head =	"	<div class='container' style='padding-top:0px;'>".
						"<span><h2><b class='fontPlay'><i class='fa fa-envelope'></i> Crie sua mensagem:</b></h2></span>".
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
					"</div>";
					
		echo $head;
		$upperHead = "<div class='container' style='margin-top:30px;'>".
						"<form class='form-horizontal fontPlay' name='myMessage' accept-charset='utf-8' role='form' method='post' action='send.php'>".
							"<blockquote class='row' id='div_sender'>".
								"<div class='col-xs-12 col-sm-1 col-md-1' style='margin-top:-15px;'>".
									"<h3 class='fontPlay'>De:</h3>".
								"</div>".
								"<div class='col-xs-9 col-sm-9 col-md-9'>".
									"<input type='text' name='sender' id='sender' class='form-control' readonly value='" . $sender . "'></input>".
									"<input type='hidden' name='to_sender' class='form-control adicionar' readonly value='" . $login . "'></input>".
								"</div>".
								"<div class='col-xs-2 col-sm-2 col-md-2' align='right'>".
									"<button type='submit' class='btn btn-success' style='width:100%; min-width:100px;'>Enviar <i class='fa fa-paper-plane'></i></button>".
								"</div>".
							"</blockquote>".
							"<blockquote class='row' style='margin-top:5px;' id='div_recipient'>".
								"<div class='col-xs-12 col-sm-1 col-md-1' style='margin-top:-15px; min-width:50px;'>".
									"<h3 class='fontPlay'>Para:</h3>".
								"</div>".
								"<div class='col-xs-11 col-sm-11 col-md-11'>".
									"<input type='text' name='recipient' id='recipient' class='form-control' readonly value='" . $msgToUsers . "'></input>".
									"<input type='hidden' name='to_recipients' class='form-control' readonly value='" . $recipients . "'></input>".
									"<input type='hidden' name='to_opt' class='form-control' readonly value='" . $msgType . "'></input>".
									"<input type='hidden' name='to_database' class='form-control' readonly value='" . $database . "'></input>".
								"</div>".
							"</blockquote>".
							"<div class='col-xs-12 col-sm-12 col-md-12' align='right'>".
								"<label class='checkbox-inline' style=''>".
									"<span id='checkMail'>".
										"<b><i>Encaminhar este conte&uacute;do por email para os destinat&aacute;rios</i></b>".
									"</span>".
									"<input type='checkbox' id='tomail' data-toggle='toggle' name='to_mail' value='mail' data-on='<b>Sim&nbsp;</b>' data-off='<b>&nbsp;N&atilde;o</b>'>".
								"</label>".
							"</div>";
		echo $upperHead;
		ldap_close($lc);
	?>
							<div align="center">
								<blockquote class="row blockhover blockquote-in" id="div_subtitle">
									<div class="col-xs-12 col-sm-2 col-md-1" align="left">
										<h3 class="fontPlay">T&iacute;tulo:</h3>
									</div>
									<div class="col-xs-10 col-sm-10 col-md-11" style="padding-top:10px;">
										<i><input type='text' name='subtitle' id="subtitle" class='form-control' placeholder="T&iacute;tulo da mensagem"></input></i>
										<!--<textarea id="subtitle" name="subtitle" rows="1" cols="120" placeholder="T&iacute;tulo da mensagem"></textarea>-->
									</div>
								</blockquote>
								<div style="margin-top:5px;">
									<textarea id="elm1" name="textBody" rows="200" cols="120" ></textarea>
								</div>
							</div>
						</form>
					</div>
			</div>
		</div>
		<span style='padding-bottom:100px;'>&nbsp;</span>
		<div class="return-left" align="center">
			<strong>
				<a href='mensagens.php' class="hoverLight return">
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
		<script src='./Scripts/bootstrap-toggle-box.min.js'></script>
		<script src='./scripts/animated.js'></script>
		<script src='./scripts/jQueryRollPlugin/jRoll.min.js'></script>
		<script>
			document.getElementById("subtitle").focus();
		</script>
	</body>
	
</html>


