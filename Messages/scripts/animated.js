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
var clicked = false;
var mouseSelect = false;
	
//Aguarda o documento ser carregado
$(document).ready(function(){
	
	if($(window).height() > 700){
		$('#homefooter').fadeIn(500);
	}
	
	// Opções de mensagem==============================================
	$(".showmsgbody").click(function(){
		var msgbodyID = this.id;
		$(".bodymsg").hide(0);
		$(".readbodymsg").hide(0);
		$("#message" + msgbodyID).fadeIn(500);
		$(".showmsgbody").css({
			'border-bottom': '0px solid transparent'
		});
		$('#' + this.id).css({
			'border-bottom': '1px solid #0cf'
		});
	});
	
	$(".prevOrNextMsg").click(function(){
		$('.bodymsg').hide(0);
		$('#message_' + this.id).fadeIn(500);
		$(".showmsgbody").css({
			'border-bottom': '0px solid transparent'
		});
		$('#_' + this.id).css({
			'border-bottom': '1px solid #0cf'
		});
		
	});
	$(".turnPage").click(function(){
		$('.msgPage').hide(0);
		$('#page' + this.id).fadeIn(500);
	});
	
	$(".showreadmsgbody").click(function(){
		var msgbodyID = this.id;
		$(".bodymsg").hide(0);
		//$("#message" + msgbodyID).fadeIn(500);
		$(".showreadmsgbody").css({
			'border-bottom': '0px solid transparent'
		});
		$('#' + this.id).css({
			'border-bottom': '1px solid #0cf'
		});
	});
	//===========================================================================
	
	
	
	//Display error message
	$(".explainOff").fadeIn(1000);
	
	//Variáveis para exibição de mensagens em MsgBox
	var icone = "<i class=\'fa fa-check fa-3x checkOk\'></i> ";
	var titulo = "<b>Conclu&iacute;do com sucesso!</b>";
	var mensagem = "";
	var cor = "rgba(0, 100, 160, 0.8)";
	
	//Apresenta campo de acesso
	$(".access").fadeIn(500);
	//Carrega tela de load aleatória---------------------------
	radNum = getRandom(1, 2);
	if (radNum == 1){
		animation= "pulse";
	}
	else if (radNum == 2){
		animation= "gyroscope";
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
	$(".filterType").click(function(){
		$("#loading").fadeIn();
	});
	$(".btnj").click(function(){
		var buttonId = this.id;
		//alert(buttonId);
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
			else if(this.id == "gotoMsgEditor"){
				if (document.forms["sendMessage"]["msgTo"].value != ""){
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
		$pointx = 25;
		$pointy = -80;
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
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 2){
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Usuário ou senha inválidos!";
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 3){
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Área restrita, faça login para obter o acesso.";
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 4){
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Acesso Negado!";
		cor = "rgba(175, 50, 50, 0.8)";
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
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 7){
		formatURL = identifyResultCode("&erro=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Usuário não encontrado!";
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 8){
		formatURL = identifyResultCode("&erro=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Usuário não encontrado!";
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 9){
		formatURL = identifyResultCode("&erro=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Equipe não encontrada ou vazia!";
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 10){
		formatURL = identifyResultCode("&erro=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Não há usuários na sua equipe!";
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 11){
		formatURL = identifyResultCode("&erro=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Usuário não localizado!";
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	else if(erro == 12){
		formatURL = identifyResultCode("&erro=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha!</b>";
		mensagem = icone + "Falha ao inserir dados!";
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	//Tratamento de eventos registrados na URL do local 'home.php'
	success = identifyReturnedCode("result");	
	if(success == 0){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "Foi <u>adicionado mais tempo</u> para o colaborador!";
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
		cor = "rgba(175, 50, 50, 0.8)";
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
		cor = "rgba(175, 50, 50, 0.8)";
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
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 9){
		formatURL = identifyResultCode("&result=");
		icone = "<i class=\'fa fa-times fa-3x checkTimes\'></i> ";
		titulo = "<b>Falha ao executar!</b>";
		mensagem = icone + "Favor informar os grupos para inser&ccedil;&atilde;o ou remo&ccedil;&atilde;o.";
		cor = "rgba(175, 50, 50, 0.8)";
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
		cor = "rgba(175, 50, 50, 0.8)";
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
		cor = "rgba(175, 50, 50, 0.8)";
		callMessage(titulo, mensagem, cor);
	}
	//-------------------------------------------------------------------------
	if(success == 14){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "Operador adicionado com sucesso!";
		callMessage(titulo, mensagem, cor);
	}
	if(success == 15){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "Operador removido com sucesso!";
		callMessage(titulo, mensagem, cor);
	}
	//--------------------------------------------------------------------------
	if(success == 16){
		formatURL = identifyResultCode("&result=");
		mensagem = icone + "Mensagem enviada com sucesso!";
		callMessage(titulo, mensagem, cor);
	}
	
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
        if($(this).scrollTop() > 80){
			$('#homefooter').fadeIn(500);
        }
        else{
            $('#homefooter').fadeOut(500);
        }
    });
    $('#toTop').click(function() {
        $('html, body').stop().animate({
           scrollTop: 0
        }, 500, function() {
            $('#toTop').fadeOut(500);
        });
    });

	//Exibe e limpa Menu
	$(".active-menu").mouseenter(function(){
		$(".menu-left").css({
			'left': '0px',
			'background-color': 'rgba(24, 52, 70, 0.8)',
			'box-shadow': '0px 3px 10px 5px rgba(50,50,50,0.5)'
			});
		$(".menu-left-bg").fadeIn(500);
		document.getElementById('indicatorLeft').innerHTML = '<span class="fa fa-chevron-left fa-1x"></span>';
	});
	$(".menu-left-bg").click(function(){
		$(".menu-left").css({
			'left': '-290px',
			'background-color': 'rgba(24, 52, 70, 1.0)',
			'box-shadow': 'none'
			});
		$(".menu-left-bg").fadeOut(500);
		document.getElementById('indicatorLeft').innerHTML = '<span class="fa fa-chevron-right fa-1x"></span>';
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
	
	$(".form-control").focus(function(){
		var blockId = this.id;
		var divBlock = "#div_" + blockId;
		$(divBlock).css({"border-left": "5px solid #0cf"});
	});
	$(".form-control").blur(function(){
		var blockId = this.id;
		var divBlock = "#div_" + blockId;
		$(divBlock).css({"border-left": "5px solid #eee"});
	});
		
	
	
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
	if(window.location.href.split('?')[1].split("&")[0].split("=")[1] == "*"){
		$("#homefooter").hide(0);
		document.getElementById("homefooter").style.position = "relative";
		document.getElementById("homefooter").style.bottom = "0px";
		document.getElementById("homefooter").style.width = "100%";
		$("#homefooter").show(1000);
		setTimeout(function(){
			document.getElementById("search").focus();
		}, 200);
	}
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
	document.getElementById("dialog").style = "background-color:" + color + ";";
	$("#msgDiv").fadeIn(200);
	$("#dialog").fadeIn(300);
}
function clearMsgBox(){
	document.getElementById("msgHeader").innerHTML = "";
	document.getElementById("msgBody").innerHTML = "";
	$("#dialog").fadeOut(200);
	$("#msgDiv").fadeOut(300);
	if (success == 1 || (success == 5 || (success == 6 || success == 16))){
		setUrl("home.php?filter=*");
	}
	else if(success == 7 || (success == 8 || (success == 9 || (success == 10 || success == 11)))){
		setUrl("gerencia.php?" + formatURL);
	}
	else if(success == 12 || success == 13){
		setUrl("user.php?" + formatURL);
	}
	if (erro == 1 || (erro == 2 || (erro == 3 || erro == 4))){
		setUrl("index.php");
	}
	else if (erro == 9 || (erro == 10 || (erro == 11 || erro == 12))){
		setUrl("home.php?filter=*");
	}
	else if (erro == 5 || (erro == 6 || erro == 7)){
		setUrl("unlock.php?" + formatURL);
	}
	else if (success == 0 || (success == 3 || success == 4)){
		setUrl("user.php?" + formatURL);
	}
	else if (success == 14 || (success == 15 || erro == 8)){
		setUrl("equipe.php");
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

//Tratamento de checkbox
function clickedCKBtn(indicador){
	$('#checkboxBtn1').bootstrapToggle('off');
	$('#checkboxBtn2').bootstrapToggle('off');
	$('#checkboxBtn3').bootstrapToggle('off');
	document.forms["sendMessage"]["optMessage"].value = indicador;
	$('#divViewOpt').fadeIn(500);
	if(indicador == 1){
		document.getElementById('viewOpt').innerHTML = "<div class='panel panel-primary' style='margin-top:30px;'>" +
															"<div class='panel-heading'>" +
																"<div class='row'>" +
																	"<div class='col-xs-2 col-sm-2 col-md-1'>" +
																		"<i class='fa fa-globe fa-2x'></i>" +
																	"</div>" +
																	"<div class='col-xs-10 col-sm-10 col-md-11'>" +	
																		"<h3 class='panel-title fontPlay'>Sua mensagem ser&aacute; encaminhada para toda a sua opera&ccedil;&atilde;o.</h3>" +
																	"</div>" +
																"</div>" +
															"</div>" +
															"<div class='panel-body'>" +
																"<h4 class='fontPlay'>Para confirmar e come&ccedil;ar a criar sua mensagem, clique no bot&atilde;o abaixo.</h4>" +
															"</div>" +
														"</div>";
	}
	else if(indicador == 2){
		document.getElementById('viewOpt').innerHTML = "<div class='panel panel-primary' style='margin-top:30px;'>" +
															"<div class='panel-heading'>" +
																"<div class='row'>" +
																	"<div class='col-xs-2 col-sm-2 col-md-1'>" +
																		"<i class='fa fa-users fa-2x'></i>" +
																	"</div>" +
																	"<div class='col-xs-10 col-sm-10 col-md-11'>" +	
																		"<h3 class='panel-title fontPlay'>Sua mensagem ser&aacute; encaminhada apenas para a equipe do supervisor informado.</h3>" +
																	"</div>" +
																"</div>" +
															"</div>" +
															"<div class='panel-body'>" +
																"<h4 class='fontPlay'>Informe a matr&iacute;cula do supervisor no campo abaixo e em seguida clique no bot&atilde;o &quot;Criar mensagem&quot;.</h4>" +
																"<input type='text' required class='form-control' name='msgTo' id='msgToEquipe' placeholder='Informe a matr&iacute;cule do supervisor.'></input>" +
															"</div>" +
														"</div>";
		document.getElementById("msgToEquipe").focus();
	}
	else if(indicador == 3){
		document.getElementById('viewOpt').innerHTML = "<div class='panel panel-primary' style='margin-top:30px;'>" +
															"<div class='panel-heading'>" +
																"<div class='row'>" +
																	"<div class='col-xs-2 col-sm-2 col-md-1'>" +
																		"<i class='fa fa-users fa-2x'></i>" +
																	"</div>" +
																	"<div class='col-xs-10 col-sm-10 col-md-11'>" +	
																		"<h3 class='panel-title fontPlay'>Sua mensagem ser&aacute; encaminhada apenas para toda a sua equipe.</h3>" +
																	"</div>" +
																"</div>" +
															"</div>" +
															"<div class='panel-body'>" +
																"<h4 class='fontPlay'>Para confirmar e come&ccedil;ar a criar sua mensagem, clique no bot&atilde;o abaixo.</h4>" +
															"</div>" +
														"</div>";
	}
	else if(indicador == 4){
		document.getElementById('viewOpt').innerHTML = "<div class='panel panel-primary' style='margin-top:30px;'>" +
															"<div class='panel-heading'>" +
																"<div class='row'>" +
																	"<div class='col-xs-2 col-sm-2 col-md-1'>" +
																		"<i class='fa fa-user fa-2x'></i>" +
																	"</div>" +
																	"<div class='col-xs-10 col-sm-10 col-md-11'>" +	
																		"<h3 class='panel-title fontPlay'>Sua mensagem ser&aacute; encaminhada apenas para o colaborador informado.</h3>" +
																	"</div>" +
																"</div>" +
															"</div>" +
															"<div class='panel-body'>" +
																"<h4 class='fontPlay'>Informe a matr&iacute;cula do colaborador no campo abaixo e em seguida clique no bot&atilde;o &quot;Criar mensagem&quot;.</h4>" +
																"<input type='text' required class='form-control' name='msgTo' id='msgToUser' placeholder='Informe a matr&iacute;cule do colaborador.'></input>" +
															"</div>" +
														"</div>";
		document.getElementById("msgToUser").focus();
	}	
}
// Opções de mensagem==============================================
function readMsg(user, num){
	setUrl("read.php?account=" + user + "&msg=" + num);
}
function callAlertBlock(num){
	$('#alert_' + num).fadeIn(500);
	$('#alert_' + num).css({
		'margin': 'auto'
	})
	$('.confirmationBG').fadeIn(500);
	$('#text_' + num).fadeIn(1000);
	$('#btn_' + num).fadeIn(300);
}
function alertBoxFade(num){
	$('#alert_' + num).hide(0);
	$('.confirmationBG').hide(0);
	$('#text_' + num).hide(0);
	$('#btn_' + num).hide(0);
}
//Exclusão-----------------------------
function excldMsg(user, num, file){
	var ind = $("#exclusiontext_"+num).attr("data-index");
	console.log(ind);
	setUrl("read.php?account=" + user + "&msg=" + num + "&excld=yes&file=" + ind);
}
function callExclusionBlock(num){
	$('#exclusion_' + num).fadeIn(500);
	$('#exclusion_' + num).css({
		'margin': 'auto'
	})
	$('.confirmationBG').fadeIn(500);
	$('#exclusiontext_' + num).fadeIn(1000);
	$('#exclusionbtn_' + num).fadeIn(300);
}
function exclusionBoxFade(num){
	$('#exclusion_' + num).hide(0);
	$('.confirmationBG').hide(0);
	$('#exclusiontext_' + num).hide(0);
	$('#exclusionbtn_' + num).hide(0);
}

//========================================================================
//Paginação de mensagens em Mensagens Enviadas============================
function setToClasse(cls, val){
	$allSelectors = document.querySelectorAll('.' + cls).length;
	for(var $i = 0; $i < $allSelectors; $i++){
		var elem = document.getElementsByClassName(cls)[$i];
		elem.classList.add(val);
	}
}

function changepage(pgID){
	var tgtclass = "_" + pgID;
	setToClasse("li_panel", "otherpage");
	$allSelectors = document.querySelectorAll('.' + tgtclass).length;
	for(var $i = 0; $i < $allSelectors; $i++){
		var elem = document.getElementsByClassName(tgtclass)[$i];
		elem.classList.remove('otherpage');
	}	
}
//========================================================================
//Função AJAX=============================================================
function getContents(elemId){
	var file = $("#"+elemId).attr("data-pointer");
	var ind = $("#"+elemId).attr("data-index");
	$countcls = document.querySelectorAll('.bodymsg').length;
	for(var $i = 0; $i < $countcls; $i++){
		var elem = document.getElementsByClassName('bodymsg')[$i];
		$('#'+elem.id).hide();
	}
	$countcls = document.querySelectorAll('.readbodymsg').length;
	for(var $i = 0; $i < $countcls; $i++){
		var elem = document.getElementsByClassName('readbodymsg')[$i];
		$('#'+elem.id).hide();
	}
	$("#emptyMessage").hide();
	//$("#readbodymsg").fadeIn(300);
	$("#readbodymsg"+ind).fadeIn(300);
	var target = file;
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			document.getElementById('R_'+ind).innerHTML = this.responseText;
		}
	};
	xhttp.open('GET', "collectMsg.php?index=" + target, true);
	xhttp.send();
};
