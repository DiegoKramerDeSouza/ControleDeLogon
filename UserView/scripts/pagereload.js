//Javascript de controle de exibição de elementos nos documentos;
//Applied to: Index

var seconds = 60;
var miliseconds = 1000;

//Aguarda o documento ser carregado
$(document).ready(function(){
	//refresh automático da página a cada 'seconds' segundos
	var wait = (seconds * miliseconds);
	setInterval(function() {
		location.reload();
    }, wait);

});
