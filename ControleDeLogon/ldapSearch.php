<?php

	require_once("restrict.php");
	require_once("ldapConnection.php");
	
	function searchAt($filter){
	
	/*
	//Teste--------------------------------------------------------
	//$filter = 1;
	
	if($filter == 1){
		$filt = '(&(objectClass=User)(objectCategory=Person))';
	}
	elseif($filfer == 2){
		$searchAccount = $_SESSION['accSearch'];
		$filt = '(&(objectClass=User)(sAMAccountname=' . $searchAccount . '))';
	}
	//--------------------------------------------------------------
	*/
	
	$filt = '(&(objectClass=User)(sAMAccountname=' . $filter . '))';
	//Filtro para pesquisa LDAP ================================
		//Search
		$sr = ldap_search($lc, $base, $filt);
		//Organiza
		$sort = ldap_sort($lc, $sr, 'name');
		//Recolhe entradas
		$info = ldap_get_entries($lc, $sr);
	//==========================================================
	
	ldap_close($lc);
		
		
		
	}
	
?>