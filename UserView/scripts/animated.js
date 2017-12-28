//Javascript de controle de exibição de elementos nos documentos;
//Applied to: Index, Home, User, unlock

var $countChecked = 0;
var $idByClass;
var $objClass;
var $showObjData;
var $checked;
var $checkedCount;
var $allBox;
var lockrefresh = false;
var tempId = "";
var tempDb = "";
var idFilter = "";
var id = "";
var clickCount = false;
var formatURL;
var success;
var erro;
var radNum = 0;
var animation = "";
	
//Aguarda o documento ser carregado
$(document).ready(function(){
	window.resizeTo(700,240);
	//Variáveis para exibição de mensagens em MsgBox
	var icone = "<i class=\'fa fa-check fa-3x checkOk\'></i> ";
	var titulo = "<b>Conclu&iacute;do com sucesso!</b>";
	var mensagem = "";
	var cor = "rgb(250, 250, 250)";
	
	//Apresenta campo de acesso
	$(".access").fadeIn(500);
	//Carrega tela de load aleatória---------------------------
	radNum = getRandom(1, 4);
	if (radNum == 1){
		animation= "pulse";
	}
	else if (radNum == 2){
		animation= "gyroscope";
	}
	else if (radNum == 3){
		animation= "squares";
	}
	else if (radNum == 4){
		animation= "3Dsquares";
	}
	$("#loadGif").jRoll({
		animation: animation
	});
	//Aplica para os botões a exibição da tela de load----------
	$(".fa-chevron-left").click(function(){
		$("#loading").fadeIn();
	});
	$("#imgLogon").click(function(){
		$("#loading").fadeIn();
	});
	$("#hexIcon").click(function(){
		$("#loading").fadeIn();
	});
	$(".btn").click(function(){
		var buttonId = this.id;
		
		if(document.getElementById(buttonId).disabled == false){
			if(this.id == "indexLoad"){
				if (document.forms["acessar"]["matricula"].value != "" && document.forms["acessar"]["senha"].value != ""){
					$("#loading").fadeIn();
				}
			}
			else if(this.id == "clean"){
				if (document.forms["cleanUser"]["clean"].value != ""){
					$("#loading").fadeIn();
				}
			}
			else if (this.id == "helpLoad" || this.id == "helpClose"){
				//Do Nothing...
			}
			else if (this.id == "EditModal" || (this.id == "modalAplRmv" || (this.id == "modalCncRmv" || (this.id == "modalAplAdd" || (this.id == "modalAplRmv" || (this.id == "modalAdd" || (this.id == "modalCncAdd" || this.id == "modalRmv"))))))){
				//Do Nothing...
			}
			else if (this.id == "selectRmv" || this.id == "selectAdd"){
				//Do Nothing...
			}
			else if (this.id == "coletiveAdd" || this.id == "cancel-coletiveAdd"){
				//Do Nothing...
			}
			else if (this.id == "cleanNot" || (this.id == "uncheckAll" || (this.id == "dropMenu1" || this.id == "tutorial"))){
				//Do Nothing...
			}
			else {
				$("#loading").fadeIn();
			}
		}
	});
	//----------------------------------------------------------
		
	//loadUserNow();
	verityCheck();
	
	//Mouseover apresenta Status
	var $statusId;
	var $achron;
	var $achronId;
	var $pointx;
	var $pointy;
	$(".accountStatus").mouseenter(function(){
		$statusId = this.id;
		$pointx = -70;
		$pointy = -10;
		$achron = ("St" + $statusId);
		$("#" + $achron).fadeIn(250);
		$achronId ="#" + $achron;
		$($achronId).css({
			left: $pointx,
			top: $pointy
		});
	});
	$(".accountStatus").mouseleave(function(){
		$statusId = this.id;
		$achron = "#St" + $statusId;
		$($achron).fadeOut(0);
		
	});
	
	//Tratamento de eventos registrados na URL do local 'index.php'
	erro = identifyReturnedCode("erro");
	if(erro == 1){
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Não foi possível estabelecer conexão com o servidor!";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 2){
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Usuário ou senha inválidos!";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 3){
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Área restrita, faça login para obter o acesso.";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 4){
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Acesso Negado!";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 5){
		formatURL = identifyResultCode("&erro=");
		mensagem = icone + "Conta desbloqueada com sucesso!";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 6){
		formatURL = identifyResultCode("&erro=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Falha ao inserir dados!";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 7){
		formatURL = identifyResultCode("&erro=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Usuário não encontrado!";
		callMessage(titulo, mensagem, cor);
	}
	//Tratamento de eventos registrados na URL do local 'home.php'
	success = identifyReturnedCode("result");	
	if(success == 0){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "Foi solicitado mais tempo ao supervisor!";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 1){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "Foi <u>adicionado mais tempo</u> para todos os colaboradores selecionados!";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 2){
		formatURL = identifyResultCode("&result=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha ao executar!</b>";
		mensagem = icone + "Não foi possível efetuar a operação!<br />Por favor acione o suporte técnico.";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 3){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "Solicitação de <u>hora extra cancelada</u> pelo supervisor!";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 4){
		formatURL = identifyResultCode("&result=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha ao executar!</b>";
		mensagem = icone + "Não foi possível negar a solicitação. Falha ao alterar atributos";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 5){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "Foi <u>efetuado o logoff</u> do colaborador.";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 6){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "Foi <u>cancelado o logoff</u> do colaborador.";
		callMessage(titulo, mensagem, cor);
	}
	//Tratamento do resultado do gerenciamento de grupos----------------------
	if(success == 7){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "Foi adicionado o colaborador ao(s) grupo(s) selecionado(s).";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 8){
		formatURL = identifyResultCode("&result=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha ao executar!</b>";
		mensagem = icone + "Não foi possível adicionar o colaborador ao(s) grupo(s).";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 9){
		formatURL = identifyResultCode("&result=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha ao executar!</b>";
		mensagem = icone + "Favor informar os grupos para inser&ccedil;&atilde;o ou remo&ccedil;&atilde;o.";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 10){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "O colaborador foi removido do(s) grupo(s) selecionado(s).";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 11){
		formatURL = identifyResultCode("&result=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha ao executar!</b>";
		mensagem = icone + "Não foi possível remover o colaborador do(s) grupo(s).";
		callMessage(titulo, mensagem, cor);
	}
	//Tratamento do resultado do reset de dados de logon e logoff----------------------
	if(success == 12){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "Foram resetados os registros do colaborador.";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 13){
		formatURL = identifyResultCode("&result=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha ao executar!</b>";
		mensagem = icone + "Não foi possível resetar os dados do colaborador.";
		callMessage(titulo, mensagem, cor);
	}
	//-------------------------------------------------------------------------
	
	//Anima objetos da class Blink
	setInterval(function(){
		  $('.blink').each(function(){
			$(this).css('visibility' , $(this).css('visibility') === 'hidden' ? '' : 'hidden')
		  });
		}, 500);

	//Tratamento de checkbox para exibir o botão de adição de tempo
	$(".chkBox").change(function(){
		$idByClass = this.id;
		idFilter = $idByClass;
		$idByClass = "#" + $idByClass;
		$checked = document.querySelector($idByClass).checked;
		$checkedCount = document.querySelectorAll('input[type="checkbox"]:checked').length;
		
		if($checked == true){
			$(".maisTempoChkd").show(500);
			getSelected(idFilter);
		} else {
			if($checkedCount == 0){
				$(".maisTempoChkd").hide(500);
				$("#uncheckedBox").hide(0);
				document.getElementById("allChecked").innerHTML = "<i class='fa fa-square-o fa-lg'></i>";
			}
			findStr(document.forms["variosUsers"]["usersCodes"].value, idFilter);
		}
	});
	
	//Controle de Hover sobre a classe informativoUser --- EXIBE
	$(".informativoUser").click(function(){
		$objClass = this.id;
		$hideInfo = "#" + $objClass + "_hideInfo";
		$showObjData = "#" + $objClass + "Details";
		$($showObjData).show(66);
		$($hideInfo).show(66);
		document.getElementById($objClass + "_maisTempo").style.top = "30px";
		clickCount = true;
	});
	//Controle de Hover sobre a classe informativoUser --- ESCONDE
	$(".btnInfo").click(function(){
		$objClass = this.id;
		$objClass = $objClass.replace("_hideInfo", "");
		$hideInfo = "#" + $objClass + "_hideInfo";
		$showObjData = "#" + $objClass + "Details";
		$($showObjData).hide(66);
		$($hideInfo).hide(66);
		document.getElementById($objClass + "_maisTempo").style.top = "20px";
		$objClass = "#" + $objClass;
		if (clickCount) {
			$($objClass).css({"box-shadow": "none"})
		}
		clickCount = false;
	});
	$(".btnInfo").mouseenter(function(){
		$objClass = this.id;
		$objClass = $objClass.replace("_hideInfo", "");
		$objClass = "#" + $objClass;
		if (clickCount) {
			$($objClass).css({"box-shadow": "0px -8px 10px 0px rgba(80, 80, 80, 0.5)"})
		}
	});
	$(".btnInfo").mouseleave(function(){
		$objClass = this.id;
		$objClass = $objClass.replace("_hideInfo", "");
		$objClass = "#" + $objClass;
		if (clickCount) {
			$($objClass).css({"box-shadow": "none"})
		}
	});
	
	//Scrool to top--------
	$(window).scroll(function() {
        if($(this).scrollTop() > 100){
            $('#toTop').fadeIn(500);
        }
        else{
            $('#toTop').fadeOut(500);
        }
    });
    $('#toTop').click(function() {
        $('html, body').stop().animate({
           scrollTop: 0
        }, 500, function() {
            $('#toTop').fadeOut(500);
        });
    });
	
	//Exibe e limpa return area left
	$(".active-return").mouseenter(function(){
		$(".return-left").animate({left: '0px'});
	});
	$(".return-left").mouseleave(function(){
		$(".return-left").animate({left: '-90px'});
	});
	
	//Mensagem de alerta para conta desabilitada
	$("#userDisabled").click(function(){
		//alert("Por favor acione a equipe de suporte para verificar o motivo da conta encontrar-se desabilitada.");
		erro = 700;
		formatURL = identifyResultCode("&result=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Por favor acione a equipe de suporte para verificar o motivo da conta encontrar-se desabilitada.</b>";
		mensagem = icone + "Não foi possível adicionar o colaborador ao(s) grupo(s).";
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	})
	//-----------------------------------------------------------------
});
//FUNÇÕES=================================================================

//Funções para selecionar/deselecionar todos os checkbox
function checkAll(){
	var btnId;
	$allBox = document.querySelectorAll('input[type="checkbox"]').length;
	for(var $i = 0; $i < $allBox; $i++){
		btnId = document.getElementsByClassName('adicionar')[$i].disabled;
		if(btnId == false){
			document.getElementsByClassName('chkBox')[$i].checked = true;
			getSelected(document.getElementsByClassName('chkBox')[$i].id);
		}
	}
	document.getElementById("mycheckbox").innerHTML = "<i class='fa fa-check-square-o fa-lg'></i>";
	$(".maisTempoChkd").show(500);
}
function uncheckAll(){
	$allBox = document.querySelectorAll('input[type="checkbox"]').length;
	document.forms["variosUsers"]["usersCodes"].value = "";
	for(var $i = 0; $i < $allBox; $i++){
		document.getElementsByClassName('chkBox')[$i].checked = false;
	}
	document.getElementById("mycheckbox").innerHTML = "<i class='fa fa-square-o fa-lg'></i>";
	$(".maisTempoChkd").hide(500);
}
function verityCheck(){
	var btnIdentity;
	$boxes = document.querySelectorAll('input[type="checkbox"]').length;
	for(var $i = 0; $i < $boxes; $i++){
		btnIdentity = document.getElementsByClassName('adicionar')[$i].disabled;
		if(btnIdentity == true){
			document.getElementsByClassName('chkBox')[$i].disabled = true;
		}
	}
}

//Funções para alterar a exibição de elementos na estrutura HTML
function fixFooter(){
	lockrefresh = true;
	document.getElementById("homefooter").style.position = "fixed";
	document.getElementById("homefooter").style.bottom = "0px";
	document.getElementById("homefooter").style.width = "100%";
	setTimeout(function(){
		document.getElementById("searchUser").focus();
	}, 200);
}
function unfixFooter(){
	lockrefresh = false;
	$("#homefooter").hide(0);
	document.getElementById("homefooter").style.position = "relative";
	document.getElementById("homefooter").style.bottom = "0px";
	document.getElementById("homefooter").style.width = "100%";
	$("#homefooter").show(1000);
	setTimeout(function(){
		document.getElementById("search").focus();
	}, 200);	
}
//Função para o tratamento de resultados
function identifyReturnedCode(val) {
	var url = window.location.search.substring(1);
	var divide = url.split("&");
	
	for (var i=0;i<divide.length;i++) {
		var match = divide[i].split("=");
		if (match[0] == val) {
			return match[1];
		}
	}
}
//Função para a remoção de resultados
function identifyResultCode(val) {
	var url = window.location.search.substring(1);
	var divide = url.split(val);
	return divide[0];
}
//Função de preenchimento de campos durante a seleção de checkbox
function getSelected(userid){
	document.forms["variosUsers"]["usersCodes"].value = document.forms["variosUsers"]["usersCodes"].value + userid;
	document.forms["variosUsers"]["usersDatabase"].value = document.forms["returnResults"]["database"].value;
}

//Função de tratamento de string
function findStr(str, val){
	var mystring = str.replace(val,"");
	document.forms["variosUsers"]["usersCodes"].value = mystring;
}

//Função de tratamento de URL
function setUrl(urlVal) {
	window.location = urlVal;
}

//Função de get em coordenadas do mouse
function showCoords(event){
	var x = event.clientX;
	var y = event.clientY;
}
//Set URL para negar a solicitação de hora extra
function negaSolicitacao(account, options){
	setUrl("negate.php?User=" + account + "&opt=" + options);
}
//Exibe o campo de usuário e foto
function loadUserNow(){
	$(".userNow").fadeIn(1300);
}
//Confirma justificativa para logoff
function justificaLogoff(val, base, conta){
	var initVal = "O colavorador solicita o logoff de sua máquina pelo seguinte motivo: \n\n\"" + val + "\"\n\nVocê confirma o logoff?";
	var strVal = initVal.toUpperCase();
	var inputbox = confirm(strVal);
	if (inputbox) {
		//alert(val);
		setUrl("userlogoff.php?account=" + conta + "&database=" + base + "&opt=" + "ok");
	} else {
		setUrl("userlogoff.php?account=" + conta + "&database=" + base + "&opt=" + "cancel");
	}
	
}
//Acordeon para informações de usuário na Home
function hideUserInfo(selectedId){
	$hideInfo = "#" + selectedId + "_hideInfo";
	$showObjData = "#" + selectedId + "Details";
	$($showObjData).hide(116);
	$($hideInfo).hide(116);
	document.getElementById(selectedId + "_maisTempo").style.top = "20px";
}
//Teste para a verificação dos grupos
function verifyGroups(){
	//alert(document.forms["selectGroupsToUser"]["select-groups"].value);
	alert(document.forms["rmvGroupsToUser"]["tormvDNGroups"].value);
	
}
//Limpa valores de gupos nas textareas
function clearValues(){
	document.forms["selectGroupsToUser"]["toinsertGroups"].value = "";
	document.forms["selectGroupsToUser"]["toinsertDNGroups"].value = "";
	document.forms["rmvGroupsToUser"]["tormvGroups"].value = "";
	document.forms["rmvGroupsToUser"]["tormvDNGroups"].value = "";
}
//Trata nomes de grupos e insere nas suas devidas textareas
//Adicionar grupos
function inputGroups(){
	var groupToInsert = document.forms["selectGroupsToUser"]["select-groups"].value;
	var filterDN = groupToInsert.split(",");
	var filterCN = filterDN[0].split("CN=");
	if (document.forms["selectGroupsToUser"]["toinsertDNGroups"].value.indexOf(groupToInsert) == -1 ){
		document.forms["selectGroupsToUser"]["toinsertDNGroups"].value = document.forms["selectGroupsToUser"]["toinsertDNGroups"].value + groupToInsert + "||";
		groupToInsert = document.forms["selectGroupsToUser"]["toinsertGroups"].value + filterCN[1] + "; ";
		document.forms["selectGroupsToUser"]["toinsertGroups"].value = groupToInsert;
	}
}
//Remover grupos
function rmvGroups(){
	var groupToRmv = document.forms["rmvGroupsToUser"]["select-rmvgroups"].value;
	var filterDN = groupToRmv.split(",");
	var filterCN = filterDN[0].split("CN=");
	if (document.forms["rmvGroupsToUser"]["tormvDNGroups"].value.indexOf(groupToRmv) == -1 ){
		document.forms["rmvGroupsToUser"]["tormvDNGroups"].value = document.forms["rmvGroupsToUser"]["tormvDNGroups"].value + groupToRmv + "||";
		groupToRmv = document.forms["rmvGroupsToUser"]["tormvGroups"].value + filterCN[1] + "; ";
		document.forms["rmvGroupsToUser"]["tormvGroups"].value = groupToRmv;
	}
}
//Apaga registros de login
function eraser(useraccount){
	setUrl("eraser.php?User=" + useraccount);
}
//MessageBox personalizado
function callMessage(title, message, color){
	document.getElementById("msgHeader").innerHTML = title;
	document.getElementById("msgBody").innerHTML = message;
	document.getElementById("msgHeader").style = "color:rgba(100,100,100,0.8);";
	document.getElementById("msgBody").style = "color:rgba(100,100,100,0.8);";
	document.getElementById("dialog").style = "background-color:" + color + ";";
	$("#msgDiv").fadeIn(200);
	$("#dialog").fadeIn(300);
	setTimeout(function(){
		clearMsgBox();
	}, 1500);
}
function clearMsgBox(){
	document.getElementById("msgHeader").innerHTML = "";
	document.getElementById("msgBody").innerHTML = "";
	$("#dialog").fadeOut(200);
	$("#msgDiv").fadeOut(300);
	
	if (success == 0){
		setUrl("index.php?" + formatURL);
	}
	
}
//Gerar número aleatório entre 1 e 4
function getRandom(min, max) {
    return Math.round(Math.random() * (max - min) + min);
}
//Verifica funcionalidade de botões
function verifyBtn() {
	var $btnDisabled;
	var $btnId;
	var $allBtn = 0;
	$allBtn = document.getElementsByClassName('btn').length;
	for(var $i = 0; $i < $allBtn; $i++){
		$btnDisabled = document.getElementsByClassName('btn')[$i].disabled;
		$btnId = document.getElementsByClassName('btn')[$i].id;
		alert($btnId + " - " + $btnDisabled + " <<");
	}
}

//========================================================================
