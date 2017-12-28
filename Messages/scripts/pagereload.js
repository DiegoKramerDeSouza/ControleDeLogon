//Javascript de controle de exibição de elementos nos documentos;
//Applied to: Home

var seconds = 60;
var miliseconds = 1000;
var countCheck = 0;

//Aguarda o documento ser carregado
$(document).ready(function(){
	//Teste
	//document.getElementById("mycheckbox").innerHTML = "<i class='fa fa-square-o fa-lg' id='uncheckedBox'></i>";
	
	//refresh automático da página a cada 'seconds' segundos
	var wait = (seconds * miliseconds);
	setInterval(function() {
		countCheck = document.querySelectorAll('input[type="checkbox"]:checked').length;
		if(countCheck == "0" && !(lockrefresh)){
			refreshPage();
			//location.reload();
		}
    }, 60000);
	if(countCheck == "0" && !(lockrefresh)){
		if ( window.location.href.indexOf('page_y') != -1 ) {
			var match = window.location.href.split('?')[1].split("&")[1].split("=");
			document.getElementsByTagName("body")[0].scrollTop = match[1];
			//refreshPage();
			//location.reload();
		}
	}
	
});

//FUNÇÕES=================================================================

//Função de refresh automático da página
function refreshPage() {
    var page_y = document.getElementsByTagName("body")[0].scrollTop;
    window.location.href = window.location.href.split('?')[0]  + '?filter=*&page_y=' + page_y;
}

//========================================================================