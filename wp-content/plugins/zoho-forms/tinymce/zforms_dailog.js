var a="";
var zForms=new Array();
				
if(window.tinyMCE){ 
	var url = tinyMCEPopup.getWindowArg("plugin_url");
}
				
				
function zf_submit()
{
	var perma_link = document.getElementById("permalink").value;
	if(perma_link.length==0)
	{
		document.getElementById("permaLinkError").style.display="block";
		document.getElementById("permaLinkError").innerHTML="Please enter your form's public link.";
		document.getElementById("permaContainer").classList.add("errorCont");
	}
	else
	{
		var permaLinkFormWidth = document.getElementById('iframeWidth');
		if(permaLinkFormWidth.value=="")
		{
			permaLinkFormWidth.value="100%";
		}
		var permaLinkFormHeight = document.getElementById('iframeHeight');
		if(permaLinkFormHeight.value=="")
		{
			permaLinkFormHeight.value="600px";
		}	
		insertContent(perma_link,permaLinkFormWidth.value,permaLinkFormHeight.value);
	}
}

function insertContent(src,width,height)
{
		
		var tag = '[zohoForms src=';
		tag += src;
		tag += ' width=';
		tag += width;
		tag += ' height=';
		tag += height;
		tag += '/]';		
		if(window.tinyMCE)
		{
			var tmce_ver=window.tinyMCE.majorVersion;
			if (tmce_ver>="4")
			{
				window.tinyMCE.execCommand('mceInsertContent', false, tag);
			}
			else
			{
				window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tag);
			}
			tinyMCEPopup.editor.execCommand('mceRepaint');
			tinyMCEPopup.close();
		}	
}

function closePopUp(){
	if(window.tinyMCE)
	{
		tinyMCEPopup.close();
	}
}
			
	
function getForms()
{
	if(a!="")
	{
		var len = a.forms.length;
		formList = document.getElementById("formname");
		for(i = 0; i < len;i++)
		{
			var option = document.createElement("option");
			option.text = a.forms[i].display_name;
			option.value = a.forms[i].link_name;
			formList.options.add(option);
			var formAndPerma = new Array();
			formAndPerma[0] = a.forms[i].link_name;
			formAndPerma[1] = a.forms[i].public_url;
			zForms[option.value] = formAndPerma;

		}
	}
					
}
				

function zforms_submit()
{
	var src='';
	var embedType = $("#zf_embedCatogory").val();
	if(embedType == "formPerma"){
		var perma_link = document.getElementById("permalink").value;
		if(perma_link.length==0)
		{
			document.getElementById("permaLinkError").style.display="block";
			document.getElementById("permaLinkError").innerHTML="Please enter your form's public link.";
			document.getElementById("permaContainer").classList.add("errorCont");
			return;
		}
		src = perma_link;
	}else if(embedType == "formSelect"){
		var formName = document.getElementById("formname").value;
		if(formName == "-Select-")
		{		
			document.getElementById("formSelectionError").style.display="block";		
			document.getElementById("formSelectionError").innerHTML="Please select a form.";
			document.getElementById("selctContainer").className= "errorCont";
			return;
		}
		src = formName;
	}
	
		var urlParams = '';
		if($("#queryParamsCB").is(":checked")){
			var hasError = false;
			var paramsDiv = $("#urlParamsDiv").find("div[elname=paramsDiv]");
			$(paramsDiv).find("div[eltype=keyValPair]").each(function(index,elem){
				var paramKeyRegex = /^[a-zA-Z0-9-_]+$/;
				var paramKey = $.trim($(elem).find("input[elname=paramKey]").val());
				var paramVal = $.trim($(elem).find("input[elname=value]").val());
				if(!paramKeyRegex.test(paramKey)){
					hasError = true;
					$(elem).find("input[elname=paramKey]").parent("div").addClass("errorCont");
				}
				if(paramKey!='' && paramVal!=''){
					if(urlParams!=''){
						urlParams+="&";
					}
					urlParams+=paramKey+"="+encodeURIComponent(paramVal);
				}
			});
			if(hasError){
				$("#paramError").show();
				return;
			}
		}
		var formWidth = document.getElementById('width');
		if(formWidth.value=="")
		{
			formWidth.value="100%";
		}
		var formHeight = document.getElementById('height');
		if(formHeight.value=="")
		{
			formHeight.value="600px";
		}
		//insertContent(urlBuild,formWidth.value,formHeight.value);
		var tag = '[zohoForms src=';
		tag += src;
		tag += ' width=';
		tag += formWidth.value;
		tag += ' height=';
		tag += formHeight.value;

		if(urlParams !=''){
			tag +=' urlparams="'+urlParams+'"';
		}
		if($("#jsEmbed").is(":checked")){
			tag +=' type=js';
		}
		if($("#autoHeightCB").is(":checked")){
			tag +=' autoheight=true'
		}
		tag += '/]';
		insertShortCode(tag);
	//}
}

function insertShortCode(shortCodeString){
		if(window.tinyMCE)
		{
			var tmce_ver=window.tinyMCE.majorVersion;
			if (tmce_ver>="4")
			{
				window.tinyMCE.execCommand('mceInsertContent', false, shortCodeString);
			}
			else
			{
				window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, shortCodeString);
			}
			tinyMCEPopup.editor.execCommand('mceRepaint');
			tinyMCEPopup.close();
		}
}

function goToHome(){
	$("#zf_tiny_homeDiv").show();
	$("#permaLinkDiv").hide();
	$("#chooseFormDiv").hide();
	$("#embedActionsDiv").hide();
}			
			
function embedPerma()
{
	$("#zf_tiny_homeDiv").hide();
	$("#zf_embedCatogory").val("formPerma");
	$("#permaLinkDiv").show();
	$("#embedActionsDiv").show();
	if(document.getElementById("permaContainer").classList.contains("errorCont"))
	{
		document.getElementById("permaContainer").classList.remove("errorCont");
		document.getElementById("permaLinkError").style.display="none";
	}
	//document.getElementById("publicLink").className= "selected";
	//document.getElementById("permaLinkDiv").style.display="block";
	document.getElementById("chooseFormDiv").style.display= "none";
	//document.getElementById("selectForm").classList.remove("selected");
	
}
		
function signin()
{				
	if(window.tinyMCE)
	{
		var domainValue = $("#zForm_domain").val();
		var formsUrl = getZohoURL(domainValue);
		if(formsUrl !=""){
			window.open(formsUrl+"/forms/login.html");
			document.getElementById("refreshDiv").style.display="block";
			document.getElementById("signinDiv").style.display="none";
		}
	}				
}
function appendScript()
{
	var scriptToAppend = document.createElement("script");
	scriptToAppend.type = "text/javascript";
	scriptToAppend.id="api";
	scriptToAppend.src = url+"/dynamicScript.js";
	document.head.appendChild(scriptToAppend);
}
function chooseForm()
{
	if(!document.getElementById("api")||a=="")
	{
		appendScript();
	}
	else
	{
		selectForm();
	}
}
function showChooseFormDiv()
{
	$("#zf_tiny_homeDiv").hide();
	$("#zf_embedCatogory").val("formSelect");
	//document.getElementById("selectForm").classList.add("selected");
	document.getElementById("chooseFormDiv").style.display="block";
	document.getElementById("permaLinkDiv").style.display= "none";
	//document.getElementById("publicLink").className= "none";
	showEmbedActionsDiv();
}

function proceedToSelectFormOnTinyMce(){
	var domainValue = $("#zForm_domain").val();
	if(domainValue == "-select-"){
		$("#domainSelectionErr").show();
		$("#domainSelectionDiv").addClass("errorCont");
		return;
	}
	var formsUrl = getZohoFormsURL(domainValue);
	if(formsUrl !=""){
		getZohoFormsLisOnTinyMce(formsUrl);
		$("#domainSelectionDiv").hide();
		$("#refreshDiv").hide();
		$("#loadingDiv").show();
		setTimeout(function() {
    		$("#loadingDiv").hide();
    		if(a==""){
				$("#signinDiv").show();
			}else if(a.forms.length == 0){
				$("#createFormDiv").show();
			}else{
				$("#formSelectionDiv").show();
			}
			
		}, 2000);
	}
}
function getZohoFormsLisOnTinyMce(formsUrl){
  			var apiURL = formsUrl+"/api/getforms?type=plugin";
  			$("#loadingDiv").show();
  			getFormsAndIncludeScriptOnTinyMce(apiURL,addToFormListDropDown);
  		}

		function addToFormListDropDown(){
  			if(a!=""){
  				var len = a.forms.length;
  				if(len > 0){
  				
	  				var formList =document.getElementById("formname");
	  				for(i = 0; i < len;i++){
	  					var option = document.createElement("option");
						option.text = a.forms[i].display_name;
						option.value = a.forms[i].public_url;
						formList.options.add(option);
	  				}
	  				$("#zFormSelectDiv").show();
  				}
  			}
  		}
  		function getFormsAndIncludeScriptOnTinyMce(url,callback)
		{
		    var script = document.createElement("script")
		    script.type = "text/javascript";
		    if (script.readyState)
		    {  
			//IE

			script.onreadystatechange = function(){ 		
		            if (script.readyState == "loaded" || script.readyState == "complete")
			    	{
		                script.onreadystatechange = null;
		                callback();
		            }
		        };
		    } 
		    else 
		    {
			//Others
		       script.onload = function(){
		            callback();
		        };
		        script.onerror = function(){
		    		callback();
		    	};
		    }	
		    script.src = url;
		    document.getElementsByTagName("head")[0].appendChild(script);	
		}
//constructing url based on domain extention
function getZohoFormsURL(domain){
  	if(domain != undefined && domain.length != 0 && domain != "-select-"){
  		if(domain == ".com" || domain == ".eu" || domain == ".com.cn" || domain == ".com.au" || domain == ".in" || domain == ".jp" || domain == ".sa") {
  			return "https://forms.zoho"+domain;
		}else if(domain == ".ca") {
			return "https://forms.zohocloud" + domain;
		}else {
			return "";
		}
  	}else{
  		return "";
  	}
}
function getZohoURL(domain){
	if(domain != undefined && domain.length != 0 && domain != "-select-"){
		if(domain == ".ca") {
			return "https://zohocloud"+domain;
		}else {
  			return "https://zoho"+domain;
		}
  	}else{
  		return "";
  	}
}
function hideDomainSelctError(){
	$("#domainSelectionDiv").removeClass("errorCont");
	$("#domainSelectionErr").hide();
}

function hideError()
{
	if(document.getElementById("permaContainer").classList.contains("errorCont"))
	{
		document.getElementById("permaContainer").classList.remove("errorCont");
		document.getElementById("permaLinkError").style.display="none";
	}
	if(document.getElementById("selctContainer").classList.contains("errorCont")){
		document.getElementById("formSelectionError").style.display="none";
		document.getElementById("selctContainer").classList.remove("errorCont");
	}
}



function selectForm()
{
	document.getElementById("selectForm").classList.add("selected");
	if(document.getElementById("selctContainer").classList.contains("errorCont"))
	{
		document.getElementById("selctContainer").classList.remove("errorCont");
		document.getElementById("formSelectionError").style.display="none";
	}
		
	if(a!="")
	{
		if(a.forms.length==0)
		{
			document.getElementById("createFormDiv").style.display="block";
			document.getElementById("chooseFormDiv").style.display="none";
			document.getElementById("signinDiv").style.display="none";
		}
		else
		{
			document.getElementById("chooseFormDiv").style.display="block";
			document.getElementById("signinDiv").style.display="none";
		}	
	}
	else
	{	
		
		document.getElementById("signinDiv").style.display="block";


				
	}
	document.getElementById("permaLinkDiv").style.display= "none";
	document.getElementById("refreshDiv").style.display="none";
	document.getElementById("publicLink").className= "none";
}
		
function refresh()
{
	
	if(document.getElementById("api"))
	{
		document.getElementById("api").remove();
	}
	chooseForm();
}

function createForm()
{
	var domainValue = $("#zForm_domain").val();
	var formsUrl = getZohoFormsURL(domainValue);
	if(formsUrl !=""){
		window.open(formsUrl);	
		document.getElementById("refreshDiv").style.display="block";	
		document.getElementById("createFormDiv").style.display="none";
		document.getElementById("signinDiv").style.display="none";
	}
}

function showOrHideUrlParamsDiv(){
	$("#paramError").hide();
	if($("#queryParamsCB").is(":checked")){
		var urlParamsDiv = $("#urlParamsDiv");
		var paramsDiv = $(urlParamsDiv).find("div[elname=paramsDiv]");
		$(paramsDiv).empty();
		var newParam = $(urlParamsDiv).find("div[elname=paramTemplate]").clone();
		$(newParam).removeAttr("elname");
		$(newParam).show();
		$(paramsDiv).append($(newParam));
		$(urlParamsDiv).slideDown();
	}else{
		$("#urlParamsDiv").slideUp();
	}
}
function addUrlParam(elem){
	var urlParamsDiv = $("#urlParamsDiv");
	var newParam = $(urlParamsDiv).find("div[elname=paramTemplate]").clone();
	$(newParam).removeAttr("elname");
	$(newParam).show();
	$(newParam).insertAfter($(elem).parent().parent("div[eltype=keyValPair]"));
}

function showiframeOrJsChange(){
	if($("#jsEmbed").is(":checked")){
		$("#autoHeightDiv").slideDown();
	}else{
		$("#autoHeightDiv").slideUp();
	}
	$("#autoHeightCB").prop("checked",false);
	$("#height").removeProp("disabled");
	$("#height").val("600px");

}
function deleteParam(elem){
	$(elem).parent().parent("div").remove();
	var urlParamsDiv = $("#urlParamsDiv");
	var paramsDiv = $(urlParamsDiv).find("div[elname=paramsDiv]");
	var parmsCount = $(paramsDiv).find("div[eltype=keyValPair]").length;
	if(parmsCount == 0){
		$("#queryParamsCB").prop("checked",false);
		$("#urlParamsDiv").slideUp();
	}
}
function showEmbedActionsDiv(){
	var formname = $("#formname").val();
	if(formname !="-Select-"){
		$("#embedActionsDiv").slideDown();
	}else{
		$("#embedActionsDiv").slideUp();
	}

}
function disableHeight(){
	if($("#autoHeightCB").is(":checked")){
		$("#height").prop("disabled",true);
		$("#height").val('');
	}else{
		$("#height").removeProp("disabled");
		$("#height").val('600px');
	}
}

function hideparamError(elem){
	$(elem).parent("div").removeClass("errorCont");
	$("#paramError").hide();
}
