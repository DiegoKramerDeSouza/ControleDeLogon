
<?php

echo "<!DOCTYPE HTML><html><head><meta charset='UFT-8'><title>PHP/LDAP Query Test</title></head><body>";

//dados
$ldapU = "call\a24792";
$base = "OU=Operacao,OU=02-SPM,OU=CallCenter,OU=05-SIBSQ2,OU=DF,OU=Call,DC=call,DC=br";
$ldapPw = "22043321qaz";
$ldapH = "LDAP://SVDF07W000005.call.br";
$ldapP = "389";
$ldapC = False;


//conexão
$lc = ldap_connect($ldapH, $ldapP) or die ("Não foi possível conectar...");

if($lc){
	//binding
	$ldapB = ldap_bind($lc, $ldapU, $ldapPw) or die ("Erro ao conectar!");
}

if($ldapB){
	print "Conexão estabelecida com sucesso!";
}

//ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION, 3);

echo "<h3>LDAP query results:</h3>";

//Filtro
$filt = '(&(objectClass=User)(objectCategory=Person))';

$sr = ldap_search($lc, $base, $filt);
$sort = ldap_sort($lc, $sr, 'name');
$info = ldap_get_entries($lc, $sr);



//echo "Searched from base " . $base . " with filter " . $filt . ".<br><br>";

for ($i = 0; $i < $info["count"]; $i++) {
	
	$mail = "Indefinido";
	if (isset($info[$i]["mail"][0])){
		$mail = $info[$i]["mail"][0];
	}
	
	echo ($i+1) . ". " . $info[$i]["cn"][0];
	echo " (e-mail: " . $mail;
	echo "  - Matrícula: " . $info[$i]["samaccountname"][0] . ")<br>";
	
	$dnUser = "CN=" . $info[$i]["cn"][0]. "," . $base . "<br />";

	$atributo10 = "Indefinido";
	$atributo11 = "Indefinido";
	$atributo12 = "Indefinido";
	$atributo13 = "Indefinido";
	$atributo14 = "Indefinido";
	$atributo15 = "Indefinido";
	
	if (isset($info[$i]["extensionattribute10"][0])){
		$atributo10 = $info[$i]["extensionattribute10"][0];
	}
	if (isset($info[$i]["extensionattribute11"][0])){
		$atributo11 = $info[$i]["extensionattribute11"][0];
	}
	if (isset($info[$i]["extensionattribute12"][0])){
		$atributo12 = $info[$i]["extensionattribute12"][0];
	}
	if (isset($info[$i]["extensionattribute13"][0])){
		$atributo13 = $info[$i]["extensionattribute13"][0];
	}
	if (isset($info[$i]["extensionattribute14"][0])){
		$atributo14 = $info[$i]["extensionattribute14"][0];
	}
	if (isset($info[$i]["extensionattribute15"][0])){
		$atributo15 = $info[$i]["extensionattribute15"][0];
	}
	echo "Atributo: " . $atributo10 . "<br />";
	echo "Atributo: " . $atributo11 . "<br />"; 
	echo "Atributo: " . $atributo12 . "<br />"; 
	echo "Atributo: " . $atributo13 . "<br />"; 
	echo "Atributo: " . $atributo14 . "<br />"; 
	echo "Atributo: " . $atributo15 . "<br />";
	echo "DN: " . $dnUser . "<br />";
	
	//Condição para alteração de valores
	
	//if ($i == 0) {
		//$dado["extensionAttribute14"] = "14"; //Dados para inserir
	
		//$ldapC = ldap_modify($lc, $dnUser, $dado);		//Inserção/modificação
		//$ldapC = ldap_mod_add($lc, $dnUser, $dado);		//Inserção/modificação
		//$ldapC = ldap_mod_replace($lc, $dnUser, $dado);	//Inserção/modificação
	  		  
	//	if ($ldapC) {
	//	  Print "<h3>Altera&ccedil;&atilde;o efetuada com sucesso!</h3>";
	//	}	
	//}
  
	print "<br />";
}

if ($i == 0) {
	echo "!";
}


ldap_close($lc);

echo "</body></html>";

/*

$first = ldap_first_entry($lc, $sr); //Get first entry

print $first . "<br />";

$data = ldap_get_dn($lc, $first); //Get first entry DN

print $data . "<br />";

//$read = ldap_read($lc, $data, $filt); //Read attributes

//print $read . "<br />";


$attr = ldap_get_attributes($lc, $first);

print_r ($attr);

print "<br /><br />" . $attr[20];
*/

?>