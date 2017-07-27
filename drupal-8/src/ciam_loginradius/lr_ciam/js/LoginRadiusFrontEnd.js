jQuery(document).ready(function () {

    jQuery("#lr-loading").click(function () {
        jQuery('#lr-loading').hide();
    });

    var url = domainName + 'admin/people/create';
    jQuery("a[href= '" + url + "']").attr('href', 'https://secure.loginradius.com/user-management/manage-users');
    jQuery('.action-links a').attr('target', '_blank');

    if (window.location.href == window.location.origin + domainName + 'admin/people') {
        jQuery('.dropbutton a').attr('href', 'https://secure.loginradius.com/user-management/manage-users');
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
    showAndHideUI();
});
var LRObject = new LoginRadiusV2(ciamoption);

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

function showAndHideUI() {
    var options = jQuery('input[name=ciam_email_verification_condition]:checked').val();
    var enableLogin = jQuery('fieldset[data-drupal-selector=edit-ciam-enable-login-on-email-verification]');
    var enableUserName = jQuery('fieldset[data-drupal-selector=edit-ciam-enable-user-name]');
    var askEmail = jQuery('fieldset[data-drupal-selector=edit-ciam-ask-email-always-for-unverified]');
    var promptPassword = jQuery('fieldset[data-drupal-selector=prompt-password]');

    if (options == 2) {
        enableLogin.hide();
        promptPassword.hide();
        enableUserName.hide();
        askEmail.hide();
    } else if (options == 1) {
        enableLogin.show();
        promptPassword.hide();
        enableUserName.hide();
        askEmail.show();
    } else {
        enableLogin.show();
        promptPassword.show();
        askEmail.show();
        enableUserName.show();
    }
}

function lrCheckValidJson() {
    jQuery('#add_custom_options').change(function () {
        var profile = jQuery('#add_custom_options').val();
        var response = '';
        try
        {
            response = jQuery.parseJSON(profile);
            if (response != true && response != false) {
                var validjson = JSON.stringify(response, null, '\t').replace(/</g, '&lt;');
                if (validjson != 'null') {
                    jQuery('#add_custom_options').val(validjson);
                    jQuery('#add_custom_options').css("border", "1px solid green");
                } else {
                    jQuery('#add_custom_options').css("border", "1px solid red");
                }
            } else {
                jQuery('#add_custom_options').css("border", "1px solid green");
            }
        } catch (e)
        {
            jQuery('#add_custom_options').css("border", "1px solid green");
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

        jQuery(".messages").removeClass("messages--error messages--status");
        jQuery(".messages").addClass(status);

    } else {
        jQuery(".messages__wrapper").hide();
        jQuery('.messages').hide();
        jQuery('.messages').text("");
    }
}

LRObject.$hooks.register('startProcess', function () {
    jQuery('#lr-loading').show();
}
);

LRObject.$hooks.register('endProcess', function () {
    if (ciamoption.formRenderDelay) {
        setTimeout(function () {
            jQuery('#lr-loading').hide();
        }, ciamoption.formRenderDelay - 1);
    }
    jQuery('#lr-loading').hide();
}
);

LRObject.$hooks.register('socialLoginFormRender', function () {
    //on social login form render
    jQuery('#lr-loading').hide();
    jQuery('#social-registration-form').show();
    show_birthdate_date_block();
});

LRObject.$hooks.call(
        'passwordMeterConfiguration', [{
                case: "worst",
                message: "6 length is required", //Your minimum password length message.
                color: "Red"
            }, {
                case: "bad",
                message: "Bad",
                color: "Red"
            }, {
                case: "weak",
                message: "weak",
                color: "yellow"
            }, {
                case: "good",
                message: "Good",
                color: "Green"
            }, {
                case: "strong",
                message: "Strong",
                color: "Green"
            }, {
                case: "secure",
                message: "Secure",
                color: "Blue"
            }]);

function callSocialInterface() {
    var custom_interface_option = {};
    custom_interface_option.templateName = 'loginradiuscustom_tmpl';
    LRObject.util.ready(function () {
        LRObject.customInterface(".interfacecontainerdiv", custom_interface_option);
    });
    jQuery('#lr-loading').hide();
}

function initializeLoginCiamForm() {
    //initialize Login form
    var login_options = {};
    login_options.onSuccess = function (response) {
        handleResponse(true, "");
        ciamRedirect(response.access_token);
    };
    login_options.onError = function (response) {
        handleResponse(false, response[0].Description, "", "error");
    };
    login_options.container = "login-container";

    LRObject.util.ready(function () {
        LRObject.init("login", login_options);
    });
    jQuery('#lr-loading').hide();
}

function initializeRegisterCiamForm() {
    var registration_options = {}
    registration_options.onSuccess = function (response) {
        if (response.access_token != null && response.access_token != "") {
            handleResponse(true, "");
            ciamRedirect(response.access_token);
        } else {
            handleResponse(true, "An email has been sent to " + jQuery("#loginradius-registration-emailid").val() + ".Please verify your email address");
            window.setTimeout(function () {
                window.location.replace(homeDomain);
            }, 7000);
        }
    };
    registration_options.onError = function (response) {
        if (response[0].Description != null) {
            handleResponse(false, response[0].Description, "", "error");
        }
    };
    registration_options.container = "registration-container";
    LRObject.util.ready(function () {
        LRObject.init("registration", registration_options);
    });
    jQuery('#lr-loading').hide();
}

function initializeResetPasswordCiamForm(ciamoption) {
    //initialize reset password form and handel email verifaction
    var vtype = LRObject.util.getQueryParameterByName("vtype");
    if (vtype != null && vtype != "") {
        if (vtype == "reset") {
            var resetpassword_options = {};
            resetpassword_options.container = "resetpassword-container";
            jQuery('#login-container').hide();
            jQuery('.interfacecontainerdiv').hide();
            jQuery('#interfaceLabel').hide();
            resetpassword_options.onSuccess = function (response) {
                handleResponse(true, "Password reset successfully");
                window.setTimeout(function () {
                    window.location.replace(ciamoption.verificationUrl);
                }, 5000);
            };
            resetpassword_options.onError = function (errors) {
                handleResponse(false, errors[0].Description, "", "error");
            }
            LRObject.util.ready(function () {
                LRObject.init("resetPassword", resetpassword_options);
            });
        } else if (vtype == "emailverification") {
            var verifyemail_options = {};
            verifyemail_options.onSuccess = function (response) {
                if (response != undefined) {
                    if (typeof response.access_token != "undefined" && response.access_token != null && response.access_token != "") {
                        if (ciamoption.loginOnEmailVerification) {
                            ciamRedirect(response.access_token);
                        } else {
                            jQuery('.region.region-highlighted').html(getMessage('status', 'Your email has been verified successfully'));
                        }
                    } else if (response.Data != null && response.Data.access_token != null && response.Data.access_token != "") {
                        if (ciamoption.loginOnEmailVerification) {
                            ciamRedirect(response.Data.access_token);
                        } else {
                            jQuery('.region.region-highlighted').html(getMessage('status', 'Your email has been verified successfully'));
                        }
                    } else {
                        jQuery('.region.region-highlighted').html(getMessage('status', 'Your email has been verified successfully'));
                    }
                }
            };
            verifyemail_options.onError = function (errors) {
                jQuery('.region.region-highlighted').html(getMessage('error', errors[0].Description));              
            }

            LRObject.util.ready(function () {
                LRObject.init("verifyEmail", verifyemail_options);
            });
        }
    }
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
            handleResponse(true, "An email has been sent to " + jQuery("#loginradius-socialRegistration-emailid").val() + ".Please verify your email address.");
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

    LRObject.util.ready(function () {
        LRObject.init('socialLogin', sl_options);
        jQuery('#lr-loading').show();
    });
    jQuery('#lr-loading').hide();
}

function initializeForgotPasswordCiamForms() {
    //initialize forgot password form
    var forgotpassword_options = {};
    forgotpassword_options.container = "forgotpassword-container";
    forgotpassword_options.onSuccess = function (response) {
        handleResponse(true, "An email has been sent to " + jQuery("#loginradius-forgotpassword-emailid").val() + " with reset Password link");
        window.setTimeout(function () {
            window.location.replace(homeDomain);
        }, 7000);
    };
    forgotpassword_options.onError = function (response) {
        if (response[0].Description != null) {
            handleResponse(false, response[0].Description, "", "error");
        }
    }
    LRObject.util.ready(function () {
        LRObject.init("forgotPassword", forgotpassword_options);
    });
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
            jQuery('.region.region-highlighted').html(getMessage('status', 'Account linked successfully'));
            window.setTimeout(function () {
                window.location.reload();
            }, 3000);

        }
    };
    la_options.onError = function (errors) {
        if (errors[0].Description != null) {
            jQuery('.region.region-highlighted').html(getMessage('error', errors[0].Description));
        }
    }

    var unlink_options = {};
    unlink_options.onSuccess = function (response) {
        if (response.IsDeleted == true) {
            jQuery('.region.region-highlighted').html(getMessage('status', 'Account unlinked successfully'));
            window.setTimeout(function () {
                window.location.reload();
            }, 3000);
        }
    };
    unlink_options.onError = function (errors) {
        if (errors[0].Description != null) {
            jQuery('.region.region-highlighted').html(getMessage('error', errors[0].Description));
        }
    }

    LRObject.util.ready(function () {
        LRObject.init("linkAccount", la_options);
        LRObject.init("unLinkAccount", unlink_options);
    });
    jQuery('#lr-loading').hide();
}

function getMessage(status, msg) {
   return '<div class="messages__wrapper layout-container"><div role="contentinfo" aria-label="Status message" class="messages messages--' + status + '"><h2 class="visually-hidden">Status message</h2>' + msg + '</div></div>';
}

function initializeAddEmailCiamForms() {
    var addemail_options = {};
    addemail_options.container = "addemail-container";
    addemail_options.onSuccess = function (response) {
        jQuery('#addemail-form').hide(); 
        jQuery('.region.region-highlighted').html(getMessage('status', 'Email added successfully, Please verify your email address.'));
         
    };
    addemail_options.onError = function (response) {
            jQuery('#addemail-form').hide(); 
            jQuery('.region.region-highlighted').html(getMessage('error', response[0].Description));
    };
    LRObject.util.ready(function () {         
        LRObject.init("addEmail", addemail_options);
    });
    jQuery('#lr-loading').hide();
}

function initializeRemoveEmailCiamForms(divhtml) {
    var removeemail_options = {};
    removeemail_options.container = "removeemail-container";
    removeemail_options.onSuccess = function (response) {
         jQuery('#removeemail-form').hide(); 
         jQuery('.region.region-highlighted').html(getMessage('status', 'Email removed successfully'));
         divhtml.remove();  
    };
    removeemail_options.onError = function (response) {
            jQuery('#removeemail-form').hide(); 
            jQuery('.region.region-highlighted').html(getMessage('error', response[0].Description));
    };
    LRObject.util.ready(function () {       
        LRObject.init("removeEmail", removeemail_options);
    });
    jQuery('#lr-loading').hide();
}

function initializeChangePasswordCiamForms() {
    var changepassword_options = {};
    changepassword_options.container = "changepassword-container";
    changepassword_options.onSuccess = function (response) {
        handleResponse(true, "Password has been updated successfully");
    };
    changepassword_options.onError = function (errors) {
        handleResponse(false, errors[0].Description, "", "error");
    };

    LRObject.util.ready(function () {
        LRObject.init("changePassword", changepassword_options);
    });
    jQuery('#lr-loading').hide();
}

function ciamRedirect(token, name) {
    var vtype = LRObject.util.getQueryParameterByName("vtype");
    if (vtype != null && vtype != "" && loggedIn) {
        if (vtype == "emailverification") {
            jQuery('.region.region-highlighted').html(getMessage('status', 'Your email has been verified successfully'));
            LocalDomain = homeDomain + "user/ciamlogin?destination=" + domainName + "user";
        }
    }
    setTimeout(function () {
        jQuery('#lr-loading').show();
    }, 700);

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

LRObject.$hooks.register('afterFormRender', function (name) {
    if (name == "socialRegistration") {
        jQuery('#login-container').find('form[name=loginradius-socialRegistration]').parent().addClass('socialRegistration');
    }
    if (name == "removeemail") {
       jQuery('#loginradius-removeemail-emailid').val(dropemailvalue);      
    }    
});