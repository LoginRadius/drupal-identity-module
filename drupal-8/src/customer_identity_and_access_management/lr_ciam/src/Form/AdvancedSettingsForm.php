<?php

/**
 * @file
 * Contains \Drupal\lr_ciam\Form\AdvancedSettingsForm.
 */

namespace Drupal\lr_ciam\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Cache\Cache;

/**
 * Displays the advanced settings form.
 */
class AdvancedSettingsForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return ['ciam.settings'];
    }

    /**
     * Implements \Drupal\Core\Form\FormInterface::getFormID().
     */
    public function getFormId() {
        return 'advanced_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('ciam.settings');
        // Configuration of which forms to protect, with what challenge.    

        $form['lr_interface_settings'] = [
          '#type' => 'details',
          '#title' => $this->t('CIAM interface customization'),
        ];
        $form['lr_interface_settings']['interface_label'] = [
          '#type' => 'textfield',
          '#title' => $this->t('What text do you want to display above the Social Login interface?'),
          '#default_value' => $config->get('interface_label'),
        ];

        $form['lr_user_settings'] = [
          '#type' => 'details',
          '#title' => $this->t('CIAM additional settings'),
        ];
        $form['lr_user_settings']['ciam_inform_validation_messages'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to display form validation message on authentication pages<a title="Form validation includes checking for username and password lengths, password complexity, etc."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a> '),
          '#default_value' => $config->get('ciam_inform_validation_messages') ? $config->get('ciam_inform_validation_messages') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
        ];
        $form['lr_user_settings']['ciam_terms_and_condition_html'] = [
          '#type' => 'textarea',
          '#title' => t('Enter text to be displayed under the Terms and Condition on the registration page'),
          '#rows' => 2,
          '#default_value' => $config->get('ciam_terms_and_condition_html'),
          '#attributes' => array('placeholder' => t('terms and conditon text')),
        ];
        $form['lr_user_settings']['ciam_form_render_delay'] = [
          '#type' => 'textfield',
          '#title' => t('Enter delay time to generate authentication pages<a title="Recommended for content heavy sites where page loading time is longer due to lots of images, videos, etc. on the page."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a> '),
          '#default_value' => $config->get('ciam_form_render_delay'),
          '#attributes' => array('placeholder' => t('100')),
        ];
        $form['lr_user_settings']['ciam_min_password_length'] = [
          '#type' => 'textfield',
          '#title' => t('Enter desired minimum length for password'),
          '#default_value' => $config->get('ciam_min_password_length'),
          '#attributes' => array('placeholder' => t('6')),
        ];
        $form['lr_user_settings']['ciam_max_password_length'] = [
          '#type' => 'textfield',
          '#title' => t('Enter desired maximum length for password'),
          '#default_value' => $config->get('ciam_max_password_length'),
          '#description' => t('If you want to set password length validation then set both minimum and maximum password length, otherwise it will not work.'),
          '#attributes' => array('placeholder' => t('32')),
        ];
        $form['lr_user_settings']['ciam_welcome_email_template'] = [
          '#type' => 'textfield',
          '#title' => t('Enter template name for welcome email'),
          '#default_value' => $config->get('ciam_welcome_email_template'),    
        ];        
        $form['lr_user_settings']['ciam_email_verification_template'] = [
          '#type' => 'textfield',
          '#title' => t('Enter template name for email verification email'),
          '#default_value' => $config->get('ciam_email_verification_template'),    
        ];        
        $form['lr_user_settings']['ciam_forgot_password_template'] = [
          '#type' => 'textfield',
          '#title' => t('Enter template name for forgot password email'),
          '#default_value' => $config->get('ciam_forgot_password_template'),         
        ];        
        $form['lr_user_settings']['ciam_custom_options'] = [
          '#type' => 'textarea',
          '#title' => t('Please enter custom user registration options for LoginRadius interface.<a title="Custom User Registration options that are added in the LoginRadius js."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a> '),
          '#id' => 'add_custom_options',
          '#rows' => 4,
          '#default_value' => $config->get('ciam_custom_options'),
          '#attributes' => array(
            'placeholder' => t('ciam custom option'),
            'onchange' => "lrCheckValidJson();",
          ),
        ];
        $form['lr_user_settings']['ciam_enable_recaptcha'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable google recaptcha at registration'),
          '#default_value' => $config->get('ciam_enable_recaptcha') ? $config->get('ciam_enable_recaptcha') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
          '#attributes' => array(
            'onchange' => "showAndHideRecaptchaOptions();",
          ),
        ];
        $form['lr_user_settings']['ciam_v2_recaptcha_type'] = [
          '#type' => 'select',
          '#title' => 'Select recaptcha type',
          '#options' => array(
            'v2Recaptcha' => t('v2Recaptcha'),
            'invisibleRecaptcha' => t('invisibleRecaptcha'),
          ),
          '#default_value' => $config->get('ciam_v2_recaptcha_type') ? $config->get('ciam_v2_recaptcha_type') : '',
        ];
        $form['lr_user_settings']['ciam_v2_recaptcha_site_key'] = [
          '#type' => 'textfield',
          '#title' => t('Enter your v2 recaptcha site key'),
          '#default_value' => $config->get('ciam_v2_recaptcha_site_key'),
        ];
        $form['lr_user_settings']['ciam_enable_remember_me'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable Remember me option<a title="Enabling this property would allow the users to check keep me sign in option, This options also has to be enabled by LoginRadius support from backend"  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a> '),
          '#default_value' => $config->get('ciam_enable_remember_me') ? $config->get('ciam_enable_remember_me') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
        ];
        $form['lr_user_settings']['ciam_ask_required_field_on_traditional_login'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable ask required field on traditional login<a title="Enabling this property would prompt an interface of required fields for a traditional legacy or old user account, if the registration schema has changed"  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a> '),
          '#default_value' => $config->get('ciam_ask_required_field_on_traditional_login') ? $config->get('ciam_ask_required_field_on_traditional_login') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
        ];
        $form['lr_user_settings']['ciam_display_password_strength'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable to check password strength<a title="To enable password strength"  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a> '),
          '#default_value' => $config->get('ciam_display_password_strength') ? $config->get('ciam_display_password_strength') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
        ];     
       
        $form['lr_user_settings']['ciam_auto_hide_messages'] = [
          '#type' => 'textfield',
          '#title' => t('Auto hide success and error message<a title="Please enter the duration (in seconds) after which the response messages will get hidden."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a> '),
          '#default_value' => $config->get('ciam_auto_hide_messages'),              
        ];   
        
        $form['lr_login_settings'] = [
          '#type' => 'details',
          '#title' => $this->t('CIAM Login Settings'),
        ];    

        $form['lr_login_settings']['ciam_login_type'] = [
          '#type' => 'radios',
          '#title' => t('Select login type<a title="At a time only one of the Login settings can be set i.e. either Email Login or Phone Login settings as per the functionality set on your LoginRadius app."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a> '),
          '#default_value' => $config->get('ciam_login_type') ? $config->get('ciam_login_type') : 'email',
          '#options' => array(
            'email' => t('Email'),
            'phone' => t('Phone'),
          ),
          '#attributes' => array(
            'onchange' => "showLoginTypeOptions();",
          ),
        ];    
 
        $form['lr_login_settings']['ciam_email_verification_condition'] = [
          '#type' => 'radios',
          '#id' => 'email_verification_options',
          '#title' => t('Select your desired email verification option during the registration process.'),
          '#default_value' => $config->get('ciam_email_verification_condition') ? $config->get('ciam_email_verification_condition') : 0,
          '#options' => array(
            0 => t('Required Email Verification'),
            1 => t('Optional Email Verification'),
            2 => t('Disabled Email Verification')
          ),
          '#attributes' => array(
            'onchange' => "showLoginTypeOptions();",
          ),
        ];

        $form['lr_login_settings']['ciam_enable_login_on_email_verification'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable login upon email verification<a title="Log user in after the verification link is clicked in the verification email."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a> '),
          '#default_value' => $config->get('ciam_enable_login_on_email_verification') ? $config->get('ciam_enable_login_on_email_verification') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
        ];
        $form['lr_login_settings']['ciam_prompt_password_on_social_login'] = [
          '#type' => 'radios',
          '#id' => 'prompt_password',
          '#title' => t('Do you want to prompt for password after registration with social provider?'),
          '#default_value' => $config->get('ciam_prompt_password_on_social_login') ? $config->get('ciam_prompt_password_on_social_login') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
        ];
        $form['lr_login_settings']['ciam_enable_user_name'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable login with username?'),
          '#default_value' => $config->get('ciam_enable_user_name') ? $config->get('ciam_enable_user_name') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
        ];
        $form['lr_login_settings']['ciam_ask_email_always_for_unverified'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to ask for email every time an unverified user tries to log in?'),
          '#default_value' => $config->get('ciam_ask_email_always_for_unverified') ? $config->get('ciam_ask_email_always_for_unverified') : 'false',
          '#options' => array(
            'true' => t('Yes, (ask for email address every time an unverified user logs in)'),
            'false' => t('No'),
          ),
        ];        
        $form['lr_login_settings']['ciam_instant_link_login'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to initiate instant login with email<a title="This option also has to be enabled by LoginRadius support from backend"  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a> '),
          '#default_value' => $config->get('ciam_instant_link_login') ? $config->get('ciam_instant_link_login') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
        ];
        $form['lr_login_settings']['ciam_instant_link_login_email_template'] = [
          '#type' => 'textfield',
          '#title' => t('Enter instant link login email template name'),
          '#default_value' => $config->get('ciam_instant_link_login_email_template'),     
        ];     
        
        $form['lr_login_settings']['ciam_instant_link_login_button_label'] = [
          '#type' => 'textfield',
          '#title' => t('Enter instant link login button name'),
          '#default_value' => $config->get('ciam_instant_link_login_button_label'),    
        ];  
        
        $form['lr_phone_settings'] = [
          '#type' => 'details',
          '#title' => $this->t('CIAM Phone Login Settings'),
        ];        
        $form['lr_phone_settings']['ciam_enable_phone_login'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable login with phone number?'),
          '#default_value' => $config->get('ciam_enable_phone_login') ? $config->get('ciam_enable_phone_login') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
          '#attributes' => array(
            'onchange' => "showLoginTypeOptions();",
          ),
        ];
        $form['lr_phone_settings']['ciam_exist_phone_number'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable option to check phone number exist or not?'),
          '#default_value' => $config->get('ciam_exist_phone_number') ? $config->get('ciam_exist_phone_number') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),        
        ];
        $form['lr_phone_settings']['ciam_sms_template'] = [
          '#type' => 'textfield',
          '#title' => t('Enter Welcome SMS template name'),
          '#default_value' => $config->get('ciam_sms_template'),
        ];
        $form['lr_phone_settings']['ciam_sms_template_phone_verification'] = [
          '#type' => 'textfield',
          '#title' => t('Enter sms template name for phone number verification'),
          '#default_value' => $config->get('ciam_sms_template_phone_verification'),
        ];        
        $form['lr_phone_settings']['ciam_instant_otp_login'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to initiate one click OTP login<a title="To initiate one click OTP login when phone number login enabled at your site"  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a> '),
          '#default_value' => $config->get('ciam_instant_otp_login') ? $config->get('ciam_instant_otp_login') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),        
        ];
        
        $form['lr_phone_settings']['ciam_sms_template_one_time_passcode'] = [
          '#type' => 'textfield',
          '#title' => t('Enter instant OTP Login SMS template name'),
          '#default_value' => $config->get('ciam_sms_template_one_time_passcode'),     
        ];   
          
        $form['lr_phone_settings']['ciam_instant_otp_login_button_label'] = [
          '#type' => 'textfield',
          '#title' => t('Enter instant OTP login button name'),
          '#default_value' => $config->get('ciam_instant_otp_login_button_label'),     
        ];
        
        $form['lr_2fa_settings'] = [
          '#type' => 'details',
          '#title' => $this->t('CIAM 2FA Settings'),
        ];        
        $form['lr_2fa_settings']['ciam_enable_2fa'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable two factor authentication?'),
          '#default_value' => $config->get('ciam_enable_2fa') ? $config->get('ciam_enable_2fa') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
          '#attributes' => array(
            'onchange' => "showAndHide2faOptions();",
          ),
        ];
        $form['lr_2fa_settings']['ciam_2fa_flow'] = [
          '#type' => 'select',
          '#title' => 'Select flow for two factor authentication',
          '#options' => array(
            'required' => t('Required'),
            'optional' => t('Optional'),
          ),
          '#default_value' => $config->get('ciam_2fa_flow') ? $config->get('ciam_2fa_flow') : 'required',
        ];
        $form['lr_2fa_settings']['ciam_google_authentication'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable google authentication?'),
          '#default_value' => $config->get('ciam_google_authentication') ? $config->get('ciam_google_authentication') : 'false',
          '#options' => array(
            'true' => t('Yes'),
            'false' => t('No'),
          ),
        ];
        $form['lr_2fa_settings']['ciam_sms_template_2fa'] = [
          '#type' => 'textfield',
          '#title' => t('Enter sms template name for Two-factor Authentication'),
          '#default_value' => $config->get('ciam_sms_template_2fa'),
        ];


        $form['lr_field_mapping'] = [
          '#type' => 'details',
          '#title' => $this->t('CIAM Field Mapping'),
        ];
        $form['lr_field_mapping']['user_fields'] = array(
          '#title' => 'user fields',
          '#type' => 'details',
          '#tree' => TRUE,
          '#weight' => 5,
          '#open' => TRUE,
        );
        $properties = $this->field_user_properties();
        $property_options = array();

        foreach ($properties as $property => $property_info) {
            if (isset($property_info['field_types'])) {
                foreach ($property_info['field_types'] as $field_type) {
                    $property_options[$field_type][$property] = $property_info['label'];           
                }
            }
        }

        $field_defaults = $config->get('user_fields', array());
        $entity_type = 'user';
        foreach (\Drupal::entityManager()
            ->getFieldDefinitions($entity_type, 'user') as $field_name => $field_definition) {
            $user_bundle = $field_definition->getTargetBundle();
            if (!empty($user_bundle)) {
                $instances[$field_name]['type'] = $field_definition->getType();
                $instances[$field_name]['label'] = $field_definition->getLabel();
            }
        }


        foreach ($instances as $field_name => $instance) {       
            $field = FieldStorageConfig::loadByName($entity_type, $field_name);
            if (isset($property_options[$field->getType()])) {
                $options = array_merge(array('' => t('- Do not import -')), $property_options[$field->getType()]);
                $form['lr_field_mapping']['user_fields'][$field->getName()] = [
                  '#title' => $this->t($instance['label']),
                  '#type' => 'select',
                  '#options' => $options,
                  '#default_value' => isset($field_defaults[$field_name]) ? $field_defaults[$field_name] : '',
                ];
            }
            else {
                $form['lr_field_mapping']['user_fields'][$field->getName()] = [
                  '#title' => $this->t($instance['label']),
                  '#type' => 'form_element',
                  '#children' => $this->t('Not any mappable properties.'),
                  '#theme_wrappers' => array('form_element'),
                ];
            }
        }

        $form['debug'] = [
          '#type' => 'details',
          '#title' => $this->t('Debug'),
        ];
        $form['debug']['ciam_debug_mode'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable Debugging mode<a title="Choosing yes will add debug log in database"  style="text-decoration:none"> (<span style="color:#3CF;">?</span>)</a>'),
          '#default_value' => $config->get('ciam_debug_mode') ? $config->get('ciam_debug_mode') : 0,
          '#options' => [
            1 => t('Yes'),
            0 => t('No'),
          ],
        ];
        // Submit button.
        $form['actions'] = ['#type' => 'actions'];
        $form['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Save configuration'),
        ];

        return parent::buildForm($form, $form_state);
    }

    function field_user_properties() {
        $common = array(
          'ID' => array(
            'label' => t('Provider ID'),
          ),
          'Provider' => array(
            'label' => t('Social Provider'),
            'field_types' => array('text', 'string'),
          ),
          'FullName' => array(
            'label' => t('Full name'),
            'field_types' => array('text', 'string'),
          ),
          'FirstName' => array(
            'label' => t('First name'),
            'field_types' => array('text', 'string'),
          ),
          'LastName' => array(
            'label' => t('Last name'),
            'field_types' => array('text', 'string'),
          ),        
          'Gender' => array(
            'label' => t('Gender'),
            'field_types' => array('text', 'list_text'),
          ),
          'BirthDate' => array(
            'label' => t('Birthday'),
            'field_types' => array('text', 'date', 'datetime', 'datestamp'),
          ),
          'About' => array(
            'label' => t('About me (a short bio)'),
            'field_types' => array('text', 'text_long', 'string', 'string_long'),
          ),
          'HomeTown' => array(
            'label' => t('HomeTown'),
            'field_types' => array('text', 'string'),
          ),
          'Company_name' => array(
            'label' => t('Work history'),
            'field_types' => array('text', 'string'),
          ),
          'ProfileUrl' => array(
            'label' => t('Profile url'),
            'field_types' => array('text', 'string'),
          ),
          'NickName' => array(
            'label' => t('Nick name'),
            'field_types' => array('text', 'string'),
          ),
          'State' => array(
            'label' => t('State'),
            'field_types' => array('text', 'string'),
          ),
          'City' => array(
            'label' => t('City'),
            'field_types' => array('text', 'string'),
          ),
          'LocalCity' => array(
            'label' => t('Local City'),
            'field_types' => array('text', 'string'),
          ),
          'Country_name' => array(
            'label' => t('Country'),
            'field_types' => array('text', 'string'),
          ),
          'LocalCountry' => array(
            'label' => t('Local Country'),
            'field_types' => array('text', 'string'),
          ),
          'ID' => array(
            'label' => t('Social ID'),
            'field_types' => array('text', 'string'),
          ),
          'ThumbnailImageUrl' => array(
            'label' => t('Thumbnail'),
            'field_types' => array('text', 'string'),
          ),
          'PhoneNumber' => array(
            'label' => t('PhoneNumber'),
            'field_types' => array('text', 'string'),
          ),
          '',
        );

        \Drupal::moduleHandler()->alter('field_user_properties', $common);
        ksort($common);
        $common = array_map("unserialize", array_unique(array_map("serialize", $common)));
        return $common;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $sl_config = \Drupal::config('ciam.settings');
        $apiKey = $sl_config->get('api_key');
        $apiSecret = $sl_config->get('api_secret');
        if ($apiKey == '') {
            $apiKey = '';
            $apiSecret = '';
        }

        module_load_include('inc', 'lr_ciam');
        $data = lr_ciam_get_authentication($apiKey, $apiSecret);
        if (isset($data['status']) && $data['status'] != 'status') {
            drupal_set_message($data['message'], $data['status']);
            return FALSE;
        }

        \Drupal\Core\Database\Database::getConnection()->delete('config')
            ->condition('name', 'ciam.settings')->execute();

        $this->config('ciam.settings')
            ->set('interface_label', $form_state->getValue('interface_label'))
            ->set('popup_title', $form_state->getValue('popup_title'))
            ->set('popup_status', $form_state->getValue('popup_status'))
            ->set('popup_error', $form_state->getValue('popup_error'))
            ->set('user_fields', $form_state->getValue('user_fields'))
            ->set('ciam_debug_mode', $form_state->getValue('ciam_debug_mode'))
            ->save();
        if (count(\Drupal::moduleHandler()->getImplementations('add_extra_config_settings')) > 0) {
            // Call all modules that implement the hook, and let them make changes to $variables.
            $data = \Drupal::moduleHandler()->invokeAll('add_extra_config_settings');
        }
        
 
        if (isset($data) && is_array($data)) {
            foreach ($data as $key => $value) {
                $this->config('ciam.settings')
                    ->set($value, $form_state->getValue($value))
                    ->save();
            }
        }
        drupal_set_message(t('Settings have been saved.'), 'status');

        //Clear page cache
        foreach (Cache::getBins() as $service_id => $cache_backend) {
            if ($service_id == 'dynamic_page_cache') {
                $cache_backend->deleteAll();
            }
        }
    }
}
