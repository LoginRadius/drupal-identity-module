jQuery(document).ready(function () {
    jQuery('a[href*="user/logout"]').click(function (e) {
        //e.preventDefault();
        var options = {};
        options.onSuccess = function () {
            window.location = drupalSettings.sso.frontUrl;
        };
        LRObject.util.ready(function () {
            LRObject.init("logout", options);
        });
    });
});

if (drupalSettings.sso.isNotLogin) {
    jQuery(document).ready(function () {
        if (jQuery(".interfacecontainerdiv").length) {
            var options = {};
            options.onSuccess = function (response) {
                var form = document.createElement("form");                
                form.action = drupalSettings.sso.loginUrl;
                form.method = "POST";

                var hidden = document.createElement("input");
                hidden.type = "hidden";
                hidden.name = "token";
                hidden.value = response;

                form.appendChild(hidden);
                document.body.appendChild(form);
                form.submit();
            };

            LRObject.util.ready(function () {
                LRObject.init("ssoLogin", options);
            });
        }
    });
    jQuery("#lr-loading").hide();
}

if (drupalSettings.sso.isNotLoginThenLogout) {
    jQuery(document).ready(function () {
        var check_options = {};
        check_options.onError = function () {
            window.location = drupalSettings.sso.logoutUrl;
        };

        LRObject.util.ready(function () {
            LRObject.init("ssoNotLoginThenLogout", check_options);       
        });
    });
}