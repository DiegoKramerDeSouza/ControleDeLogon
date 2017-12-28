<?php
function json_read($url){
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$result_curl = curl_exec($curl);
	
	$result_curl_string = json_decode($result_curl, true);
	$result_curl_var = json_decode($result_curl);
	
	return $result_curl_var;
}

function object_treatment($object, $id_url){
	if($object != ""){
		$num_object = count($object);
		$i = 0;
		while($i < $num_object){
			$id_o = $object[$i]->id;
			$name_o = $object[$i]->name;
			$status_name_o = $object[$i]->status_name;
			
			$id_novo = str_split($id_o);
			$id_novo[0] = 'c';
			$matricula = implode("", $id_novo);
			
			if($matricula == $id_url){
				if($status_name_o == "available"){
					return "available";
				} else if($status_name_o == "break"){
					return "break";
				} else if($status_name_o == "busy"){
					return "busy";
				} else if($status_name_o == "ring"){
					return "ring";
				}
			}
			$i++;
		}
	} else {
		echo "";
	}
	return "notfound";
}

function using_curl($url, $id_url){
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$result_curl = curl_exec($curl);
	
	$result_curl_string = json_decode($result_curl, true);
	$result_curl_var = json_decode($result_curl);
	
	//exemplo de retorno: "id":"453393","name":"JAQUELINE BORGES DA SILVA","status_name":"busy"
	$num_result_curl_var = count($result_curl_var);
	$i = 0;
	while($i < $num_result_curl_var){
		$id = $result_curl_var[$i]->id;
		$name = $result_curl_var[$i]->name;
		$status_name = $result_curl_var[$i]->status_name;
		
		$id_novo = str_split($id);
		$id_novo[0] = 'c';
		$matricula = implode("", $id_novo);
		
		if($matricula == $id_url){
			//echo "--------------------------------";
			//echo "<br>ID: " . $id;
			//echo "<br>MATRICULA: " . $matricula;
			//echo "<br>NAME: " . $name;
			//echo "<br>STATUS NAME: " . $status_name;
			//echo "<br>";
			if($status_name == "available"){
				echo "true";
			} else if($status_name == "break"){
				echo "true";
			} else if($status_name == "busy"){
				echo "false";
			} else if($status_name == "ring"){
				echo "false";
			}
		}
		$i++;
	}
	//$nome = $result_curl_var[0]->nome;
	//$idade = $result_curl_var->idade;
	//$escolaridade = $result_curl_var->escolaridade;
}

?>