<!DOCTYPE html>
<html>
	<header>
		<meta charset="uft-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<title>SCL</title>
		<link rel="shortcut icon" href="Images/Hexagon.png">
		<link rel='stylesheet' href='./styles/font-awesome.css' />
		<link rel='stylesheet' href='./styles/bootstrap_free.css' />
		<link rel='stylesheet' href='./styles/style.css' />
	</header>
	<body>
		<?php
			$user = $_GET['account'];
			$user = base64_encode($user);
		?>
		<div class='fontPlay' style='color:#aa0000; font-size:30px; margin-top:10%;' align='center'>
			<p><i class='fa fa-check fa-2x' style='color:#00aa33;'></i> <b>O SCL registrou seus dados no sistema!</b></p>
			<p style='font-size:18px;'><b>Para visualizar seu tempo clique no bot&atilde;o abaixo.</b></p>
			<button type='button' class='btn btn-info btn-lg' onclick="openTimer()"><i class='fa fa-clock-o fa-lg'></i> Abra aqui!</button>
		</div>
		
		<?php
			echo "<script>
					function openTimer(){
						window.open('http://SCLBG.call.br/userview/index.php?search=" . $user . "','winname','directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=350');
						myvbalert();
					}
				  </script>";
		?>
	</body>
	
</html>