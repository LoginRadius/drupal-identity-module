jQuery(document).ready(function () {

    jQuery("#fade, #lr-loading").click(function () {
        jQuery('#fade, #lr-loading').hide();
    });

    window.addEventListener( "pageshow", function ( event ) {
        var historyTraversal = event.persisted || 
                               ( typeof window.performance != "undefined" && 
                                    window.performance.navigation.type === 2 );
        if ( historyTraversal ) {
          // Handle page restore.
          window.location.reload();
        }
    });

    dropemailvalue = '';
    jQuery('.removeEmail').each(function () {
        jQuery(this).click(function () {
            jQuery('form[name="loginradius-removeemail"]').remove();
            var html = jQuery(this).parents('tr');
            dropemailvalue = jQuery(this).parents('tr').find('.form-text').val();
            showRemoveEmailPopup(html);
        });
    });
    showAndHideinstantPhoneOptions();
    showAndHideinstantemailOptions();
    showAndHideCustomPageDiv();
});

function showAndHideCustomPageDiv() {
    var options = jQuery('input[name=lr_ciam_userlogin_redirect]:checked').val();
    if (options == '2') {
        jQuery('.form-item-lr-ciam-custom-redirection').show();
    } else {
        jQuery('.form-item-lr-ciam-custom-redirection').hide();
    }
}

function showAndHideinstantPhoneOptions() {
    var options = jQuery('input[name=lr_ciam_instant_otp_login]:checked').val();
    if (options == 'true') {
        jQuery('.form-item-lr-ciam-sms-template-one-time-passcode').show();
    } else {
        jQuery('.form-item-lr-ciam-sms-template-one-time-passcode').hide();
    }
}

function showAndHideinstantemailOptions() {
    var options = jQuery('input[name=lr_ciam_instant_link_login]:checked').val();
    if (options == 'true') {
        jQuery('.form-item-lr-ciam-instant-link-login-email-template').show();
    } else {
        jQuery('.form-item-lr-ciam-instant-link-login-email-template').hide();
    }
}


 if (typeof LoginRadiusV2 === 'undefined') {
  var e = document.createElement('script');
  e.src = 'https://auth.lrcontent2.com/v2/js/LoginRadiusV2.js';
  e.type = 'text/javascript';
  document.getElementsByTagName("head")[0].appendChild(e);
  }
  
  var lrloadInterval = setInterval(function () {
    	        if (typeof LoginRadiusV2 != 'undefined') {
        	clearInterval(lrloadInterval);
                 LRObject = new LoginRadiusV2(commonOptions);
    	        }
	        }, 1);
function showRemoveEmailPopup(html) {
    jQuery('#removeemail-form').show();
    initializeRemoveEmailCiamForms(html);
}

function showAddEmailPopup() {
    jQuery('#addemail-form').show();
    initializeAddEmailCiamForms();
}

function lrCloseRemovePopup() {
    jQuery('form[name="loginradius-removeemail"]').remove();
    jQuery('#removeemail-form').hide();
}

function lrCloseAddEmailPopup() {
    jQuery('#addemail-form').hide();
}

function getBackupCodes() {
    var lrObjectInterval2 = setInterval(function () {
     if(typeof LRObject !== 'undefined')
       {
           clearInterval(lrObjectInterval2);
           LRObject.api.getBackupCode(accessToken,
            function (response) {
                jQuery('#backupcode-table-body').empty();
                for (var i = 0; i < response.BackUpCodes.length; i++) {
                    var html = '';
                    jQuery('#resettable').hide();
                    jQuery('#lr_ciam_reset_table').show();

                    html += '<div class="form-item code-list" id="backup-codes-' + i + '-field">';
                    html += '<span class="backupCode">' + response.BackUpCodes[i] + '</span>';
                    html += '</div>';

                    jQuery('#backupcode-table-body').append(html);

                }
                jQuery('.mybackupcopy').click(function () {
                    setClipboard();
                });
            }, function (errors) {
        jQuery('#resettable').show();
    });
  }
    }, 1);
}

function resetBackupCodes() {
    var lrObjectInterval1 = setInterval(function () {
                if(typeof LRObject !== 'undefined')
                {
                    clearInterval(lrObjectInterval1);
    LRObject.api.resetBackupCode(accessToken,
            function (response) {
                jQuery('#backupcode-table-body').empty();
                for (var i = 0; i < response.BackUpCodes.length; i++) {
                    var html = '';
                    jQuery('#resettable').hide();
                    jQuery('#lr_ciam_reset_table').show();

                    html += '<div class="form-item code-list" id="backup-codes-' + i + '-field">';
                    html += '<span class="backupCode">' + response.BackUpCodes[i] + '</span>';
                    html += '</div>';

                    jQuery('#backupcode-table-body').append(html);

                }
                jQuery('.mybackupcopy').click(function () {
                    setClipboard();
                });
            }, function (errors) {
                 handleResponse(false, errors[0].Description, "", "error");
    });
   }
    }, 1);
}

function setClipboard() {
    var value = '';
    jQuery('.code-list').find('span').each(function () {
        value += jQuery(this).html() + "\n";
    });
    var tempInput = document.createElement("textarea");
    tempInput.style = "position: absolute; left: -1000px; top: -1000px";
    tempInput.value = value;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
    jQuery('.copyMessage').show();
    setTimeout(removeCodeCss, 5000);
}

function removeCodeCss() {
    jQuery('.code-list').find('span').removeAttr('style');
    jQuery('.copyMessage').hide();
}

function changeIconColor() {
    jQuery('.code-list').find('span').css({'background-color': '#29d', 'color': '#fff'});
}

function lrCheckValidJson(element) {
    jQuery('#'+element).change(function () {
        var profile = jQuery('#'+element).val();
        var response = '';
        try
        {
            response = jQuery.parseJSON(profile);
            if (response != true && response != false) {
                var validjson = JSON.stringify(response, null, '\t').replace(/</g, '&lt;');
                if (validjson != 'null') {
                    jQuery('#'+element).val(validjson);
                    jQuery('#'+element).css("border", "1px solid green");
                } else {
                    jQuery('#'+element).css("border", "1px solid red");
                }
            } else {
                jQuery('#'+element).css("border", "1px solid green");
            }
        } catch (e)
        {
            jQuery('#'+element).css("border", "1px solid red");
        }
    });
}

function show_birthdate_date_block() {
    var maxYear = new Date().getFullYear();
    var minYear = maxYear - 100;
    if (jQuery('body').on) {
        jQuery('body').on('focus', '.loginradius-birthdate', function () {
            jQuery('.loginradius-birthdate').datepicker({
                dateFormat: 'mm-dd-yy',
                maxDate: new Date(),
                minDate: "-100y",
                changeMonth: true,
                changeYear: true,
                yearRange: (minYear + ":" + maxYear)
            });
        });
    } else {
        jQuery(".loginradius-birthdate").live("focus", function () {
            jQuery('.loginradius-birthdate').datepicker({
                dateFormat: 'mm-dd-yy',
                maxDate: new Date(),
                minDate: "-100y",
                changeMonth: true,
                changeYear: true,
                yearRange: (minYear + ":" + maxYear)
            });
        });
    }
}

function handleResponse(isSuccess, message, show, status) {
    status = status ? status : "status";
    if (typeof show != 'undefined' && !show) {
        jQuery('#fade').show();
    }
    if (isSuccess) {
        jQuery('form').each(function () {
            this.reset();
        });
    }
    if (message != null && message != "") {
        jQuery('#lr-loading').hide();
        jQuery('.messageinfo').text(message);
        jQuery(".messages").show();
        jQuery('.messageinfo').show();
        jQuery(".messages").removeClass("error status");
        jQuery(".messages").addClass(status);
        if(autoHideTime != "" && autoHideTime != "0"){
        setTimeout(fade_out, autoHideTime*1000);
        }

    } else {
        jQuery(".messages").hide();
        jQuery('.messageinfo').hide();
        jQuery('.messageinfo').text("");
    }
}
function fade_out() {
    jQuery(".messages").hide();
}
 var setButtonInterval = setInterval(function () {
                if(typeof LRObject !== 'undefined')
                {
                    clearInterval(setButtonInterval);
LRObject.$hooks.call('setButtonsName', {
    removeemail: "Remove"
});

LRObject.$hooks.register('startProcess', function () {
    jQuery('#lr-loading').show();
    if (commonOptions.phoneLogin) {
        jQuery('#lr-loading').hide();
    }
});

LRObject.$hooks.register('endProcess', function (name) {
    if (LRObject.options.twoFactorAuthentication === true || LRObject.options.optionalTwoFactorAuthentication === true)
            {
                jQuery('#authentication-container').show();
            }
            if (LRObject.options.phoneLogin === true)
            {
                jQuery('#updatephone-container').show();
                jQuery('#lr_phoneid').show();
            }
            if(name === 'resendOTP' && jQuery('#login-container').length > 0)
       {
           handleResponse(true, commonOptions.FORGOT_PASSWORD_PHONE_MSG);
       }
    jQuery('#lr-loading').hide();
}
);
LRObject.$hooks.register('afterFormRender', function (name) {
    if (name == "socialRegistration") {
        jQuery('#login-container').find('form[name=loginradius-socialRegistration]').parent().addClass('socialRegistration');
    }
    if(name == 'otp')
        {
            handleResponse(true, commonOptions.TWO_FA_MSG);
        }
        if(name == 'twofaotp')
        {
            handleResponse(true, commonOptions.TWO_FA_MSG);
        }
    if (name == "removeemail") {
        jQuery('#loginradius-removeemail-emailid').val(dropemailvalue);
    }
});

LRObject.$hooks.register('socialLoginFormRender', function () {
    //on social login form render
    jQuery('#lr-loading').hide();
    jQuery('#social-registration-form').show();
    show_birthdate_date_block();
});
}
 }, 1);
function callSocialInterface() {
    var custom_interface_option = {};
    custom_interface_option.templateName = 'loginradiuscustom_tmpl';
     var interfaceInterval = setInterval(function () {
                if(typeof LRObject !== 'undefined')
                {
                    clearInterval(interfaceInterval);
        LRObject.customInterface(".interfacecontainerdiv", custom_interface_option);
  }
     }, 1);
    jQuery('#lr-loading').hide();
}

function initializeLoginCiamForm() {
    //initialize Login form
    var login_options = {};
    login_options.onSuccess = function (response) {
        if (response.IsPosted == true && typeof response.access_token !== 'undefined') {
            if (jQuery('#loginradius-login-username').length !== 0) {
                handleResponse(true, commonOptions.LOGIN_BY_EMAIL_MSG);
            } else if (jQuery('#loginradius-login-emailid').length !== 0) {
                handleResponse(true, commonOptions.LOGIN_BY_EMAIL_MSG);
            }
       }
       else if( typeof response.Data !== 'undefined' && typeof response.Data.Sid !== 'undefined')
       { 
           handleResponse(true, commonOptions.LOGIN_BY_PHONE_MSG);           
       } else if( typeof response.Data !== 'undefined' && typeof response.access_token === 'undefined')
       {     
           handleResponse(true, commonOptions.EMAIL_VERIFICATION_SUCCESS_MSG);         
       }else if(response.IsPosted == true) {
           handleResponse(true, commonOptions.LOGIN_BY_EMAIL_MSG);     
       }else if (response.access_token) {
        handleResponse(true);
        ciamRedirect(response.access_token);
       }
    };
    login_options.onError = function (response) {
        handleResponse(false, response[0].Description, "", "error");
    };
    login_options.container = "login-container";
   var loginInterval = setInterval(function () {
    if(typeof LRObject != 'undefined')
     {
            clearInterval(loginInterval);
            LRObject.init("login", login_options);
   }
   }, 1);
    jQuery('#lr-loading').hide();
}

function initializeRegisterCiamForm() {
    var registration_options = {};
    registration_options.onSuccess = function (response) {
        var optionalemailverification = '';
        var disableemailverification = '';
        if (typeof LRObject.options.optionalEmailVerification != 'undefined') {
            optionalemailverification = LRObject.options.optionalEmailVerification;
        }
        if (typeof LRObject.options.disabledEmailVerification != 'undefined') {
            disableemailverification = LRObject.options.disabledEmailVerification;
        }                
        if (response.IsPosted && response.Data == null) {
            if ((typeof (optionalemailverification) == 'undefined' || optionalemailverification !== true) && (typeof (disableemailverification) == 'undefined' || disableemailverification !== true)) {
                handleResponse(true, commonOptions.REGISTRATION_SUCCESS_MSG);
                jQuery('html, body').animate({scrollTop: 0}, 1000);
            }
        }else if (response.access_token != null && response.access_token != "") {
            handleResponse(true, "");
            ciamRedirect(response.access_token);
        } else if(response.IsPosted && typeof response.Data !== 'undefined' && response.Data!==null && typeof response.Data.Sid !== 'undefined')
        {
            handleResponse(true, commonOptions.REGISTRATION_OTP_MSG);
        } else if(LRObject.options.otpEmailVerification==true && response.Data==null) {
            handleResponse(true, commonOptions.REGISTRATION_OTP_VERIFICATION_MSG);           
        } else {
            handleResponse(true, commonOptions.REGISTRATION_SUCCESS_MSG);
        }
    };
    registration_options.onError = function (response) {
        if (response[0].Description != null) {
            handleResponse(false, response[0].Description, "", "error");
        }
    };
    registration_options.container = "registration-container";
     var registrationInterval = setInterval(function () {
         if(typeof LRObject !== 'undefined')
         {
          clearInterval(registrationInterval);
           if(typeof registrationFormSchema !== 'undefined'){
            if(registrationFormSchema != ''){
              LRObject.registrationFormSchema  = registrationFormSchema;
            }
        } 
        LRObject.init("registration", registration_options);
}
     }, 1);
    jQuery('#lr-loading').hide();
}


function initializeSocialRegisterCiamForm() {
    var sl_options = {};
    sl_options.onSuccess = function (response) {
        if (response.IsPosted && typeof response.Data.AccountSid !== 'undefined') {
            handleResponse(true, "An OTP has been sent.");
        }
        else if (response.IsPosted == true && typeof response.Data.AccountSid === 'undefined') {
            handleResponse(true, commonOptions.SOCIAL_LOGIN_MSG);
            jQuery('#social-registration-form').hide();
            jQuery('#lr-loading').hide();
        } else if(response.access_token){
            handleResponse(true, "", true);
            ciamRedirect(response.access_token);
            jQuery('#lr-loading').hide();
        }
    };
    sl_options.onError = function (response) {
        if (response[0].Description != null) {
            handleResponse(false, response[0].Description, "", "error");
            jQuery('#social-registration-form').hide();
            jQuery('#lr-loading').hide();
        }
    };
    sl_options.container = "social-registration-container";
   var socialregistrationInterval = setInterval(function () {
     if(typeof LRObject !== 'undefined')
     {
      clearInterval(socialregistrationInterval);
        LRObject.init('socialLogin', sl_options);
}
   }, 1);
    jQuery('#lr-loading').hide();
}

function initializeForgotPasswordCiamForms() {
    //initialize forgot password form
    var form_name = "";
    var forgotpassword_options = {};
    forgotpassword_options.container = "forgotpassword-container";
    forgotpassword_options.onSuccess = function (response) {
        if(response.IsPosted == true && typeof response.Data !== 'undefined' && response.Data!==null)
        {
            handleResponse(true, commonOptions.FORGOT_PASSWORD_PHONE_MSG);   
        }else if(LRObject.options.otpEmailVerification==true && typeof response.Data==='undefined')
        {
            handleResponse(true, commonOptions.FORGOT_PHONE_OTP_VERIFICATION_MSG);  
        } else if (response.IsPosted == true && typeof (response.Data) === "object") {				
            if(jQuery('form[name="loginradius-resetpassword"]').length > 0) {
            handleResponse(true, commonOptions.FORGOT_PASSWORD_SUCCESS_MSG);  
            }           
        }else if (response.IsPosted == true && typeof (response.Data) === "undefined") {
            if(jQuery('form[name="loginradius-resetpassword"]').length > 0) {
            handleResponse(true, commonOptions.FORGOT_PASSWORD_SUCCESS_MSG);  
            } else {
            handleResponse(true, commonOptions.FORGOT_PASSWORD_MSG);   
            }
        }
        jQuery('input[type="text"]').val('');
        jQuery('input[type="password"]').val('');
        
    };
    forgotpassword_options.onError = function (response) {
        if (response[0].Description != null) {
            handleResponse(false, response[0].Description, "", "error");
        }
    }
    var forgotPasswordInterval = setInterval(function () {
     if(typeof LRObject !== 'undefined')
     {
      clearInterval(forgotPasswordInterval);
      LRObject.$hooks.register('startProcess', function (name) {
            if(name == 'resetPassword')
            {
                form_name = name;
            }
             jQuery('#lr-loading').show();
    });
        LRObject.init("forgotPassword", forgotpassword_options);
}
    }, 1);
    jQuery('#lr-loading').hide();
}

function initializeResetPasswordCiamForm(commonOptions) {
    //initialize reset password form and handel email verifaction
     var resetpasswordInterval = setInterval(function () {
         if(typeof LRObject !== 'undefined')
         {
          clearInterval(resetpasswordInterval);
    var vtype = LRObject.util.getQueryParameterByName("vtype");
    
    if (vtype != null && vtype != "") {
        if (vtype == "reset") {
            var resetpassword_options = {};
            resetpassword_options.container = "resetpassword-container";
            jQuery('#login-container').hide();
            jQuery('.interfacecontainerdiv').hide();
            resetpassword_options.onSuccess = function (response) {
                handleResponse(true, commonOptions.FORGOT_PASSWORD_SUCCESS_MSG);
                window.setTimeout(function () {
                    window.location.replace(commonOptions.verificationUrl);
                }, 5000);
            };
            resetpassword_options.onError = function (errors) {
                handleResponse(false, errors[0].Description, "", "error");
            }
            LRObject.init("resetPassword", resetpassword_options);
        } else if (vtype == "emailverification") {
            var verifyemail_options = {};
            verifyemail_options.onSuccess = function (response) {
                if (typeof response !== 'undefined') {
                    if (!loggedIn && typeof response.access_token != "undefined" && response.access_token != null && response.access_token != "") {
                        ciamRedirect(response.access_token);
                    } 
                    else if (!loggedIn && response.Data != null && response.Data.access_token != null && response.Data.access_token != "") {
                        ciamRedirect(response.Data.access_token);
                    } else {            
                        handleResponse(true, commonOptions.EMAIL_VERIFICATION_SUCCESS_MSG);
                        window.setTimeout(function () {
                            window.location.replace(commonOptions.verificationUrl);
                        }, 3000);
                    }
                }
            };
            verifyemail_options.onError = function (errors) {                    
                handleResponse(false, errors[0].Description, "", "error");                
            }
            LRObject.init("verifyEmail", verifyemail_options);
        } else if (vtype == "oneclicksignin") {
            var options = {};
            options.onSuccess = function (response) {
                ciamRedirect(response.access_token);
            };
            options.onError = function (errors) {
                if (!loggedIn) {
                    handleResponse(false, errors[0].Description, "", "error");
                }
            };
                LRObject.init("instantLinkLogin", options);
        }
    }
    }
     }, 1);
}

function initializeTwoFactorAuthenticator() {
    //initialize two factor authenticator button
    var authentication_options = {};
    authentication_options.container = "authentication-container";
    authentication_options.onSuccess = function (response) {
        if(response.Sid) {
            handleResponse(true, commonOptions.TWO_FA_MSG);
        }
        if (response.IsDeleted) {
                handleResponse(true, commonOptions.TWO_FA_DISABLED_MSG);
                window.setTimeout(function () {
                window.location.reload();
            }, 3000);
        } else if(typeof response.Uid != 'undefined') {
             handleResponse(true, commonOptions.TWO_FA_ENABLED_MSG);
             window.setTimeout(function () {
                window.location.reload();
            }, 3000);
        }        
    };
    authentication_options.onError = function (errors) {
        if (errors[0].Description != null) {
            handleResponse(false, errors[0].Description, "", "error");
            window.setTimeout(function () {
                window.location.reload();
            }, 1000);            
        }
    }
    var twofaInterval = setInterval(function () {
            if(typeof LRObject.options !== 'undefined' && LRObject.options != '')
            {
                clearInterval(twofaInterval);                    
                LRObject.init("createTwoFactorAuthentication", authentication_options);                    
            }
    }, 1);
}

function initializeProfileUpdate() {
    var profileeditor_options = {};
    profileeditor_options.container = "profileeditor-container";
    profileeditor_options.onSuccess = function(response) {
        handleResponse(true, commonOptions.UPDATE_USER_PROFILE, 'showmsg');   
        lrSetCookie('lr_profile_update', 'true');
        window.location.href = window.location.href;         
    };
    profileeditor_options.onError = function(errors) {
        if (errors[0].Description != null) {
            handleResponse(false, errors[0].Description, "", "error");    
        }
    };
    var lrUpdateInterval = setInterval(function () {
        if (typeof LRObject !== 'undefined')
        {
            clearInterval(lrUpdateInterval);
            LRObject.init("profileEditor", profileeditor_options);          
        }
    }, 1);    
}

function initializePhoneUpdate(phone_id) {
    var updatephone_options = {};
    updatephone_options.container = "updatephone-container";
    updatephone_options.onSuccess = function (response) {
        if(typeof response.Data !== 'undefined'){
            handleResponse(true, commonOptions.UPDATE_PHONE_MSG, 'showmsg');            
        }
        else if(response.IsPosted == true) {
            handleResponse(true, commonOptions.UPDATE_PHONE_SUCCESS_MSG, 'showmsg');  
            window.setTimeout(function () {
                window.location.reload();
            }, 1000);
        }       
    };
    updatephone_options.onError = function (errors) {
        if (errors[0].Description != null) {
            handleResponse(false, errors[0].Description, "", "error");
        }
    };
    var updatePhoneInterval = setInterval(function () {
                if(typeof LRObject.options !== 'undefined' && LRObject.options != '')
                {
                    clearInterval(updatePhoneInterval);
                     
                       LRObject.init("updatePhone", updatephone_options);
                       if(phone_id == '--')
                       {
                       jQuery('#updatephone-container #loginradius-submit-update').val('Add');
                   }
                       
                      
                   
                }
    }, 1);
}

function initializeAccountLinkingCiamForms() {
    var la_options = {};
    la_options.container = "interfacecontainerdiv";
    la_options.templateName = 'loginradiuscustom_tmpl_link';
    la_options.onSuccess = function (response) {
        if (response.IsPosted != true) {
            handleResponse(true);
            ciamRedirect(response);
        } else {
            lrSetCookie('lr_message', commonOptions.ACCOUNT_LINKING_MSG);
            window.location.href = window.location.href.split('?')[0] + '?lrresponse=true'; 
        }
    };
    la_options.onError = function (errors) {
        if (errors[0].ErrorCode != '1028' && errors[0].Description != null) {
            lrSetCookie('lr_message', errors[0].Description);
            window.location.href = window.location.href.split('?')[0] + '?lrresponse=false';
        }
    }

    var unlink_options = {};
    unlink_options.onSuccess = function (response) {
        if (response.IsDeleted == true) {
            lrSetCookie('lr_message', commonOptions.ACCOUNT_UNLINKING_MSG);
            window.location.href = window.location.href.split('?')[0] + '?lrresponse=true'; 
        }
    };
    unlink_options.onError = function (errors) {
        if (errors[0].Description != null) {
            lrSetCookie('lr_message', errors[0].Description);
            window.location.href = window.location.href.split('?')[0] + '?lrresponse=false';
        }
    }
     var linkInterval = setInterval(function () {
                if(typeof LRObject.options !== 'undefined' && LRObject.options != '')
                {
                    clearInterval(linkInterval);
                    if(LRObject.options.phoneLogin === true || LRObject.options.disabledEmailVerification !== true)
                    {
                    LRObject.init("linkAccount", la_options);
                    LRObject.init("unLinkAccount", unlink_options);
                    }
                }
    }, 1);
    jQuery('#lr-loading').hide();
}


function initializeAddEmailCiamForms() {
    var addemail_options = {};
    addemail_options.container = "addemail-container";
    addemail_options.onSuccess = function (response) {
        jQuery('#addemail-form').hide();
        handleResponse(true, commonOptions.ADD_EMAIL_MSG);
    };
    addemail_options.onError = function (errors) {
        jQuery('#addemail-form').hide();
        handleResponse(false, errors[0].Description, "", "error");
    };
    var emailInterval = setInterval(function () {
     if(typeof LRObject !== 'undefined')
     {
      clearInterval(emailInterval);
        LRObject.init("addEmail", addemail_options);
   }
    }, 1);
    jQuery('#lr-loading').hide();
}

function initializeRemoveEmailCiamForms(divhtml) {
    var removeemail_options = {};
    removeemail_options.container = "removeemail-container";
    removeemail_options.onSuccess = function (response) {
        jQuery('#removeemail-form').hide();
        handleResponse(true, commonOptions.REMOVE_EMAIL_MSG);
        divhtml.remove();
        window.setTimeout(function () {
            window.location.reload();
        }, 1000);
    };
    removeemail_options.onError = function (errors) {
        jQuery('#removeemail-form').hide();
        handleResponse(false, errors[0].Description, "", "error");
    };
    var removeemailInterval = setInterval(function () {
     if(typeof LRObject !== 'undefined')
     {
      clearInterval(removeemailInterval);
        LRObject.init("removeEmail", removeemail_options);
    }
    }, 1);
    jQuery('#lr-loading').hide();
}

function initializeChangePasswordCiamForms() {
    var changepassword_options = {};
    changepassword_options.container = "changepassword-container";
    changepassword_options.onSuccess = function (response) {
        handleResponse(true, commonOptions.CHANGE_PASSWORD_SUCCESS_MSG);
    };
    changepassword_options.onError = function (errors) {
        handleResponse(false, errors[0].Description, "", "error");
    };
    var changepasswordInterval = setInterval(function () {
     if(typeof LRObject !== 'undefined')
     {
      clearInterval(changepasswordInterval);
        LRObject.init("changePassword", changepassword_options);
  }
    }, 1);
    jQuery('#lr-loading').hide();
}

function ciamRedirect(token, name) {
    if (window.redirect) {
        redirect(token, name);
    } else {
        var token_name = name ? name : 'token';
        var source = typeof lr_source != 'undefined' && lr_source ? lr_source : '';

        var form = document.createElement('form');

        form.action = LocalDomain;
        form.method = 'POST';

        var hiddenToken = document.createElement('input');
        hiddenToken.type = 'hidden';
        hiddenToken.value = token;
        hiddenToken.name = token_name;
        form.appendChild(hiddenToken);

        document.body.appendChild(form);
        form.submit();
    }
}

function lrSetCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
