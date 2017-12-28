<?php
	if(isset($_GET['index'])){
		$index = $_GET['index'];
		if(file_exists("//call.br/servicos/LOGS/LogsMessages/SCL/" . $index . ".html")){
			$body = file_get_contents("//call.br/servicos/LOGS/LogsMessages/SCL/" . $index . ".html");
			echo $body;
		} else {
			echo "<div><h2>Índice não localizado</h2></div>";
		}
	} else {
		echo "<div><h2>Índice não localizado</h2></div>";
	}
?>