jQuery(document).ready(function () {
    //handleResponse(true, "");
    jQuery("#fade, #lr-loading").click(function () {
        jQuery('#fade, #lr-loading').hide();
    });
    var url = window.location.pathname + '/create';
    jQuery("a[href= " + url + "]").attr('href', 'https://secure.loginradius.com/user-management/manage-users');
    jQuery('.action-links a').attr('target', '_blank');
    showAndHideUI();
});


var LRObject = new LoginRadiusV2(ciamoption);

var hasOwnProperty = Object.prototype.hasOwnProperty;
function isEmpty(obj) {

    // null and undefined are "empty"
    if (obj == null)
        return true;

    // Assume if it has a length property with a non-zero value
    // that that property is correct.
    if (obj.length > 0)
        return false;
    if (obj.length === 0)
        return true;

    // If it isn't an object at this point
    // it is empty, but it can't be anything *but* empty
    // Is it empty?  Depends on your application.
    if (typeof obj !== "object")
        return true;

    // Otherwise, does it have any properties of its own?
    // Note that this doesn't handle
    // toString and valueOf enumeration bugs in IE < 9
    for (var key in obj) {
        if (hasOwnProperty.call(obj, key))
            return false;
    }
    return true;
}


function showAndHideUI() {
    var options = jQuery('input[name=lr_ciam_email_verification_condition]:checked').val();
    if (options == 2) {
        jQuery('.form-item-lr-ciam-enable-login-on-email-verification,.form-item-lr-ciam-prompt-password-on-social-login,.form-item-lr-ciam-enable-user-name,.form-item-lr-ciam-ask-email-always-for-unverified').hide();
    } else if (options == 1) {
        jQuery('.form-item-lr-ciam-enable-login-on-email-verification,.form-item-lr-ciam-ask-email-always-for-unverified').show();
        jQuery('.form-item-lr-ciam-prompt-password-on-social-login,.form-item-lr-ciam-enable-user-name').hide();
    } else {
        jQuery('.form-item-lr-ciam-enable-login-on-email-verification,.form-item-lr-ciam-prompt-password-on-social-login,.form-item-lr-ciam-ask-email-always-for-unverified,.form-item-lr-ciam-enable-user-name').show();
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
    status = status ? status : "status";
    if (typeof show != 'undefined' && !show) {
        jQuery('#fade').show();
    }
    if (message != null && message != "") {
        jQuery('#lr-loading').hide();
        jQuery('.messageinfo').text(message);
        jQuery(".messages").show();
        jQuery('.messageinfo').show();
        jQuery(".messages").removeClass("error status");
        jQuery(".messages").addClass(status);
        if (isSuccess) {
            jQuery('form').each(function () {
                this.reset();
            });
        }
    } else {
        jQuery(".messages").hide();
        jQuery('.messageinfo').hide();
        jQuery('.messageinfo').text("");
    }
}

LRObject.$hooks.register('startProcess', function () {
    jQuery('#lr-loading').show();
});

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
                case: "worst", // Case Can not be changed
                message: "6 length is required", //Your minimum password length message.
                color: "Red" // Above message color
            }, {
                case: "bad", // Case Can not be changed
                message: "Bad",
                color: "Red"
            }, {
                case: "weak", // Case Can not be changed
                message: "weak",
                color: "yellow"
            }, {
                case: "good", // Case Can not be change
                message: "Good",
                color: "Green"
            }, {
                case: "strong", // Case Can not be changed
                message: "Strong",
                color: "Green"
            }, {
                case: "secure", // Case Can not be changed
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
            handleResponse(false, "An email has been sent to " + jQuery("#loginradius-registration-emailid").val() + ".Please verify your email address");
            jQuery('input[type="text"],input[type="password"],textarea,select').val('');
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
                        handleResponse(true, "");
                        ciamRedirect(response.access_token);
                    } else if (response.Data != null && response.Data.access_token != null && response.Data.access_token != "") {
                        handleResponse(true, "");
                        ciamRedirect(response.Data.access_token);
                    } else {
                        handleResponse(true, "Your email has been verified successfully");                    
                    }
                }
            };
            verifyemail_options.onError = function (errors) {   
                handleResponse(false, errors[0].Description, "", "error");   
            }

            LRObject.util.ready(function () {
                LRObject.init("verifyEmail", verifyemail_options);      
            });
        }
    }   
}

function initializeSocialRegisterCiamForm() {
    var sl_options = {};
    sl_options.onSuccess = function (response) {
        if (response.IsPosted) {
            handleResponse(true, "An email has been sent to " + jQuery("#loginradius-socialRegistration-emailid").val() + ".Please verify your email address.");
            jQuery('#social-registration-form').hide();
            jQuery('#lr-loading').hide();
        } else {
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
        handleResponse(false, "An email has been sent to " + jQuery("#loginradius-forgotpassword-emailid").val() + " with reset Password link");
        jQuery('input[type="text"]').val('');
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
            var messageDiv = getMessage('status', 'Account linked successfully');
            jQuery(messageDiv).insertBefore("#main-wrapper");
            window.setTimeout(function () {
                window.location.reload();
            }, 3000);

        }
    };
    la_options.onError = function (errors) {
        if (errors[0].Description != null) {
            var messageDiv = getMessage('error', errors[0].Description);
            jQuery(messageDiv).insertBefore("#main-wrapper");
        }
    }

    var unlink_options = {};
    unlink_options.onSuccess = function (response) {
        if (response.IsDeleted == true) {
            var messageDiv = getMessage('status', 'Account unlinked successfully');
            jQuery(messageDiv).insertBefore("#main-wrapper");
            window.setTimeout(function () {
                window.location.reload();
            }, 3000);
        }
    };
    unlink_options.onError = function (errors) {
        if (errors[0].Description != null) {
            var messageDiv = getMessage('error', errors[0].Description);
            jQuery(messageDiv).insertBefore("#main-wrapper");
        }
    }

    LRObject.util.ready(function () {
        LRObject.init("linkAccount", la_options);
        LRObject.init("unLinkAccount", unlink_options);
    });
    jQuery('#lr-loading').hide();
}

function getMessage(status, msg) {
    return '<div id="messages"><div class="section clearfix"><div class="messages ' + status + '"><h2 class="element-invisible">Status message</h2>' + msg + '</div></div></div>';
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
 
    if (vtype != null && vtype != "" && (loggedIn == '1' || loggedIn == 1)) {
        if (vtype == "emailverification") {
                handleResponse(true, "Your email has been verified successfully");     
                LocalDomain = homeDomain+"lr_ciam/token_handler?destination=user";
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
});