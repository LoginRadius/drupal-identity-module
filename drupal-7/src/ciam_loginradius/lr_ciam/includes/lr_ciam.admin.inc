<?php

/**
 * @file
 * Admin page callback file for the lr_ciam module.
 */
module_load_include('inc', 'lr_ciam', 'includes/lr_ciam.functions');
module_load_include('php', 'lr_ciam', 'LoginRadiusSDK/Utility/Functions');
module_load_include('php', 'lr_ciam', 'LoginRadiusSDK/LoginRadiusException');
module_load_include('php', 'lr_ciam', 'LoginRadiusSDK/Clients/IHttpClientInterface');
module_load_include('php', 'lr_ciam', 'LoginRadiusSDK/CustomerRegistration/Advanced/ConfigurationAPI');

use \LoginRadiusSDK\Utility\Functions;
use \LoginRadiusSDK\LoginRadiusException;
use \LoginRadiusSDK\Clients\IHttpClientInterface;
use \LoginRadiusSDK\CustomerRegistration\Advanced\ConfigurationAPI;


/**
 * Function that build an abstract representation of a HTML form for Social Login settings.
 *
 * @param array $form Nested array of form elements that comprise the form.
 * @param array $form_state A keyed array containing the current state of the form.
 * @return array A renderable form array for the respective request.
 */
function lr_ciam_activation_settings($form, &$form_state) {
    $podata = isset($_POST['op']) ? $_POST['op'] : ''; 
    $api_key = variable_get('lr_ciam_apikey', '');
    $api_secret = variable_get('lr_ciam_apisecret', '');
    try {
        if (isset($api_key) && $api_key != '' && isset($api_secret) && $api_secret != '' && isset($podata) && $podata != 'Save configuration') {
            $configObject = new ConfigurationAPI();
            $configData = $configObject->getConfigurations();
            if($configData->IsPhoneLogin)
            {
                drupal_set_message(t('If only the Phone Id Login options is enabled for the App, a random Email Id will be generated if a user registered using the PhoneID. Format of random email id is: "randomid+timestamp@yourdomain.com"'), 'warning');
            }
        }} catch (Exception $e) {
        watchdog('loginradius_logging', $e, array(), WATCHDOG_ERROR);
    }

    drupal_add_css(drupal_get_path('module', 'lr_ciam') . '/css/lr_ciam.admin.min.css');
    drupal_add_js(
      drupal_get_path('module', 'lr_ciam') . '/js/LoginRadiusActivation.js', array(
      'type' => 'file',
      'scope' => 'header',
      'weight' => 10,
        )
    );

    $decrypted_secret_key = lr_ciam_encrypt_and_decrypt( variable_get('lr_ciam_apisecret'), variable_get('lr_ciam_apikey'), 'd' );
    $setup_group = 'setup';
    $form[$setup_group] = array(
      '#type' => 'fieldset',
      '#title' => t('LoginRadius API Configurations'),
      '#description' => t('To access the loginradius web service please enter the credentials below ( <a href="https://www.loginradius.com/docs/api/v2/admin-console/platform-security/api-key-and-secret/" target="_blank">How to get it?</a> )'),
      '#id' => 'setup',
      '#collapsible' => TRUE,
    );

    $form[$setup_group]['lr_ciam_apikey'] = array(
      '#type' => 'textfield',
      '#title' => t('LoginRadius API Key'),
      '#required' => TRUE,
      '#default_value' => variable_get('lr_ciam_apikey', ''),     
    );

    $form[$setup_group]['lr_ciam_apisecret'] = array(
      '#type' => 'textfield',
      "#suffix" => '<div id="ciam_show_button">Show</div>',
      '#title' => t('LoginRadius API Secret'),
      '#required' => TRUE,
      '#default_value' => $decrypted_secret_key,     
    );    

    // Submit handler.
    $form['#submit'][] = 'lr_ciam_admin_settings_submit';
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save configuration'),
    );
    return $form;
}
 
function lr_ciam_select_template($template_array)
{
    $templates = array();
    if(!in_array('default', $template_array) || empty($template_array))
            {
                array_push($template_array, 'default');
            }

            foreach ($template_array as $template) {
                $templates[$template] = $template;
            }
            array_unshift($templates, '-- Select Template --');            
            return $templates;
}

function lr_ciam_authentication_settings($form, &$form_state) {
    drupal_add_css(drupal_get_path('module', 'lr_ciam') . '/css/lr_ciam.admin.min.css');
     $api_key = variable_get('lr_ciam_apikey', '');
     $api_secret = variable_get('lr_ciam_apisecret', '');

     $configObject = new ConfigurationAPI();
     $configData = $configObject->getConfigurations();  
     $email_templates = lr_ciam_get_email_templates($api_key, $api_secret);  

    // Basic user settings section.
    $basic_group = 'basic';
    $form[$basic_group] = array(
      '#type' => 'fieldset',
      '#title' => t('Redirection settings after login<a title="This feature sets the redirection to the page where user will get redirected to post login."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a>'),
      '#collapsible' => TRUE,
    );

    $form[$basic_group]['lr_ciam_userlogin_redirect'] = array(
      '#type' => 'radios',
      '#default_value' => variable_get('lr_ciam_userlogin_redirect', 0),
      '#options' => array(
        0 => t('Redirect to same page'),
        1 => t('Redirect to profile page'),
        2 => t('Redirect to custom page (If you want user to be redirected to specific URL after login)'),
      ),
      '#attributes' => array(
        'onchange' => "showAndHideCustomPageDiv();",
      )
    );

    $form[$basic_group]['lr_ciam_userlogin_redirect']['lr_ciam_custom_redirection'] = array(
      '#type' => 'textfield',
      '#weight' => 50,
      '#default_value' => variable_get('lr_ciam_custom_redirection'),
    );

    $email_auth_settings = 'email_auth_settings';
    $form[$email_auth_settings] = array(
      '#type' => 'fieldset',
      '#title' => t('Email Authentication Settings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );  
    
    $form[$email_auth_settings]['lr_ciam_prompt_password_on_social_login'] = array(
      '#type' => 'radios',
      '#id' => 'prompt_password',
      '#title' => t('Enable prompt password on Social login<a title="This feature when enabled, will prompt the user to set the password at the time of login from any social provider."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a>'),
      '#default_value' => variable_get('lr_ciam_prompt_password_on_social_login', 'false'),
      '#options' => array(
        'true' => t('Yes'),
        'false' => t('No'),
      )
    );

    $form[$email_auth_settings]['lr_ciam_enable_user_name'] = array(
      '#type' => 'radios',
      '#title' => t('Enable login with username<a title="This feature when enabled, will let the user to login with username."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a>'),
      '#default_value' => variable_get('lr_ciam_enable_user_name', 'false'),
      '#options' => array(
        'true' => t('Yes'),
        'false' => t('No'),
      )
    );

    $form[$email_auth_settings]['lr_ciam_ask_email_always_for_unverified'] = array(
      '#type' => 'radios',
      '#title' => t('Ask for email from unverified user<a title="This feature when enabled, will ask for email every time user tries to login if email is not verified."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a>'),
      '#default_value' => variable_get('lr_ciam_ask_email_always_for_unverified', 'false'),
      '#options' => array(
        'true' => t('Yes'),
        'false' => t('No'),
      )
    );
     
    $form[$email_auth_settings]['lr_ciam_ask_required_field_on_traditional_login'] = array(
      '#type' => 'radios',
      '#title' => t('Ask for required field on traditional login<a title="This feature when enabled, will ask for newly added required fields on traditional login."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a> '),
      '#default_value' => variable_get('lr_ciam_ask_required_field_on_traditional_login', 'false'),
      '#options' => array(
        'true' => t('Yes'),
        'false' => t('No'),
      )
    ); 

    if(isset($email_templates->EmailTemplates)) {
    $form[$email_auth_settings]['lr_ciam_welcome_email_template'] = array(
      '#type' => 'select',
      '#title' => t('Welcome email template<a title="Select the name of Welcome email template which is created in the LoginRadius Dashboard."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a> '),
      '#options' => lr_ciam_select_template($email_templates->EmailTemplates->Welcome),
      '#default_value' => variable_get('lr_ciam_welcome_email_template')
    );

    $form[$email_auth_settings]['lr_ciam_email_verification_template'] = array(
      '#type' => 'select',
      '#title' => t('Account verification email template<a title="Select the name of Account verification email template which is created in the LoginRadius Dashboard."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a> '),
      '#options' => lr_ciam_select_template($email_templates->EmailTemplates->Verification),
      '#default_value' => variable_get('lr_ciam_email_verification_template')
    );

    $form[$email_auth_settings]['lr_ciam_forgot_password_template'] = array(
      '#type' => 'select',
      '#title' => t('Reset password email template<a title="Select the name of Reset password email template which is created in the LoginRadius Dashboard."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a> '),
      '#options' => lr_ciam_select_template($email_templates->EmailTemplates->ResetPassword),
      '#default_value' => variable_get('lr_ciam_forgot_password_template')
    );
    }

    if(isset($configData) && $configData->TwoFactorAuthentication->IsEnabled){
    $form[$email_auth_settings]['lr_ciam_sms_template_2fa'] = array(
      '#type' => 'select',
      '#title' => t('Two-factor authentication SMS template<a title="Select the name of Two-factor authentication SMS template which is created in the LoginRadius Dashboard."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a> '),
      '#options' => lr_ciam_select_template($email_templates->SMSTemplates->SecondFactorAuthentication),
      '#default_value' => variable_get('lr_ciam_sms_template_2fa')
    );
   }
       
    if(isset($email_templates->SMSTemplates)){
    $phone_auth_settings = 'phone_auth_settings';

    if(isset($configData) && $configData->IsPhoneLogin){
    $form[$phone_auth_settings] = array(
      '#type' => 'fieldset',
      '#title' => t('Phone Authentication Settings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ); 
    $form[$phone_auth_settings]['lr_ciam_exist_phone_number'] = array(
      '#type' => 'radios',    
      '#title' => t('Check Phone number exist or not<a class="lrTooltip" title="Turn on, if you want to enable Phone Exist functionality."> (<span class="lrTooltipColor">?</span>)</a>'),
      '#default_value' => variable_get('lr_ciam_exist_phone_number', 'false'),
      '#options' => array(
        'true' => t('Yes'),
        'false' => t('No'),
      )
    ); 
     $form[$phone_auth_settings]['lr_ciam_sms_template'] = array(
      '#type' => 'select',
      '#title' => t('Phone welcome SMS template<a class="lrTooltip" title="Select the name of Phone welcome SMS template which is created in the LoginRadius Dashboard."> (<span class="lrTooltipColor">?</span>)</a>'),
      '#options' => lr_ciam_select_template($email_templates->SMSTemplates->Welcome),
      '#default_value' => variable_get('lr_ciam_sms_template')
    );
     $form[$phone_auth_settings]['lr_ciam_sms_template_phone_verification'] = array(
      '#type' => 'select',
      '#title' => t('Phone verification SMS template<a class="lrTooltip" title="Select the name of Phone verification SMS template which is created in the LoginRadius Dashboard."> (<span class="lrTooltipColor">?</span>)</a>'),
      '#options' => lr_ciam_select_template($email_templates->SMSTemplates->Verification),
      '#default_value' => variable_get('lr_ciam_sms_template_phone_verification')
    );
     $form[$phone_auth_settings]['lr_ciam_sms_template_password_reset'] = array(
      '#type' => 'select',
      '#title' => t('Password reset SMS template<a class="lrTooltip" title="Select the name of Password reset SMS template which is created in the LoginRadius Dashboard."> (<span class="lrTooltipColor">?</span>)</a>'),
      '#options' => lr_ciam_select_template($email_templates->SMSTemplates->ResetPassword),
      '#default_value' => variable_get('lr_ciam_sms_template_password_reset')
    );
     $form[$phone_auth_settings]['lr_ciam_sms_template_change_number'] = array(
      '#type' => 'select',
      '#title' => t('Change phone number SMS template<a class="lrTooltip" title="Select the name of Change phone number SMS template which is created in the LoginRadius Dashboard."> (<span class="lrTooltipColor">?</span>)</a>'),
      '#options' => lr_ciam_select_template($email_templates->SMSTemplates->ChangePhoneNo),
      '#default_value' => variable_get('lr_ciam_sms_template_change_number')
    ); 
   }
  }
    
    module_load_include('inc', 'lr_ciam', 'includes/lr_field_mapping');
    //field mapping section
    $field_map_group = 'fieldmap';
    $form[$field_map_group] = array(
      '#type' => 'fieldset',
      '#title' => t('Field Mapping'),
      '#description' => t('Each of your <a href="!url" target="_blank">fields that are attached to users</a> are listed below. Map social provider data to field by choosing from select list and configure your user fields from <a href="!url"> FIELD MAPPING </a>section.<br/><br/>', array(
        '!url' => url('admin/config/people/accounts/fields')
      )),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form[$field_map_group]['lr_ciam_user_fields'] = array(
      '#title' => 'user fields',
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#weight' => 5,
      '#collapsible' => TRUE,
    );
    $properties = lr_field_mapping_ciam_user_properties();
    $property_options = array();

    foreach ($properties as $property => $property_info) {
        if (isset($property_info['field_types'])) {
            foreach ($property_info['field_types'] as $field_type) {
                $property_options[$field_type][$property] = $property_info['label'];
            }
        }
    } 

    $field_defaults = variable_get('lr_ciam_user_fields', array());
    $instances = field_info_instances('user', 'user');

    foreach ($instances as $field_name => $instance) {
        $field = field_info_field($instance['field_name']);

        if (isset($property_options[$field['type']])) {
            $options = array_merge(array('' => t('- Do not import -')), $property_options[$field['type']]);

            $form[$field_map_group]['lr_ciam_user_fields'][$field_name] = array(
              '#title' => check_plain(t($instance['label'])),
              '#type' => 'select',
              '#options' => $options,
              '#default_value' => isset($field_defaults[$field_name]) ? $field_defaults[$field_name] : '',
            );
        }
        else {
            $form[$field_map_group]['lr_ciam_user_fields'][$field_name] = array(
              '#title' => check_plain(t($instance['label'])),
              '#type' => 'form_element',
              '#children' => '<em>' . t('This field cannot be mapped with LR field.') . '</em>',
              '#theme_wrappers' => array('form_element'),
            );
        }
    }  


    if (module_exists('profile2')) {
        $form[$field_map_group]['lr_ciam_profile_fields'] = array(
          '#title' => 'Profile2 fields',
          '#type' => 'fieldset',
          '#tree' => TRUE,
          '#weight' => 5,
          '#collapsible' => TRUE,
        );

        $properties = lr_field_mapping_ciam_user_properties();
        $property_options = array();

        foreach ($properties as $property => $property_info) {
            if (isset($property_info['field_types'])) {
                foreach ($property_info['field_types'] as $field_type) {
                    $property_options[$field_type][$property] = $property_info['label'];
                }
            }
        }

        $field_defaults = variable_get('lr_ciam_profile_fields', array());

        foreach (field_info_instances('profile2') as $field_name => $instances) {
            $form[$field_map_group]['lr_ciam_profile_fields'][$field_name] = array(
              '#type' => 'fieldset',
              '#title' => check_plain(t($field_name)),
              '#tree' => TRUE,
              '#weight' => 5,
              '#collapsible' => TRUE,
            );
            foreach ($instances as $instance => $array) {
                $field = field_info_field($array['field_name']);
                if (isset($property_options[$field['type']])) {
                    $options = array_merge(array('' => t('- Do not import -')), $property_options[$field['type']]);
                    $form[$field_map_group]['lr_ciam_profile_fields'][$field_name][$array['field_name']] = array(
                      '#title' => check_plain(t($array['label'])),
                      '#type' => 'select',
                      '#options' => $options,
                      '#default_value' => isset($field_defaults[$field_name][$array['field_name']]) ? $field_defaults[$field_name][$array['field_name']] : '',
                    );
                }
                else {
                    $form[$field_map_group]['lr_ciam_profile_fields'][$field_name][$array['field_name']] = array(
                      '#title' => check_plain(t($array['label'])),
                      '#type' => 'form_element',
                      '#children' => '<em>' . t('Not any mappable properties.') . '</em>',
                      '#theme_wrappers' => array('form_element'),
                    );
                }
            }
        }
    }

    $form['#submit'][] = 'lr_ciam_admin_settings_submit';
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save configuration'),
    );
    return $form;
}

function lr_ciam_advanced_settings($form, &$form_state) {
  drupal_add_css(drupal_get_path('module', 'lr_ciam') . '/css/lr_ciam.admin.min.css');
  $api_key = variable_get('lr_ciam_apikey', '');
  $api_secret = variable_get('lr_ciam_apisecret', '');

  $configObject = new ConfigurationAPI();
  $configData = $configObject->getConfigurations();  
  $email_templates = lr_ciam_get_email_templates($api_key, $api_secret);  
  
  $advance_auth_settings = 'advanced_settings';
  $form[$advance_auth_settings] = array(
    '#type' => 'fieldset',
    '#title' => t('Advacned Options'),
    '#collapsible' => TRUE,
  );

    $form[$advance_auth_settings]['lr_ciam_instant_link_login'] = array(
    '#type' => 'radios',            
    '#title' => t('Enable Passwordless Link Login<a class="lrTooltip" title="This feature enables Passwordless Link Login on the login form."> (<span class="lrTooltipColor">?</span>)</a>'),
    '#default_value' => variable_get('lr_ciam_instant_link_login', 'false'),
    '#options' => array(
      'true' => t('Yes'),
      'false' => t('No'),
    ),
      '#attributes' => array(
      'onchange' => "showAndHideinstantemailOptions();",
    )
  );  

  $form[$advance_auth_settings]['lr_ciam_instant_link_login_email_template'] = array(
    '#type' => 'select',
    '#title' => t('Passwordless link login email template<a class="lrTooltip" title="Select the name of Passwordless link login email template which is created in the LoginRadius Dashboard."> (<span class="lrTooltipColor">?</span>)</a>'),
    '#options' => lr_ciam_select_template($email_templates->EmailTemplates->InstantSignIn),
    '#default_value' => variable_get('lr_ciam_instant_link_login_email_template')
  );

   if(isset($configData) && $configData->IsPhoneLogin) {
    $form[$advance_auth_settings]['lr_ciam_instant_otp_login'] = array(
    '#type' => 'radios',    
    '#title' => t('Enable passwordless OTP login<a title="Turn on, if you want to enable Passwordless OTP login." class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a>'),
    '#default_value' => variable_get('lr_ciam_instant_otp_login', 'false'),
    '#options' => array(
      'true' => t('Yes'),
      'false' => t('No'),
    ),
      '#attributes' => array(
      'onchange' => "showAndHideinstantPhoneOptions();",
    )
  );
    $form[$advance_auth_settings]['lr_ciam_sms_template_one_time_passcode'] = array(
      '#type' => 'select',
      '#title' => t('Passwordless OTP login SMS template<a title="Select the name of Passwordless OTP template which is created in the LoginRadius Dashboard." class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a>'),
      '#options' => lr_ciam_select_template($email_templates->SMSTemplates->OneTimePassCode),
      '#default_value' => variable_get('lr_ciam_sms_template_one_time_passcode')
    );     
  }  
  
  $form[$advance_auth_settings]['lr_ciam_display_password_strength'] = array(
    '#type' => 'radios',
    '#title' => t('Enable password strength<a title="This feature when enabled, shows the strength bar under the password field on registration form, reset password form and change password form." class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a> '),
    '#default_value' => variable_get('lr_ciam_display_password_strength', 'false'),
    '#options' => array(
      'true' => t('Yes'),
      'false' => t('No'),
    )
  );
  
  $form[$advance_auth_settings]['lr_auto_hide_messages'] = array(
    '#type' => 'textfield',
      '#element_validate' => array('lr_auto_hide_messages_validate'),
      '#attributes' => array(
      ' type' => 'number',
  ),
    '#title' => t('Messages timeout setting (in seconds)<a title="Enter the duration (in seconds) to hide response message."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a> '),
    '#default_value' => variable_get('lr_auto_hide_messages')
  );

  $form[$advance_auth_settings]['lr_ciam_save_mail_in_db'] = array(
    '#type' => 'radios',
    '#title' => t("Do you want to store user's email address in the database?"),
    '#default_value' => variable_get('lr_ciam_save_mail_in_db', 'false'),
    '#options' => array(
      'true' => t('Yes'),
      'false' => t('No'),
    )
  );

  $form[$advance_auth_settings]['lr_ciam_save_username_in_db'] = array(
    '#type' => 'radios',
    '#title' => t("Do you want to store user's first and last name as their username in the database?"),
    '#default_value' => variable_get('lr_ciam_save_username_in_db', 'false'),
    '#options' => array(
      'true' => t('Yes'),
      'false' => t('No'),
    )
  );

  $site_map_message = variable_get('lr_ciam_terms_and_condition_html', array('value' => '', 'format' => NULL));
  $form[$advance_auth_settings]['lr_ciam_terms_and_condition_html'] = array(
    '#type' => 'text_format',
    '#format' => isset($site_map_message['format']) ? $site_map_message['format'] : NULL,
    '#title' => t('Terms and Conditions<a title="Enter the content which needs to be displayed on the registration form."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a> '),
    '#rows' => 4,
    '#default_value' => $site_map_message['value'],
  );
 
  $form[$advance_auth_settings]['lr_ciam_custom_options'] = array(
    '#type' => 'textarea',
    '#title' => t('Custom options for LoginRadius interface<a title="This feature allows custom CIAM options to be enabled on the LoginRadius interface."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a> '),
    '#id' => 'add_custom_options',
    '#rows' => 4,
    '#default_value' => variable_get('lr_ciam_custom_options'),
    '#attributes' => array(
      'onchange' => "lrCheckValidJson('add_custom_options');",
    ),
    '#description' => t('Insert custom option like commonOptions.usernameLogin = true;'),
  );

  $form[$advance_auth_settings]['lr_ciam_registration_schema'] = array(
    '#type' => 'textarea',
    '#title' => t('Registration form schema<a title="From here, you can customize the default registration form according to your desired fields, validation rules and field types."  class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a> '),
    '#id' => 'add_registratiom_schema',
    '#rows' => 4,
    '#default_value' => variable_get('lr_ciam_registration_schema'),
    '#attributes' => array(
      'onchange' => "lrCheckValidJson('add_registratiom_schema');",
    ),
    '#element_validate' => array('check_valid_json'),
  );
  
  $form['#submit'][] = 'lr_ciam_admin_settings_submit';
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration'),
  );
  return $form;
}

function lr_auto_hide_messages_validate($form,$form_state)
{
    if($form['#value'] < 0)
    {
        form_error($form, t('Auto hide success message time should be positive'));
    }
}

function check_valid_json($form,$form_state){
if($form['#value'] != ''){
  $validjson = json_decode($form['#value']);
  if(is_null($validjson)){
    form_error($form, t('Please enter valid JSON'));
  }
 }
}

/**
 * Submit Social Login tab settings.
 *
 * @param array $form Nested array of form elements that comprise the form.
 * @param array $form_state A keyed array containing the current state of the form
 */
function lr_ciam_admin_settings_submit($form, &$form_state) {
    if (!lr_ciam_save_settings($form_state)) {
        return FALSE;
    }
    $settings = array(
      'lr_ciam_force_registration' => '1',
      'lr_ciam_update_profile' => '1',
    );

    foreach ($settings as $key => $values) {
        if (!variable_get($key, FALSE) && variable_get($key, '') != '0') {
            variable_set($key, $values);
        }
    }
}

/**
 * Save settings to database.
 *
 * @param array $form_state A keyed array containing the current state of the form
 */
function lr_ciam_save_settings(&$form_state) {
    $api_key = isset($form_state['values']['lr_ciam_apikey']) ? trim($form_state['values']['lr_ciam_apikey']) : trim(variable_get('lr_ciam_apikey', ''));
    $api_secret = isset($form_state['values']['lr_ciam_apisecret']) ? trim($form_state['values']['lr_ciam_apisecret']) : trim(variable_get('lr_ciam_apisecret', ''));
    
    $currentURL = $_SERVER['HTTP_HOST'] . request_uri();
    $link_array = explode('/',$currentURL);
    $page = end($link_array);

    $is_overlayed = (bool)(preg_match( '/render=overlay$/', $_SERVER['QUERY_STRING']));    
 
    if($page == 'authentication' || $page == 'advanced' || $page == 'sso' || $page == 'ief') {
      $api_secret = lr_ciam_encrypt_and_decrypt( $api_secret, $api_key, 'd' );
    }  else if($is_overlayed && ($page == 'authentication?render=overlay&render=overlay' || $page == 'advanced?render=overlay&render=overlay' || $page == 'sso?render=overlay&render=overlay' || $page == 'ief?render=overlay&render=overlay')){
      $api_secret = lr_ciam_encrypt_and_decrypt( $api_secret, $api_key, 'd' );
    }

    $data = lr_ciam_get_authentication($api_key, $api_secret);
    if (isset($data['status']) && $data['status'] != 'status') {
        drupal_set_message($data['message'], $data['status']);
        return FALSE;
    }
    form_state_values_clean($form_state);    
  
    if(!variable_get('lr_ciam_apikey', FALSE) || !variable_get('lr_ciam_apisecret', FALSE) || variable_get('lr_ciam_apikey' , '') != $api_key || variable_get('lr_ciam_apisecret' , '') != $api_secret)
    {
      Functions::setDefaultApplication($api_key, $api_secret);
        $configObject = new ConfigurationAPI();
        $config = (array)$configObject->getConfigurations();


                    if(isset($config['AppName'])) {
                    variable_set('lr_ciam_site_name', $config['AppName']);   
                    variable_set('lr_ciam_api_request_signing', (isset($config['ApiRequestSigningConfig']->IsEnabled) && $config['ApiRequestSigningConfig']->IsEnabled) ? 'true' : 'false');                    
                    }

                    if(isset($config['CustomDomain']) && $config['CustomDomain'] != '') {
                    variable_set('lr_ciam_custom_hub_domain', $config['CustomDomain']);                 
                    }                      
           

                    if(isset($config['AskPasswordOnSocialLogin']) && !variable_get('lr_ciam_prompt_password_on_social_login',FALSE)) {
                        if($config['AskPasswordOnSocialLogin'] == 1) {
                         variable_set('lr_ciam_prompt_password_on_social_login', 'true');
                        }
                        else {
                            variable_set('lr_ciam_prompt_password_on_social_login', 'false');
                        }
                    }

                    if(isset($config['IsUserNameLogin']) && !variable_get('lr_ciam_enable_user_name',FALSE)){
                        if($config['IsUserNameLogin'] == 1) {
                            variable_set('lr_ciam_enable_user_name', 'true');
                        }
                        else {
                            variable_set('lr_ciam_enable_user_name', 'false');
                        }
                    }

                    if(isset($config['AskEmailIdForUnverifiedUserLogin']) && !variable_get('lr_ciam_ask_email_always_for_unverified',FALSE))
                    {
                        if($config['AskEmailIdForUnverifiedUserLogin'] == 1){
                         variable_set('lr_ciam_ask_email_always_for_unverified', 'true');
                        }
                        else{
                            variable_set('lr_ciam_ask_email_always_for_unverified', 'false');
                        }
                    }
                    
                    if(isset($config['AskRequiredFieldsOnTraditionalLogin']) && !variable_get('lr_ciam_ask_required_field_on_traditional_login',FALSE))
                    {
                        if($config['AskRequiredFieldsOnTraditionalLogin'] == 1){
                         variable_set('lr_ciam_ask_required_field_on_traditional_login', 'true');
                        }
                        else{
                            variable_set('lr_ciam_ask_required_field_on_traditional_login', 'false');
                        }
                    }

                    if(isset($config['CheckPhoneNoAvailabilityOnRegistration']) && !variable_get('lr_ciam_ask_required_field_on_traditional_login',FALSE))
                    {
                        if($config['CheckPhoneNoAvailabilityOnRegistration'] == 1) {
                         variable_set('lr_ciam_exist_phone_number', 'true');
                        }
                        else{
                            variable_set('lr_ciam_exist_phone_number', 'false');
                        }
                    }
    }
    
    foreach ($form_state['values'] as $key => $value) {
      if (is_array($value) && isset($form_state['values']['array_filter'])) {
          $value = array_keys(array_filter($value));
      }
      if($key == 'lr_ciam_apisecret'){
        $value = lr_ciam_encrypt_and_decrypt( $form_state['values']['lr_ciam_apisecret'], $form_state['values']['lr_ciam_apikey'], 'e' );     
      }
      variable_set($key, $value);
    }
    

    drupal_set_message(t('The configuration options have been saved.'));
    // Clear the cached pages and blocks.
    cache_clear_all();
    menu_rebuild();
    return TRUE;
}


/**
 * Function validates against GUID format of keys
 *
 * @param string $key LoginRadius API key/secret
 * @return boolean If valid - true, else - false
 */
function lr_ciam_isvalid_key($key) {
    return preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $key);
}

/**
 * Check API credentials and API connection methos is working or not
 *
 * @param string $apikey LoginRadius API Key
 * @param string $apisecret LoginRadius API Secret
 * @param string $api_handler API connection method curl/drupal_http_request
 * @return array Contain message and status of data
 */
function lr_ciam_get_authentication($apikey, $apisecret) {
    if (isset($apikey)) {
        $data = array();
        try {
          $query_array = array(
            'apiKey' => $apikey,
            'apiSecret' => $apisecret
          );
         
          $url = 'https://api.loginradius.com/api/v2/app/validate';
        
          $result = Functions::_apiClientHandler('GET',$url, $query_array);
            if (empty($result)) {
                $data['message'] = check_plain('please check your php.ini settings to enable CURL or FSOCKOPEN');
                $data['status'] = 'error';
            }
            elseif (isset($result->Status) && !$result->Status) {
                $error = array(
                  "API_KEY_NOT_VALID" => "LoginRadius API key is invalid. Get your LoginRadius API key from <a href='http://www.loginradius.com' target='_blank'>LoginRadius</a>",
                  "API_SECRET_NOT_VALID" => "LoginRadius API Secret is invalid. Get your LoginRadius API Secret from <a href='http://www.loginradius.com' target='_blank'>LoginRadius</a>",
                  "API_KEY_NOT_FORMATED" => "LoginRadius API Key is not formatted correctly",
                  "API_SECRET_NOT_FORMATED" => "LoginRadius API Secret is not formatted correctly"
                );

                foreach ($result->Messages as $value) {
                    $data['message'] = $error["$value"];
                    $data['status'] = 'error';
                    break;
                }
            }
            return $data;
        }
        catch (Exception $e) {
            $data['message'] = 'Something went wrong, check your credentials';
            $data['status'] = 'error';
            return $data;          
        }
    }
}
