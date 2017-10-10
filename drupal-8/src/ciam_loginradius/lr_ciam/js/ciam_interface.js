//initialize ciam options
var commonOptions = {};
var LocalDomain = drupalSettings.ciam.callback;
var homeDomain = drupalSettings.ciam.home;
var accessToken = drupalSettings.ciam.accessToken;
var autoHideTime = drupalSettings.ciam.autoHideTime;
var loggedIn = drupalSettings.ciam.loggedIn;
var domainName = drupalSettings.ciam.appPath;
commonOptions.apiKey = drupalSettings.ciam.apiKey;
commonOptions.appName = drupalSettings.ciam.appName;
commonOptions.appPath = drupalSettings.ciam.appPath;
commonOptions.verificationUrl = drupalSettings.ciam.verificationUrl;
commonOptions.forgotPasswordUrl = drupalSettings.ciam.forgotPasswordUrl;
commonOptions.callbackUrl = drupalSettings.ciam.callback;
commonOptions.hashTemplate = true; 
if(drupalSettings.ciam.formValidationMessage){
	commonOptions.formValidationMessage = drupalSettings.ciam.formValidationMessage;
}
if(drupalSettings.ciam.termsAndConditionHtml){
	commonOptions.termsAndConditionHtml = drupalSettings.ciam.termsAndConditionHtml;
}
if(drupalSettings.ciam.formRenderDelay){
	commonOptions.formRenderDelay = drupalSettings.ciam.formRenderDelay;
}
if(drupalSettings.ciam.verificationEmailTemplate){
	commonOptions.verificationEmailTemplate = drupalSettings.ciam.verificationEmailTemplate;
}
if(drupalSettings.ciam.resetPasswordEmailTemplate){
	commonOptions.resetPasswordEmailTemplate = drupalSettings.ciam.resetPasswordEmailTemplate;
}
if(drupalSettings.ciam.passwordminlength && drupalSettings.ciam.passwordmaxlength){
	commonOptions.passwordLength  = {min : drupalSettings.ciam.passwordminlength, max :drupalSettings.ciam.passwordmaxlength}
}
if(drupalSettings.ciam.displayPasswordStrength){
	commonOptions.displayPasswordStrength = drupalSettings.ciam.displayPasswordStrength;
}
if(drupalSettings.ciam.stayLogin){
	commonOptions.stayLogin = drupalSettings.ciam.stayLogin;
}
if(drupalSettings.ciam.askRequiredFieldForTraditionalLogin){
	commonOptions.askRequiredFieldForTraditionalLogin = drupalSettings.ciam.askRequiredFieldForTraditionalLogin;
}
if(drupalSettings.ciam.enableGoogleRecaptcha){   
    if(drupalSettings.ciam.recaptchaType== 'v2Recaptcha'){
	commonOptions.v2Recaptcha = true;
    }else{
        commonOptions.invisibleRecaptcha = true;
    }
        if(drupalSettings.ciam.v2RecaptchaSiteKey){
	commonOptions.v2RecaptchaSiteKey = drupalSettings.ciam.v2RecaptchaSiteKey;
    }
} else {
    commonOptions.sott = drupalSettings.ciam.sott;
}

if(drupalSettings.ciam.loginTypeEmail){ 
if(drupalSettings.ciam.loginOnEmailVerification){
	commonOptions.loginOnEmailVerification = drupalSettings.ciam.loginOnEmailVerification;
}
if(drupalSettings.ciam.promptPasswordOnSocialLogin){
	commonOptions.promptPasswordOnSocialLogin = drupalSettings.ciam.promptPasswordOnSocialLogin;
}
if(drupalSettings.ciam.usernameLogin){
	commonOptions.usernameLogin = drupalSettings.ciam.usernameLogin;
}
if(drupalSettings.ciam.askEmailForUnverifiedProfileAlways){
	commonOptions.askEmailForUnverifiedProfileAlways = drupalSettings.ciam.askEmailForUnverifiedProfileAlways;
}
if(drupalSettings.ciam.optionalEmailVerification){
	commonOptions.optionalEmailVerification = drupalSettings.ciam.optionalEmailVerification;
}
if(drupalSettings.ciam.disabledEmailVerification){
	commonOptions.disabledEmailVerification = drupalSettings.ciam.disabledEmailVerification;
}}

if(drupalSettings.ciam.loginTypePhone){   
if(drupalSettings.ciam.phoneLogin){   
    commonOptions.phoneLogin = true;
    if(drupalSettings.ciam.existPhoneNumber){
	commonOptions.existPhoneNumber = drupalSettings.ciam.existPhoneNumber;
    }   
    if(drupalSettings.ciam.smsTemplateWelcome){
	commonOptions.smsTemplateWelcome = drupalSettings.ciam.smsTemplateWelcome;
    }
    if(drupalSettings.ciam.smsTemplatePhoneVerification){
	commonOptions.smsTemplatePhoneVerification = drupalSettings.ciam.smsTemplatePhoneVerification;
    }
    if(drupalSettings.ciam.instantOTPLogin){
	commonOptions.instantOTPLogin = drupalSettings.ciam.instantOTPLogin;
    }
    if(drupalSettings.ciam.smsTemplateInstantOTPLogin){
	commonOptions.smsTemplateInstantOTPLogin = drupalSettings.ciam.smsTemplateInstantOTPLogin;
    }
    if(drupalSettings.ciam.instantOTPLoginButtonLabel){
	commonOptions.instantOTPLoginButtonLabel = drupalSettings.ciam.instantOTPLoginButtonLabel;
    }    
}} else if(drupalSettings.ciam.instantLinkLogin){
	commonOptions.instantLinkLogin = drupalSettings.ciam.instantLinkLogin;
        if(drupalSettings.ciam.instantLinkLoginEmailTemplate){
	commonOptions.instantLinkLoginEmailTemplate = drupalSettings.ciam.instantLinkLoginEmailTemplate;
        }
        if(drupalSettings.ciam.instantLinkLoginButtonLabel){
	commonOptions.instantLinkLoginButtonLabel = drupalSettings.ciam.instantLinkLoginButtonLabel;
        }
}
if(drupalSettings.ciam.enableTwoFactorAuth){   
    if(drupalSettings.ciam.twoFactorAuthFlow == 'required'){
	commonOptions.twoFactorAuthentication = true;
    }else{
        commonOptions.optionalTwoFactorAuthentication = true;
    }    
    if(drupalSettings.ciam.googleAuthentication){
	commonOptions.googleAuthentication = drupalSettings.ciam.googleAuthentication;
    }
    if(drupalSettings.ciam.smsTemplate2FA){
	commonOptions.smsTemplate2FA = drupalSettings.ciam.smsTemplate2FA;
    }
}
if(drupalSettings.ciam.debugMode){
	commonOptions.debugMode = drupalSettings.ciam.debugMode;
}
if(drupalSettings.ciam.customScript){  
       eval(drupalSettings.ciam.customScript);  
}
jQuery(document).ready(function () {
    initializeResetPasswordCiamForm(commonOptions);
});