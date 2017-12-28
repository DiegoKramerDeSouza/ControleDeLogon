<?php
if(session_id() == '') {
    session_start();
}
if (isset($_SESSION['matricula']) && isset($_SESSION['senha'])){
	//Autenticação
	$login = $_SESSION['matricula'];
	$senha = $_SESSION['senha'];
	$name = $_SESSION['name'];
	$photo = $_SESSION['photo'];
	$allowedUser = $_SESSION['admPermission'];
	//Verificação de serviços
	$acpd = "Y3pBd01USXc=";
	$acpp = "YzNob2EyMHRNVEk0T1E9PQ==";
	$mlu = "Y3pBd01USXo=";
	$mlp = "U205eWJrQmtRREV5TXc9PQ==";
}
else {
	session_destroy();
	header("Location:index.php");
	exit();
}

?>