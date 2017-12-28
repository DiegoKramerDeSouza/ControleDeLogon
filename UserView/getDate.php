<?php
//Set time zone
	date_default_timezone_set("America/Sao_Paulo");
	
//Data/Hora Atual
	$data = getdate();
	$mesPadrao = 31;
	
	$addH = 0;
	$addM = 0;
	
	$horas = (Int)$data["hours"];
	$minutos = (Int)$data["minutes"];
	$dias = (Int)$data["mday"];
	$meses = (Int)$data["mon"];
	$anos = (Int)$data["year"];
	$dataAtual = $horas . ":" . $minutos . " - " . $dias . "/" . $meses . "/" . $anos;
	
	if((($meses == 4 || $meses == 6) || $meses == 9) || $meses == 11){
		$mesPadrao = 30;
	}
	elseif($meses == 2){
		$mesPadrao = 28;
	}

?>