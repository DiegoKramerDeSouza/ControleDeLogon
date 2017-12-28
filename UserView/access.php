<!DOCTYPE html>
<html>
	<header>
		<meta charset="uft-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<title>SCL</title>
		
		<link rel="shortcut icon" href="Images/Hexagon.png">
		<link rel='stylesheet' href='./styles/bootstrap_free.min.css' />
		<link rel='stylesheet' href='./styles/font-awesome.min.css' />
		<link rel='stylesheet' href='./styles/style.css' />
		
		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		
	</header>
	
	<body>
		<div class="result">
		
		<?php
			
			
			if (isset($_POST['matricula']) && isset($_POST['senha'])){
				
				$login = $_POST['matricula'];
				$senha = $_POST['senha'];
				$allowedUser = false;
				$name = "";
				
				//Dados de Conexão LDAP================================================================================
				//Usuário de conexão
				$ldapU = "call\\" . $login;
				//Senha de conexão
				$ldapPw = $senha;
				//Caminho - OU
				$base = "DC=call,DC=br";
				//Host de conexão
				$ldapH = "LDAP://SVDF07W000005.call.br";
				//Porta de conexão
				$ldapP = "389";
				//=====================================================================================================

				//Conexão LDAP
				$lc = ldap_connect($ldapH, $ldapP) or die (header("Location:index.php?erro=1"));
				//("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>Não foi possível conectar...</strong><br /><br /><a href='.\index.php' style='color:#fff;'>Voltar</a></div>");
				ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($lc, LDAP_OPT_REFERRALS, 0);
				
				if($lc){
					//Binding de conta com LDAP
					$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die (header("Location:index.php?erro=2"));
				}
				
				if($ldapB){
					$filt = '(&(objectClass=User)(sAMAccountname=' . $login . '))';
					//Search
					$sr = ldap_search($lc, "DC=call,DC=br", $filt);//$base
					//Recolhe entradas
					$info = ldap_get_entries($lc, $sr);
					
					if (isset($info[0]["displayname"][0])){
						$name = $info[0]["displayname"][0];
						$groups = $info[0]["memberof"];
						foreach($groups as $member){
							$member = explode(",", $member, 2);
							if ($member[0] == "CN=G.GTI.DESBLOQUEIO.CONTA.ADM"){
								$allowedUser = true;
							}
						}
					}
					//Get photo--------------------
					if (isset($info[0]["thumbnailphoto"])){
						$photo = $info[0]["thumbnailphoto"][0];
						$photo = "<img class='img-circle' src='data:image/jpeg;base64," . base64_encode($photo) . "' style='width:50px; height:50px;'/>";
						//$photo = "<img class='img-circle' src='Images/user_icon.png' style='width:50px; height:50px;'>";
					} else {
						$photo = "<img class='img-circle' src='Images/user_icon.png' style='width:50px; height:50px;'>";
					}
					//-----------------------------
					
					session_start();
					$_SESSION['matricula'] = $login;
					$_SESSION['senha'] = $senha;
					$_SESSION['name'] = $name;
					$_SESSION['photo'] = $photo;
					$_SESSION['admPermission'] = $allowedUser;
									    
					ldap_close($lc);
					header("Location:home.php");
					
				}
			} else {
				header("Location:index.php");
				exit();
			}
		?>
		</div>
		
		<script src='scripts/jquery-2.1.4.min.js'></script>
		<script src='scripts/bootstrap.min.js'></script>
		<script src='scripts/animated.js'></script>
		
	</body>
	
</html>


