<?php
		
	//Dados de Conexão LDAP================================================================================
	//Usuário de conexão
	$ldapU = "call\\" . $login;
	//Senha de conexão
	$ldapPw = $senha;
	//Caminho - OU
	$base = "DC=call,DC=br";
	//Host de conexão
	$ldapH = "LDAP://SVDF07W000010.call.br";
	//Porta de conexão
	$ldapP = "389";
	//=====================================================================================================
		
	//Estabelece conexão com LDAP
		$lc = ldap_connect($ldapH, $ldapP) or die (header("Location:index.php?erro=1"));
		//("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>Não foi possível conectar...</strong><br /><br /><a href='.\index.php' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
		ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($lc, LDAP_OPT_REFERRALS, 0);
		
		if($lc){
			//Executa Binding de conta LDAP
			$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die (header("Location:index.php?erro=2"));
			//("<style>.logoff{display:none;}</style><div align='center'><div class='informativo container'><strong>N&atilde;o foi poss&iacute;vel conectar!<br /> Favor verificar seu usu&aacute;rio e senha e tente novamente.</strong><br /><br /><a href='.\index.php' style='color:#fff;'> <i class='fa fa-arrow-left'></i> Voltar</a></div>");
		}
	//===========================================================================================================
	
?>