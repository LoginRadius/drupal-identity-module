jQuery(document).ready(function () {

    jQuery("#lr-loading").click(function () {
        jQuery('#lr-loading').hide();
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
  
    showOrHideCustomRedirection(jQuery('input:radio[name="login_redirection"]:checked').val());
    jQuery('input:radio[name="login_redirection"]').change(function () {
        showOrHideCustomRedirection(jQuery(this).val());
    });

    showOrHidePasswordlessTemplate(jQuery('input:radio[name="ciam_instant_link_login"]:checked').val());
    jQuery('input:radio[name="ciam_instant_link_login"]').change(function () {
        showOrHidePasswordlessTemplate(jQuery(this).val());
    });

    showOrHidePasswordlessOTPTemplate(jQuery('input:radio[name="ciam_instant_otp_login"]:checked').val());
    jQuery('input:radio[name="ciam_instant_otp_login"]').change(function () {
        showOrHidePasswordlessOTPTemplate(jQuery(this).val());
    });

    if (window.location.href == window.location.origin + domainName + 'admin/people/create') {
        jQuery('.form-item-mail label').attr('class', 'js-form-required form-required');
        jQuery('#edit-mail').attr('required', 'required');
    } else {
        jQuery('#edit-mail').attr('disabled', 'disabled');
        jQuery('#edit-mail').attr('style', 'background:#ededed');
    }

    dropemailvalue = '';
    jQuery('.removeEmail').each(function () {
        jQuery(this).click(function () {
            jQuery('form[name="loginradius-removeemail"]').remove();
            var html = jQuery(this).parents('tr');
            dropemailvalue = jQuery(this).parents('tr').find('.form-email').val();
            showRemoveEmailPopup(html);
        });
    });   
    
    jQuery('#addEmail').attr('onClick', 'showAddEmailPopup()');    
});

function showOrHidePasswordlessTemplate(option) {
    if ('false' === option || '' === option) {
        jQuery('.form-item-ciam-instant-link-login-email-template').hide();
    } else {
        jQuery('.form-item-ciam-instant-link-login-email-template').show();
    }
}

function showOrHidePasswordlessOTPTemplate(option) {
    if ('false' === option || '' === option) {
        jQuery('.form-item-ciam-sms-template-one-time-passcode').hide();
    } else {
        jQuery('.form-item-ciam-sms-template-one-time-passcode').show();
    }
}

function showOrHideCustomRedirection(option) {
    if ('0' === option || '1' === option || '' === option) {
        jQuery('.form-item-custom-login-url').hide();
    } else {
        jQuery('.form-item-custom-login-url').show();
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
    status = status ? 'messages--' + status : "messages--status";
    if (isSuccess) {
        jQuery('form').each(function () {
            this.reset();
        });
    }
    if (message != null && message != "") {
        jQuery('#lr-loading').hide();
        jQuery('.messages').text(message);
        jQuery(".messages__wrapper").show();
        jQuery(".messages").show();    

        jQuery(".messages").removeClass("messages--error messages--status showmsg");
        jQuery(".messages").addClass(status);
        jQuery(".messages").addClass(show);
        if (autoHideTime != "" && autoHideTime != "0") {
            setTimeout(fade_out, autoHideTime * 1000);
        }
    } else {
        jQuery(".messages__wrapper").hide();
        jQuery('.messages').hide();
        jQuery('.messages').text("");
    }
}

function showErrorMsgForOneClickSignIn(isSuccess, message) {
    if (isSuccess) {
        jQuery('form').each(function () {
            this.reset();
        });
    }
    if (message != null && message != "") {
        jQuery('#lr-loading').hide();
        jQuery('div[data-drupal-messages-fallback]').text(message);
        jQuery("div[data-drupal-messages-fallback]").show(); 
        jQuery("div[data-drupal-messages-fallback]").removeClass("oneclick--errormsg");
        jQuery("div[data-drupal-messages-fallback]").addClass("oneclick--errormsg");
        if (autoHideTime != "" && autoHideTime != "0") {
            setTimeout(fade_out, autoHideTime * 1000);
        }
    } else {
        jQuery(".div[data-drupal-messages-fallback]").hide();   
        jQuery('.div[data-drupal-messages-fallback]').text("");
    }
}

function fade_out() {
    jQuery(".messages").hide();
}

var setButtonInterval = setInterval(function () {
    if (typeof LRObject !== 'undefined')
    {
        clearInterval(setButtonInterval);
        LRObject.$hooks.register('startProcess', function () {
            jQuery('#lr-loading').show();
        });

        LRObject.$hooks.register('endProcess', function () {
            if (LRObject.options.twoFactorAuthentication === true || LRObject.options.optionalTwoFactorAuthentication === true)
            {
                jQuery('#authentication-container').show();
            }
            jQuery('#edit-account-phone').hide();
            if (LRObject.options.phoneLogin === true)
            {
                jQuery('#updatephone-container').show();
                jQuery('#edit-account-phone').show();
            }
            jQuery('#lr-loading').hide();
        });

        LRObject.$hooks.call('setButtonsName', {
            removeemail: "Remove"
        });
       
        LRObject.registrationFormSchema  = registrationSchema;

        LRObject.$hooks.register('socialLoginFormRender', function () {
            //on social login form render
            jQuery('#lr-loading').hide();
            jQuery('#social-registration-form').show();
            show_birthdate_date_block();
        });

        LRObject.$hooks.register('afterFormRender', function (name) {
            if (name == "socialRegistration") {
                jQuery('#login-container').find('form[name=loginradius-socialRegistration]').parent().addClass('socialRegistration');
            }       
            if (name == "updatePhone") {
                if(phoneId == "") {
                     jQuery('#updatephone-container').find('#loginradius-submit-update').attr('value', 'Add');
                }
            }
            if (name == "removeemail") {
                jQuery('#loginradius-removeemail-emailid').val(dropemailvalue);
            }
        });
    }
}, 1);

function getBackupCodes() {
    var lrGetBackupInterval = setInterval(function () {
        if (typeof LRObject !== 'undefined')
        {
            clearInterval(lrGetBackupInterval);
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
                            setClipboard(jQuery(this).parent('.form-item').find('span').text());
                        });
                    }, function (errors) {
                jQuery('#resettable').show();
            });
        }
    }, 1);
}

function resetBackupCodes() {
    var lrResetBackupInterval = setInterval(function () {
        if (typeof LRObject !== 'undefined')
        {
            clearInterval(lrResetBackupInterval);
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
                            setClipboard(jQuery(this).parent('.form-item').find('span').text());
                        });
                    }, function (errors) {
            });
        }
    }, 1);
}

function callSocialInterface() {
    var custom_interface_option = {};
    custom_interface_option.templateName = 'loginradiuscustom_tmpl';
    var lrSocialInterval = setInterval(function () {
        if (typeof LRObject !== 'undefined')
        {
            clearInterval(lrSocialInterval);
            LRObject.customInterface(".interfacecontainerdiv", custom_interface_option);
        }
    }, 1);
    jQuery('#lr-loading').hide();
}

function initializeSocialRegisterCiamForm() {
    var sl_options = {};
    sl_options.onSuccess = function (response) {
        if (response.access_token != null && response.access_token != "") {
            handleResponse(true, "");
            ciamRedirect(response.access_token);
            jQuery('#lr-loading').hide();
        } else if (response.IsPosted) {
            handleResponse(true, commonOptions.messagesList.SOCIAL_LOGIN_MSG);
            jQuery('#social-registration-form').hide();
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
    var lrSocialLoginInterval = setInterval(function () {
        if (typeof LRObject !== 'undefined')
        {
            clearInterval(lrSocialLoginInterval);
            LRObject.init('socialLogin', sl_options);
        }
    }, 1);

}

function initializeLoginCiamForm() {
    //initialize Login form
    var login_options = {};
    login_options.onSuccess = function (response) {
        if (response.IsPosted == true && typeof response.access_token !== 'undefined') {
            if (jQuery('#loginradius-login-username').length !== 0 || jQuery('#loginradius-login-emailid').length !== 0) {
                handleResponse(true, commonOptions.messagesList.LOGIN_BY_EMAIL_MSG);
            }
       }
       else if( typeof response.Data !== 'undefined' && typeof response.Data.Sid !== 'undefined')
       { 
           handleResponse(true, commonOptions.messagesList.OTP_SEND_ON_PHONE_SUCCESS_MSG);           
       } 
       else if( typeof response.Data !== 'undefined')
       {     
           handleResponse(true, commonOptions.messagesList.EMAIL_VERIFICATION_SUCCESS_MSG);         
       }else if(response.IsPosted == true) {
           handleResponse(true, commonOptions.messagesList.LOGIN_BY_EMAIL_MSG);     
       }else if (response.access_token) {
           handleResponse(true);
           ciamRedirect(response.access_token);
       }
    };
    login_options.onError = function (response) {
        handleResponse(false, response[0].Description, "", "error");
    };
    login_options.container = "login-container";

    var lrLoginInterval = setInterval(function () {
        if (typeof LRObject !== 'undefined')
        {
            clearInterval(lrLoginInterval);
            LRObject.init("login", login_options);
        }
    }, 1);
    jQuery('#lr-loading').hide();
}

function initializeRegisterCiamForm() {
    var registration_options = {}
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
                handleResponse(true, commonOptions.messagesList.REGISTRATION_SUCCESS_MSG);
                jQuery('html, body').animate({scrollTop: 0}, 1000);
            }
        }else if (response.access_token != null && response.access_token != "") {
            handleResponse(true, "");
            ciamRedirect(response.access_token);
        } else if(response.IsPosted && typeof response.Data !== 'undefined' && response.Data!==null && typeof response.Data.Sid !== 'undefined')
        {
            handleResponse(true, commonOptions.messagesList.OTP_SEND_ON_PHONE_SUCCESS_MSG);
        } else if(LRObject.options.otpEmailVerification==true && response.Data==null) {
            handleResponse(true, commonOptions.messagesList.VERIFICATION_OTP_SEND_ON_EMAIL_MSG);           
        } else {
            handleResponse(true, commonOptions.messagesList.REGISTRATION_SUCCESS_MSG);
        }
    };
    registration_options.onError = function (response) {
        if (response[0].Description != null) {
            handleResponse(false, response[0].Description, "", "error");  
        }else if (response[0] != null) {
            handleResponse(false, response[0], "", "error");      
        }
        jQuery('html, body').animate({scrollTop: 0}, 1000);
    };
    registration_options.container = "registration-container";
    var lrRegisterInterval = setInterval(function () {
        if (typeof LRObject !== 'undefined')
        {
            clearInterval(lrRegisterInterval);
            LRObject.init("registration", registration_options);
        }
    }, 1);

    jQuery('#lr-loading').hide();
}

function initializeResetPasswordCiamForm(commonOptions) {
    //initialize reset password form and handel email verifaction
    var resetpasswordInterval = setInterval(function () {
        if (typeof LRObject !== 'undefined')
        {
            clearInterval(resetpasswordInterval);
            var vtype = LRObject.util.getQueryParameterByName("vtype");
            if (vtype != null && vtype != "") {
                if (vtype == "reset") {
                    var resetpassword_options = {};
                    resetpassword_options.container = "resetpassword-container";
                    jQuery('#login-container').hide();
                    jQuery('.interfacecontainerdiv').hide();
                    jQuery('.page-title').text('Reset Password');
                    jQuery('#interfaceLabel').hide();
                    resetpassword_options.onSuccess = function (response) {
                        handleResponse(true, commonOptions.messagesList.FORGOT_PASSWORD_SUCCESS_MSG);
                        window.setTimeout(function () {
                           window.location.replace(commonOptions.verificationUrl);
                        }, 3000);
                    };
                    resetpassword_options.onError = function (errors) {
                        handleResponse(false, errors[0].Description, "", "error");
                    }

                    LRObject.init("resetPassword", resetpassword_options);

                } else if (vtype == "emailverification") {
                    var verifyemail_options = {};
                    verifyemail_options.onSuccess = function (response) {
                        if (typeof response != 'undefined') {
                            if (!loggedIn && typeof response.access_token != "undefined" && response.access_token != null && response.access_token != "") {
                                ciamRedirect(response.access_token);
                            } else if (!loggedIn && response.Data != null && response.Data.access_token != null && response.Data.access_token != "") {
                                ciamRedirect(response.Data.access_token);
                            } else {
                                lrSetCookie('lr_message', commonOptions.messagesList.EMAIL_VERIFICATION_SUCCESS_MSG);
                                window.location.href = window.location.href.split('?')[0] + '?lrresponse=true';
                            }
                        }
                    };
                    verifyemail_options.onError = function (errors) {
                        lrSetCookie('lr_message', errors[0].Description);
                        window.location.href = window.location.href.split('?')[0] + '?lrresponse=false';
                    }

                    LRObject.init("verifyEmail", verifyemail_options);


                } else if (vtype == "oneclicksignin") {
                    var options = {};
                    options.onSuccess = function (response) {
                        ciamRedirect(response.access_token);
                    };
                    options.onError = function (errors) {
                        if (!loggedIn){
                            showErrorMsgForOneClickSignIn(false, errors[0].Description);
                        }else{
                            window.location.href = homeDomain;
                        }
                    };

                    LRObject.init("instantLinkLogin", options);
                }
            }
        }
    }, 1);
    jQuery('#lr-loading').hide();
}

function initializeForgotPasswordCiamForms() {
    //initialize forgot password form
    var forgotpassword_options = {};
    forgotpassword_options.container = "forgotpassword-container";
    forgotpassword_options.onSuccess = function (response) {
            if(response.IsPosted == true && typeof response.Data !== 'undefined' && response.Data!==null)
            {
                handleResponse(true, commonOptions.messagesList.OTP_SEND_ON_PHONE_SUCCESS_MSG);   
            }else if(LRObject.options.otpEmailVerification==true && typeof response.Data==='undefined')
            {
                handleResponse(true, commonOptions.messagesList.VERIFICATION_OTP_SEND_ON_EMAIL_MSG);  
            } else if (response.IsPosted == true && typeof (response.Data) === "object") {				
                if(jQuery('form[name="loginradius-resetpassword"]').length > 0) {
                handleResponse(true, commonOptions.messagesList.FORGOT_PASSWORD_SUCCESS_MSG);  
                window.setTimeout(function () {
                    window.location.replace(commonOptions.verificationUrl);
                 }, 3000);
                }           
			}else if (response.IsPosted == true && typeof (response.Data) === "undefined") {
                if(jQuery('form[name="loginradius-resetpassword"]').length > 0) {
                handleResponse(true, commonOptions.messagesList.FORGOT_PASSWORD_SUCCESS_MSG);  
                window.setTimeout(function () {
                    window.location.replace(commonOptions.verificationUrl);
                 }, 3000);
                } else {
                handleResponse(true, commonOptions.messagesList.FORGOT_PASSWORD_MSG);   
                }
            }
    };
    forgotpassword_options.onError = function (response) {
        if (response[0].Description != null) {
            handleResponse(false, response[0].Description, "", "error");
        }
    }

    var lrForgotInterval = setInterval(function () {
        if (typeof LRObject !== 'undefined')
        {
            clearInterval(lrForgotInterval);
            LRObject.init("forgotPassword", forgotpassword_options);
        }
    }, 1);

    jQuery('#lr-loading').hide();
}

function initializeAccountLinkingCiamForms() {
    var la_options = {};
    la_options.container = "interfacecontainerdiv";
    la_options.templateName = 'loginradiuscustom_tmpl_link';
    la_options.onSuccess = function (response) {
        if (response.IsPosted != true) {
            handleResponse(true, "");
            ciamRedirect(response);
        } else {
            handleResponse(true, commonOptions.messagesList.ACCOUNT_LINKING_MSG, "showmsg");
            window.setTimeout(function () {
                window.location.reload();
            }, 3000);
        }
    };
    la_options.onError = function (errors) {
        handleResponse(false, errors[0].Description, "showmsg", "error");
    }

    var unlink_options = {};
    unlink_options.onSuccess = function (response) {
        if (response.IsDeleted == true) {
            handleResponse(true, commonOptions.messagesList.ACCOUNT_UNLINKING_MSG, "showmsg");
            window.setTimeout(function () {
                window.location.reload();
            }, 3000);
        }
    };
    unlink_options.onError = function (errors) {
        handleResponse(false, errors[0].Description, "showmsg", "error");
    }

    
    var lrLinkingInterval = setInterval(function () {
        var localaccesstoken = LRObject.storage.getBrowserStorage('LRTokenKey');      
        if (typeof LRObject !== 'undefined' && localaccesstoken)
        {
            clearInterval(lrLinkingInterval);
            LRObject.init("linkAccount", la_options);
            LRObject.init("unLinkAccount", unlink_options);
        }
    }, 1);
    jQuery('#lr-loading').hide();
}

function initializeTwoFactorAuthenticator() {
    //initialize two factor authenticator button
    var authentication_options = {};
    authentication_options.container = "authentication-container";
    authentication_options.onSuccess = function (response) {
        if(response.Sid){          
            handleResponse(true, commonOptions.messagesList.OTP_SEND_ON_PHONE_SUCCESS_MSG);
        } if (response.IsDeleted == true) {
            handleResponse(true, commonOptions.messagesList.TWO_FA_DISABLED_MSG, "showmsg");  
            jQuery('html, body').animate({scrollTop: 0}, 1000);
            window.setTimeout(function () {
                window.location.reload();
            }, 3000);
        } else if(typeof response.Uid != 'undefined'){
            handleResponse(true, commonOptions.messagesList.TWO_FA_ENABLED_MSG, "showmsg"); 
            jQuery('html, body').animate({scrollTop: 0}, 1000);
             window.setTimeout(function () {
                window.location.reload();
            }, 3000);
        }
    };
    authentication_options.onError = function (errors) {
        if (errors[0].Description != null) {
              handleResponse(false, errors[0].Description, "showmsg", "error");     
        }
    }
    var lrTwoFAInterval = setInterval(function () {
        var localaccesstoken = LRObject.storage.getBrowserStorage('LRTokenKey');      
        if (typeof LRObject !== 'undefined' && localaccesstoken)
        {
            clearInterval(lrTwoFAInterval);
            LRObject.init("createTwoFactorAuthentication", authentication_options);

        }
    }, 1);
}

function initializeProfileUpdate() {
    var profileeditor_options = {};
    profileeditor_options.container = "profileeditor-container";
    profileeditor_options.onSuccess = function(response) {
        handleResponse(true, commonOptions.messagesList.UPDATE_USER_PROFILE, 'showmsg');   
        lrSetCookie('lr_profile_update', 'true');
        window.location.href = window.location.href;
         
    };
    profileeditor_options.onError = function(errors) {
        if (errors[0].Description != null) {
            handleResponse(false, errors[0].Description, "showmsg", "error");       
        }
    };
    var lrUpdateInterval = setInterval(function () {
        var localaccesstoken = LRObject.storage.getBrowserStorage('LRTokenKey');      
        if (typeof LRObject !== 'undefined' && localaccesstoken)
        {
            clearInterval(lrUpdateInterval);
            LRObject.init("profileEditor",profileeditor_options);          
        }
    }, 1);    
}

function initializePhoneUpdate() {
    var updatephone_options = {};
    updatephone_options.container = "updatephone-container";
    updatephone_options.onSuccess = function (response) {
        if(typeof response.Data !== 'undefined'){
            handleResponse(true, commonOptions.messagesList.OTP_SEND_ON_PHONE_SUCCESS_MSG, 'showmsg');            
        }
        else if(response.IsPosted == true) {
            handleResponse(true, commonOptions.messagesList.UPDATE_PHONE_SUCCESS_MSG, 'showmsg');  
            window.setTimeout(function () {
                window.location.reload();
            }, 3000);
        }
    };
    updatephone_options.onError = function (errors) {
        if (errors[0].Description != null) {
            handleResponse(false, errors[0].Description, "showmsg", "error");       
        }
    };
    var lrUpdateInterval = setInterval(function () {
        var localaccesstoken = LRObject.storage.getBrowserStorage('LRTokenKey');      
        if (typeof LRObject !== 'undefined' && localaccesstoken)
        {
            clearInterval(lrUpdateInterval);
            LRObject.init("updatePhone", updatephone_options);            
        }
    }, 1);    
}

function initializeAddEmailCiamForms() {
    var addemail_options = {};
    addemail_options.container = "addemail-container";
    addemail_options.onSuccess = function (response) {
        jQuery('#addemail-form').hide();
        handleResponse(true, commonOptions.messagesList.ADD_EMAIL_MSG, 'showmsg');
        jQuery('html, body').animate({scrollTop: 0}, 1000);
    };
    addemail_options.onError = function (errors) {
        jQuery('#addemail-form').hide();
        handleResponse(false, errors[0].Description, "showmsg", "error");
        jQuery('html, body').animate({scrollTop: 0}, 1000);
    };

    var lrAddInterval = setInterval(function () {
        var localaccesstoken = LRObject.storage.getBrowserStorage('LRTokenKey');      
        if (typeof LRObject !== 'undefined' && localaccesstoken)
        {
            clearInterval(lrAddInterval);
            LRObject.init("addEmail", addemail_options);
            jQuery('#lr-loading').hide();
        }
    }, 1);
}

function initializeRemoveEmailCiamForms(divhtml) {
    var removeemail_options = {};
    removeemail_options.container = "removeemail-container";
    removeemail_options.onSuccess = function (response) {
        jQuery('#removeemail-form').hide();
        handleResponse(true, commonOptions.messagesList.REMOVE_EMAIL_MSG, 'showmsg');
        divhtml.remove();
        jQuery('html, body').animate({scrollTop: 0}, 1000);
    };
    removeemail_options.onError = function (errors) {
        jQuery('#removeemail-form').hide();
        handleResponse(false, errors[0].Description, "showmsg", "error");
        jQuery('html, body').animate({scrollTop: 0}, 1000);

    };
    var lrRemoveInterval = setInterval(function () {             
        var localaccesstoken = LRObject.storage.getBrowserStorage('LRTokenKey');      
        if (typeof LRObject !== 'undefined' && localaccesstoken)
        {
            clearInterval(lrRemoveInterval);
            LRObject.init("removeEmail", removeemail_options);
            jQuery('#lr-loading').hide();
        }
    }, 1);
}

function initializeChangePasswordCiamForms() {
    var changepassword_options = {};
    changepassword_options.container = "changepassword-container";
    changepassword_options.onSuccess = function (response) {
        handleResponse(true, commonOptions.messagesList.CHANGE_PASSWORD_SUCCESS_MSG);
    };
    changepassword_options.onError = function (errors) {
        handleResponse(false, errors[0].Description, "", "error");
    };

    var lrChangeInterval = setInterval(function () {
        var localaccesstoken = LRObject.storage.getBrowserStorage('LRTokenKey');      
        if (typeof LRObject !== 'undefined' && localaccesstoken)
        {
            clearInterval(lrChangeInterval);
            LRObject.init("changePassword", changepassword_options);
            jQuery('#lr-loading').hide();
        }
    }, 1);
}

function ciamRedirect(token, name) {
    if (window.redirect) {
        redirect(token, name);
    } else {
        var token_name = name ? name : 'token';   

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

function lrSetCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
