<?php

require_once dirname(__FILE__).'/../zforms_config.php';

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        
		<title>Zoho Forms</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
		<script type="text/javascript" src="jquery.js"></script>
		<script language="javascript" type="text/javascript" src="zforms_dailog.js"></script> 
		<link href="zforms_dailog.css" rel="stylesheet">		
     </head>
     <body style="background: rgba(0,0,0,0.7);">
	
		<div class="zf-WpPopupContainer">
		<div class="zf-WpMainWrap">

			<input id="zf_embedCatogory" type="hidden" value="formPerma">
			<div class="zf-WpFormPermaWrap" id="zf_tiny_homeDiv" >
				<p>You can choose a form from your Zoho Form's account or enter your  form's permalink</p>
				<button onclick="showChooseFormDiv();">Access Zoho Forms</button>
				<span>or</span>
				<a onclick="embedPerma();">Enter Form Permalink</a>
			</div>

			<div class="zf-WpEmbeddedWrap zf-EmbedInner" id="permaLinkDiv" style="display:none">
				<div class="zf-WpEmbeddedHead">
						<span onclick="goToHome();">
							<img src="backbtn.jpg">
						</span>
						<h3>Embedded with Permalink</h3>
					</div>
					

					<div class="zf-WpEmbeddedContainer">
						  <div class="embeddedPublicLink" id="permaContainer">
							  	<label>Enter your form's public Link <em>*</em></label>
							  	<textarea onchange="hideError()" id="permalink"></textarea>
							  	<span>Not sure where to find your form's permalink URL? <a href="https://www.zoho.com/forms/help/share/public-sharing.html#link" target="_blank">Click here</a> to learn more.</span>
							  	<p id="permaLinkError" style="display:none;"/>
							</div>
					</div>
			</div>
			<div class="zf-WpEmbeddedWrap zf-EmbedInner" id="chooseFormDiv" style="display: none;">
				<div class="zf-WpEmbeddedHead"> 
					<span onclick="goToHome();"><img src="backbtn.jpg"></span>
						<h3>Choose your form</h3>
				</div>
				<div id="domainSelectionDiv" class="zf-WpSelectDomain">
					<label>Choose the domain where you've registered your Zoho account.<em>*</em></label>
						<span class="cusDropArrow"></span>
						<select id="zForm_domain" onchange="hideDomainSelctError()"; >
							<option value="-select-">-Select-</option>
							<option value=".com">zoho.com</option>
							<option value=".eu">zoho.eu</option>
							<option value=".com.cn">zoho.com.cn</option>
							<option value=".in">zoho.in</option>
							<option value=".com.au">zoho.com.au</option>
							<option value=".jp">zoho.jp</option>
							<option value=".ca">zohocloud.ca</option>
							<option value=".sa">zoho.sa</option>
						</select>
					
					<p id="domainSelectionErr" style="display:none">Please choose a domain.</p>
					<div class="zf-wpFooter" style="border-top: 0px;">
						<button onclick="proceedToSelectFormOnTinyMce();">Connect</button>
					</div>
				</div>
				<div class="zf-WpSelectDomain zf-WpSelectFrm" id="formSelectionDiv" style="display:none">
		   			<div id="selctContainer">
						<label>Choose a form<em>*</em></label>
						<span class="cusDropArrow"></span>
							<select id="formname" onchange="showEmbedActionsDiv();hideError();"; style="width: 100%" >
								<option value="-Select-">-Select-</option>
							</select>
						
						<p id="formSelectionError" />
					</div>
				</div>
				<div class="zf-WpFormPermaWrap zf-WpFormRefresh" id="signinDiv" style="display:none">
							<p> Sign in to your Zoho Forms account to select a form. </p>
						<div class="zf-wpFooter" style="border-top: 0px;">
							<button onclick="signin()">Sign In</button>
						</div>
				</div>
				<div class="zf-WpFormPermaWrap zf-WpFormRefresh" id="createFormDiv" style="display:none">
					<p> You have not created any forms yet. Click on Create to build a new form. </p>
					<div class="zf-wpFooter" style="border-top: 0px;">
						<button onclick="createForm()">Create</button>	
					</div>
				</div>
				<div class="zf-WpFormPermaWrap zf-WpFormRefresh" id="refreshDiv" style="display:none">
					<p> Please Refresh to view your forms. </p>
					<div class="zf-wpFooter" style="border-top: 0px;">
						<button id="button_refresh" onclick="proceedToSelectFormOnTinyMce()">Refresh</button>		
					</div>
				</div>
				<div id="loadingDiv" class="zf-WpFormPermaWrap zf-WpFormRefresh" style="display: none;">
					<p>loading</p>
					<div class="zf-wb-spinner">
						<div class="bounce1"></div>
						<div class="bounce2"></div>
						<div class="bounce3"></div>
					</div>
				</div>
			</div>

			<div class="zf-WpEmbeddedWrap" id="embedActionsDiv" style="display:none">
				<div class="embedFormUsing">
				  	<label>Embed form using</label>
					<div class="flLeft cusRadioButton">
						<span> 
							<input name="embedType" id="iframeEmbed" type="radio" value="iframe" checked="" onchange="showiframeOrJsChange();">
							<label for="iframeEmbed">Iframe</label> 
						</span>
						<span> 
							<input name="embedType" id="jsEmbed" type="radio" value="jsCode" onchange="showiframeOrJsChange();">
							<label for="jsEmbed">Javascript</label> 
						</span>
					</div>
					<div id="autoHeightDiv" style="display:none;" class="flLeft cusCheckBox">
						<input type="checkbox" id="autoHeightCB" onchange="disableHeight();">
						<label for="autoHeightCB">Set form height automatically.</label>
					</div>

					<div class="embedFormInputField">
						<span>
							<label>Width</label>
							<input type="text" value="100%" id="width">
						</span>
						<span>
							<label>Height</label>
							<input type="text" value ="600px" id="height">
						</span>
					</div>
			  	</div>
				<div class="embedIncldeParamtrs">
				   	<div class="cusCheckBox">
				   		<input type="checkbox" id="queryParamsCB" onchange="showOrHideUrlParamsDiv();">
				   		<label for="queryParamsCB">Include URL Parameters</label>
				   	</div>
				   	<span>Include parameters to prefill forms and add referrers to track sources</span>
				 </div>
				 <div class="embedParameterValue" id="urlParamsDiv" style="display:none;">
								<div class="embedField">
										<div class="embedFiel_Col1"> Parameter Name </div>
										<div class="embedFiel_Col2"> Parameter Value  </div>
								</div>
								<div class="clearBoth"></div>
								<div class="addparamsWrap" elname="paramTemplate" eltype="keyValPair" style="display:none;">
										<div class="addParamaFeld">
												<input type="text" elname="paramKey" maxlength="50" onchange="hideparamError(this);">
										</div>
										<div class="intArrow"><em class="arrowImg"></em></div>
										<div class="addParamaFeld">
												<input type="text" elname="value" maxlength="50">
										</div>
										<div class="zf-Wp-plusminus"> 
											<span class="fiedPropPlus" onclick="addUrlParam(this);"></span> 
											<span class="fiedPropMinus" onclick="deleteParam(this);"> </span> 
										</div>
									</div>
								<div elname="paramsDiv">
								</div>
						</div>
						<div id="paramError" class="errorCont" style="display:none;">
							<p>Parameters should contain only alphanumeric characters, underscore, and hyphen.</p>
						</div>
				<div class="clearBoth"></div>
				 <div class="zf-wpFooter">
					<button onclick="zforms_submit()">Embed</button>
				</div>
			</div>
		</div>
			<div class="zf-WpBranding"> 
				<img src="zohobranding.jpg">
			</div>
		</div>
					
	</body>
</html>

