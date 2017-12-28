<?php
	require_once("conf.php");
	
	//Dados de Conexão LDAP================================================================================
	//Usuário de conexão
	$ldapU = "call\\" . base64_decode($sysAcc);
	//Senha de conexão
	$ldapPw = base64_decode($sysPwd);
	//Caminho - OU
	$base = "DC=call,DC=br";
	//Host de conexão
	$ldapH = "LDAP://SVDF07W000010.call.br";
	//Porta de conexão
	$ldapP = "389";
	//=====================================================================================================
		
	//Estabelece conexão com LDAP
		$lc = ldap_connect($ldapH, $ldapP) or die (header("Location:index.php?erro=1"));
		ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($lc, LDAP_OPT_REFERRALS, 0);
		
		if($lc){
			//Executa Binding de conta LDAP
			$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die (header("Location:index.php?erro=2"));
		}
	//===========================================================================================================
	
?>