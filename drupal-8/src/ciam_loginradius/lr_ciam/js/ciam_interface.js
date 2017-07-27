//initialize ciam options
var ciamoption = {};
var LocalDomain = drupalSettings.ciam.callback;
var homeDomain = drupalSettings.ciam.home;
var loggedIn = drupalSettings.ciam.loggedIn;
var domainName = drupalSettings.ciam.appPath;
ciamoption.apiKey = drupalSettings.ciam.apiKey;
ciamoption.appName = drupalSettings.ciam.appName;
ciamoption.appPath = drupalSettings.ciam.appPath;
ciamoption.verificationUrl = drupalSettings.ciam.verificationUrl;
ciamoption.forgotPasswordUrl = drupalSettings.ciam.forgotPasswordUrl;
ciamoption.sott = drupalSettings.ciam.sott;
ciamoption.callbackUrl = drupalSettings.ciam.callback;
ciamoption.hashTemplate = true; 
if(drupalSettings.ciam.formValidationMessage){
	ciamoption.formValidationMessage = drupalSettings.ciam.formValidationMessage;
}
if(drupalSettings.ciam.termsAndConditionHtml){
	ciamoption.termsAndConditionHtml = drupalSettings.ciam.termsAndConditionHtml;
}
if(drupalSettings.ciam.formRenderDelay){
	ciamoption.formRenderDelay = drupalSettings.ciam.formRenderDelay;
}
if(drupalSettings.ciam.passwordminlength && drupalSettings.ciam.passwordmaxlength){
	ciamoption.passwordLength  = {min : drupalSettings.ciam.passwordminlength, max :drupalSettings.ciam.passwordmaxlength}
}
if(drupalSettings.ciam.stayLogin){
	ciamoption.stayLogin = drupalSettings.ciam.stayLogin;
}
if(drupalSettings.ciam.askRequiredFieldForTraditionalLogin){
	ciamoption.askRequiredFieldForTraditionalLogin = drupalSettings.ciam.askRequiredFieldForTraditionalLogin;
}
if(drupalSettings.ciam.displayPasswordStrength){
	ciamoption.displayPasswordStrength = drupalSettings.ciam.displayPasswordStrength;
}
if(drupalSettings.ciam.usernameLogin){
	ciamoption.usernameLogin = drupalSettings.ciam.usernameLogin;
}
if(drupalSettings.ciam.askEmailForUnverifiedProfileAlways){
	ciamoption.askEmailForUnverifiedProfileAlways = drupalSettings.ciam.askEmailForUnverifiedProfileAlways;
}
if(drupalSettings.ciam.loginOnEmailVerification){
	ciamoption.loginOnEmailVerification = drupalSettings.ciam.loginOnEmailVerification;
}
if(drupalSettings.ciam.promptPasswordOnSocialLogin){
	ciamoption.promptPasswordOnSocialLogin = drupalSettings.ciam.promptPasswordOnSocialLogin;
}
if(drupalSettings.ciam.optionalEmailVerification){
	ciamoption.optionalEmailVerification = drupalSettings.ciam.optionalEmailVerification;
}
if(drupalSettings.ciam.verificationEmailTemplate){
	ciamoption.verificationEmailTemplate = drupalSettings.ciam.verificationEmailTemplate;
}
if(drupalSettings.ciam.disabledEmailVerification){
	ciamoption.disabledEmailVerification = drupalSettings.ciam.disabledEmailVerification;
}
if(drupalSettings.ciam.customScript){  
       eval(drupalSettings.ciam.customScript);  
}
jQuery(document).ready(function () {
    initializeResetPasswordCiamForm(ciamoption);
});