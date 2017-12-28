<?php
	$sysAcc = base64_decode($acpd);
	$sysPwd = base64_decode($acpp);
	//Dados de Conexão LDAP================================================================================
	//Usuário de conexão
	$ldapU = "call\\" . base64_decode($sysAcc);
	//Senha de conexão
	$ldapPw = base64_decode($sysPwd);
	//Caminho - OU
	$content = $dbView;
	//Host de conexão
	$ldapH = "LDAP://SVDF07W000010.call.br";
	//Porta de conexão
	$ldapP = "389";
	//=====================================================================================================
	
	//Estabelece conexão com LDAP
	$lc = ldap_connect($ldapH, $ldapP);
	ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($lc, LDAP_OPT_REFERRALS, 0);
	
	if($lc){
		//Executa Binding de conta LDAP
		$ldapB = ldap_bind($lc, $ldapU, $ldapPw);
	}
	if($ldapB){
		//Filtro para pesquisa LDAP ================================
		//Filtro
		$filter = '(&(objectClass=User)(objectCategory=Person))';
		//Search
		$sr = ldap_search($lc, $content, $filter);
		//Organiza
		$sort = ldap_sort($lc, $sr, 'name');
		//Recolhe entradas
		$info = ldap_get_entries($lc, $sr);
		//==========================================================
		for ($i = 0; $i < $info["count"]; $i++) {
			$matriculas_arr[] = array("label" => $info[$i]["samaccountname"][0] . " - " . $info[$i]["name"][0], "value" => $info[$i]["samaccountname"][0]);
		}
	}
	
?>