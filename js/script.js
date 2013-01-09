
function showElem(pDiv){
	if($('#'+pDiv).is(":hidden")){
		$('#'+pDiv).slideDown("Normal");
	}else{
		$('#'+pDiv).slideUp("Normal");
	}
}

function testMail(pTxt){
	var email = /^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$/;
	if(pTxt != ''){
		if(pTxt.match(email)){
			return "";
		}else{
			return "Veuillez inscrire un email valide";
		}
	}
	return "";
}

function isNotEmpty(pTxt){
	if(pTxt != ''){
		return "";
	}else{
		return "Ce champ ne peut pas rester vide";
	}
}

function testInput(pElem){
	var error = new Array();
	var nbrError = 0;
	var notError = true;
	var tError = '';
	
	$('#'+$(pElem).attr("error_id")).slideUp("Normal", function(){
		$('#'+$(pElem).attr("error_id")).html("");
	});
	
	if($(pElem).attr("email") == "true"){
		tError = testMail($(pElem).val());
		if(tError != ''){
			error[nbrError] = tError;
			nbrError++;
		}
	}
	
	if($(pElem).attr("require") == "true"){
		tError = isNotEmpty($(pElem).val());
		if(tError != ''){
			error[nbrError] = tError;
			nbrError++;
		}
	}
	
	if(nbrError != 0){
		$('#'+$(pElem).attr("error_id")).slideDown("Normal", function(){
			for(var i = 0; i < nbrError; i++){
				$('#'+$(pElem).attr("error_id")).html($('#'+$(pElem).attr("error_id")).html()+error[i]+'<br />');
			}
		});
		return false;
	}else{
		return true;
	}
	
}

function sendMailAdmin(pId){
	var txt = $('#txt_ctc_mj_'+pId).val();
	txt = txt.replace("&", "%26");
	
	if(testInput('#mail_ctct_mj_'+pId)){
		var email = $('#mail_ctct_mj_'+pId).val();
		getAjax('#ret_ctct_mj_'+pId, 'actions/party.php', 'action=ctc_mj&txt='+txt+"&email="+email+"&paty_id="+pId+"", sendMailAdminConfirm);
	}
}

function sendMailAdminConfirm(pTxt, pId){
	if(pTxt == "1"){
		alert("Votre email a bien été envoyé.");
		//$(pId).slideDown("Slow", function(){
		//	$(pId).html("Votre email a bien été envoyé.");
		//});
	}else{
		alert("Erreur lors de l'envoi de l'email");
	}
}

function getAjax(pDiv, pPage, pArgs, pFuncName){

	$.ajax({
		type: "POST",
		data: pArgs,
		url: pPage,
		dataType: "html",
		success: function(html){
			pFuncName.call(this, html, pDiv);
		},
		error: function(xhr, ajaxOptions, thrownError){
			alert(thrownError);
		}
	});
}