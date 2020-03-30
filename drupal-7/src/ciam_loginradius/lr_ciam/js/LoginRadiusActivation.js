jQuery(document).ready(function () {
    //handleResponse(true, "");
    if(jQuery("#edit-lr-ciam-apisecret").attr('type') == 'password'){
        document.getElementById('edit-lr-ciam-apisecret').setAttribute('type', 'text');     
    }else{
        document.getElementById('edit-lr-ciam-apisecret').setAttribute('type', 'password');
    }

    jQuery("#ciam_show_button").click(function () { 
        if(jQuery("#edit-lr-ciam-apisecret").attr('type') == 'password') {
            document.getElementById('edit-lr-ciam-apisecret').setAttribute('type', 'text');    
            jQuery("#ciam_show_button").text("Hide");       
        }else{
            document.getElementById('edit-lr-ciam-apisecret').setAttribute('type', 'password');
            jQuery("#ciam_show_button").text("Show"); 
        }
    });
});
