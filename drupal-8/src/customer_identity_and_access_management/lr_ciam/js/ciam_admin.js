jQuery(document).ready(function () {
   if(jQuery("#secret").prop("type") == 'password') {
        jQuery("#secret").prop("type",'text');       
    } else {
        jQuery("#secret").prop("type",'password');
    }

    jQuery("#ciam_show_button").click(function () {
        if(jQuery("#secret").prop("type") == 'password'){
            jQuery("#secret").prop("type",'text');       
            jQuery("#ciam_show_button").text("Hide");       
        }else{
            jQuery("#secret").prop("type",'password');
            jQuery("#ciam_show_button").text("Show"); 
        }
      });


      jQuery("#edit-submit").on('click',function(){  
        var profile = jQuery('#ciam_registration_schema').val();
        var response = '';
        try
        {
            if (profile != "" && typeof profile !== 'undefined') {
            response = jQuery.parseJSON(profile);
            if (response != true && response != false) {
                var validjson = JSON.stringify(response, null, '\t').replace(/</g, '&lt;');
                if (validjson != 'null') {
                    jQuery('#ciam_registration_schema').val(validjson);
                    jQuery(".registation_form_schema").hide();    
                    jQuery('#ciam_registration_schema').css("border", "1px solid green");
                } else {
                    jQuery('#ciam_registration_schema').css("border", "1px solid green");
                }
            }} else {
                jQuery(".registation_form_schema").hide();    
            }
        } catch (e)
        {
            jQuery('#ciam_registration_schema').css("border", "1px solid red");
            jQuery(".registation_form_schema").show();        
            jQuery('.registation_form_schema').html('<div style="color:red;">Please enter a valid Json. '+e.message+'</div>');
            return false;
        }
    });
});
   
function lrCheckValidJson() {
    jQuery('#ciam_custom_options').change(function () {
        var profile = jQuery('#ciam_custom_options').val();
        var response = '';
        try
        {
            response = jQuery.parseJSON(profile);
            if (response != true && response != false) {
                var validjson = JSON.stringify(response, null, '\t').replace(/</g, '&lt;');
                if (validjson != 'null') {
                    jQuery('#ciam_custom_options').val(validjson);
                    jQuery('#ciam_custom_options').css("border", "1px solid green");
                } else {
                    jQuery('#ciam_custom_options').css("border", "1px solid red");
                }
            } else {
                jQuery('#ciam_custom_options').css("border", "1px solid green");
            }
        } catch (e)
        {
            jQuery('#ciam_custom_options').css("border", "1px solid red");
        }
    });
}

