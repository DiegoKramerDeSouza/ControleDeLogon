<?php
	require_once("restrict.php");

	$sysAcc = base64_decode($acpd);
	$sysPwd = base64_decode($acpp);
	$mailAcc = base64_decode($mlu);
	$mailPwd = base64_decode($mlp);
	$filepath = "//call.br/servicos/LOGS/LogsForceLogoff/";
?>