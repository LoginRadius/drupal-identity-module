<?php

namespace Drupal\lr_ciam\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Cache\Cache;
use LoginRadiusSDK\CustomerRegistration\Advanced\ConfigurationAPI;
use LoginRadiusSDK\Utility\Functions;

/**
 * Displays the advanced settings form.
 */
class AuthenticationSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['lr_ciam.settings'];
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
    $config = $this->config('lr_ciam.settings');
    $apiKey = trim($config->get('api_key'));
    $apiSecret = trim($config->get('api_secret'));
    // Configuration of which forms to protect, with what challenge.
    if (isset($apiKey) && $apiKey != '' && isset($apiSecret) && $apiSecret != '') {
      try {
        $configObject = new ConfigurationAPI();
        $configOptions = $configObject->getConfigurations();
      }
      catch (LoginRadiusException $e) {
        \Drupal::logger('ciam')->error($e);
      }

      $options = [
        'output_format' => 'json',
      ];
      $decryt_secret_key = encrypt_and_decrypt( $apiSecret, $apiKey, $apiKey, 'd' );    
      $query_array = [
        'apikey' => $apiKey,
        'apisecret' => $decryt_secret_key,
      ];

      try {
        $url = "https://config.lrcontent.com/ciam/appInfo/templates";
        $email_templates = Functions::apiClient($url, $query_array, $options);
      }
      catch (LoginRadiusException $e) {
        \Drupal::logger('ciam')->error($e);
      }
    }
  
    $form['lr_basic_settings'] = [
      '#type' => 'details',      
      '#title' => $this->t('Redirection settings after login<a title="This feature sets the redirection to the page where user will get redirected to post login." style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
    ];  

    $form['lr_basic_settings']['login_redirection'] = [
      '#type' => 'radios',    
      '#default_value' => $config->get('login_redirection') ? $config->get('login_redirection') : 0,
      '#options' => [
        0 => $this->t('Redirect to same page'),
        1 => $this->t('Redirect to profile page'),
        2 => $this->t('Redirect to custom page (If you want user to be redirected to specific URL after login)'),
      ],
    ];

    $form['lr_basic_settings']['login_redirection']['custom_login_url'] = [
      '#type' => 'textfield',
      '#weight' => 50,
      '#default_value' => $config->get('custom_login_url'),
    ];

    $form['lr_email_auth_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Email Authentication Settings'),
    ];

    $form['lr_email_auth_settings']['ciam_prompt_password_on_social_login'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable prompt password on Social login<a title="This feature when enabled, will prompt the user to set the password at the time of login from any social provider." style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#default_value' => $config->get('ciam_prompt_password_on_social_login') ? $config->get('ciam_prompt_password_on_social_login') : 'false',
      '#options' => [
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ],
    ];

    $form['lr_email_auth_settings']['ciam_user_name_login'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable login with username<a title="This feature when enabled, will let the user to login with username."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#default_value' => $config->get('ciam_user_name_login') ? $config->get('ciam_user_name_login') : 'false',
      '#options' => [
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ],
    ];
    
    $form['lr_email_auth_settings']['ciam_ask_email_for_unverified_user_login'] = [
      '#type' => 'radios',
      '#title' => $this->t('Ask for email from unverified user<a title="This feature when enabled, will ask for email every time user tries to login if email is not verified."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#default_value' => $config->get('ciam_ask_email_for_unverified_user_login') ? $config->get('ciam_ask_email_for_unverified_user_login') : 'false',
      '#options' => [
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ],
    ];

    $form['lr_email_auth_settings']['ciam_ask_required_fields_on_traditional_login'] = [
      '#type' => 'radios',
      '#title' => $this->t('Ask for required field on traditional login<a title="This feature when enabled, will ask for newly added required fields on traditional login."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#default_value' => $config->get('ciam_ask_required_fields_on_traditional_login') ? $config->get('ciam_ask_required_fields_on_traditional_login') : 'false',
      '#options' => [
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ],
    ];
    if (isset($email_templates->EmailTemplates)) {
      $form['lr_email_auth_settings']['ciam_welcome_email_template'] = [
        '#title' => $this->t('Welcome email template<a title="Select the name of Welcome email template which is created in the LoginRadius Dashboard."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
        '#type' => 'select',
        '#options' => $this->getEmailTemplate($email_templates->EmailTemplates->Welcome),
        '#default_value' => $config->get('ciam_welcome_email_template'),
      ];
      $form['lr_email_auth_settings']['ciam_email_verification_template'] = [
        '#type' => 'select',
        '#title' => $this->t('Account verification email template<a title="Select the name of Account verification email template which is created in the LoginRadius Dashboard."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
        '#options' => $this->getEmailTemplate($email_templates->EmailTemplates->Verification),
        '#default_value' => $config->get('ciam_email_verification_template'),
      ];
      $form['lr_email_auth_settings']['ciam_reset_password_email_template'] = [
        '#type' => 'select',
        '#title' => $this->t('Reset password email template<a title="Select the name of Reset password email template which is created in the LoginRadius Dashboard."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
        '#options' => $this->getEmailTemplate($email_templates->EmailTemplates->ResetPassword),
        '#default_value' => $config->get('ciam_reset_password_email_template'),
      ];
      if (isset($configOptions) && $configOptions->TwoFactorAuthentication->IsEnabled) {
        $form['lr_email_auth_settings']['ciam_sms_template_2fa'] = [
          '#type' => 'select',
          '#title' => $this->t('Two-factor authentication SMS template<a title="Select the name of Two-factor authentication SMS template which is created in the LoginRadius Dashboard."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
          '#options' => $this->getEmailTemplate($email_templates->SMSTemplates->SecondFactorAuthentication),
          '#default_value' => $config->get('ciam_sms_template_2fa'),
        ];
      }
    }

    if (isset($configOptions) && $configOptions->IsPhoneLogin) {
      $form['lr_phone_auth_settings'] = [
        '#type' => 'details',
        '#title' => $this->t('Phone Authentication Settings'),
      ];
      $form['lr_phone_auth_settings']['ciam_check_phone_no_availability'] = [
        '#type' => 'radios',
        '#title' => $this->t('Check Phone number exist or not<a title="Turn on, if you want to enable Phone Exist functionality."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
        '#default_value' => $config->get('ciam_check_phone_no_availability') ? $config->get('ciam_check_phone_no_availability') : 'false',
        '#options' => [
          'true' => $this->t('Yes'),
          'false' => $this->t('No'),
        ],
      ];
      $form['lr_phone_auth_settings']['ciam_welcome_sms_template'] = [
        '#type' => 'select',
        '#title' => $this->t('Phone welcome SMS template<a title="Select the name of Phone welcome SMS template which is created in the LoginRadius Dashboard."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
        '#options' => $this->getEmailTemplate($email_templates->SMSTemplates->Welcome),
        '#default_value' => $config->get('ciam_welcome_sms_template'),
      ];
      $form['lr_phone_auth_settings']['ciam_sms_template_phone_verification'] = [
        '#type' => 'select',
        '#title' => $this->t('Phone verification SMS template<a title="Select the name of Phone verification SMS template which is created in the LoginRadius Dashboard."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
        '#options' => $this->getEmailTemplate($email_templates->SMSTemplates->Verification),
        '#default_value' => $config->get('ciam_sms_template_phone_verification'),
      ];
      $form['lr_phone_auth_settings']['ciam_sms_template_reset_password'] = [
        '#type' => 'select',
        '#title' => $this->t('Password reset SMS template<a title="Select the name of Password reset SMS template which is created in the LoginRadius Dashboard."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
        '#options' => $this->getEmailTemplate($email_templates->SMSTemplates->ResetPassword),
        '#default_value' => $config->get('ciam_sms_template_reset_password'),
      ];
      $form['lr_phone_auth_settings']['ciam_sms_template_change_phone_no'] = [
        '#type' => 'select',
        '#title' => $this->t('Change phone number SMS template<a title="Select the name of Change phone number SMS template which is created in the LoginRadius Dashboard."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
        '#options' => $this->getEmailTemplate($email_templates->SMSTemplates->ChangePhoneNo),
        '#default_value' => $config->get('ciam_sms_template_change_phone_no'),
      ];
    }

    $form['lr_field_mapping'] = [
      '#type' => 'details',
      '#title' => $this->t('Field Mapping'),
    ];
    $form['lr_field_mapping']['user_fields'] = [
      '#title' => 'user fields',
      '#type' => 'details',
      '#tree' => TRUE,
      '#weight' => 5,
      '#open' => TRUE,
    ];
    $properties = $this->fieldUserProperties();
    $property_options = [];


    foreach ($properties as $property => $property_info) {
      if (isset($property_info['field_types'])) {
        foreach ($property_info['field_types'] as $field_type) {
          $property_options[$field_type][$property] = $property_info['label'];
        }
      }
    }

    $field_defaults = $config->get('user_fields', []);
    $entity_type = 'user';
    foreach (\Drupal::service('entity_field.manager')
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
        $options = array_merge(['' => $this->t('- Do not import -')], $property_options[$field->getType()]);
  
        $form['lr_field_mapping']['user_fields'][$field->getName()] = [
          '#title' => $instance['label'],
          '#type' => 'select',
          '#options' => $options,
          '#default_value' => isset($field_defaults[$field_name]) ? $field_defaults[$field_name] : '',
        ];
      }
      else {
        $form['lr_field_mapping']['user_fields'][$field->getName()] = [
          '#title' => $instance['label'],
          '#type' => 'form_element',
          '#children' => $this->t('This field cannot be mapped with LR field.'),
          '#theme_wrappers' => ['form_element'],
        ];
      }
    }
    
    // Submit button.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Get email template.
   */
  public function getEmailTemplate($template_array) {
    $template = [];
    if (is_array($template_array) || is_object($template_array)) {
      foreach ($template_array as $name) {
        $template[$name] = $name;
      }
    }
    if (empty($template)) {
      $template['default'] = 'default';
    }
    return array_merge(['' => $this->t('- Select -')], $template);
  }

/**
 * Get fields for mapping.
 */
public function fieldUserProperties() {
    try {
      $configObject = new ConfigurationAPI();
      $configOptions = $configObject->getConfigurations();
    }
    catch (LoginRadiusException $e) {
      \Drupal::logger('ciam')->error($e);
    }
    
    $common = [];
    if(isset($configOptions->RegistrationFormSchema)){
        foreach($configOptions->RegistrationFormSchema as $key => $val){
     
          if($val->type != 'email' && $val->type != 'password' && $val->name != 'pin') {

            if($val->type == 'option'){
              $common[str_replace(' ', '', $val->display)]['label'] = $this->t($val->display);
              $common[str_replace(' ', '', $val->display)]['field_types'] = ['text', 'list_string'];          
            } else if($val->type == 'multi'){
              $common[str_replace(' ', '', $val->name)]['label'] = $this->t($val->display);
              $common[str_replace(' ', '', $val->name)]['field_types'] = ['text', 'boolean']; 
            }else if($val->name == 'birthdate'){
              $common[str_replace(' ', '', $val->display)]['label'] = $this->t($val->display);
              $common[str_replace(' ', '', $val->display)]['field_types'] = ['text', 'date', 'datetime', 'datestamp'];              
            } else {
              $common[str_replace(' ', '', $val->display)]['label'] = $this->t($val->display);
              $common[str_replace(' ', '', $val->display)]['field_types'] = ['text', $val->type];         
            }
          }
        }
    }

  
    \Drupal::moduleHandler()->alter('fieldUserProperties', $common);
    ksort($common);
    $common = array_map("unserialize", array_unique(array_map("serialize", $common)));
    return $common;
  }
 
  /**
   * Form submit handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::config('lr_ciam.settings');
    $api_key = $config->get('api_key');
    $api_secret = $config->get('api_secret');
    if ($api_key == '') {
      $api_key = '';
      $api_secret = '';
    }
    $decryt_secret_key = encrypt_and_decrypt( $api_secret, $api_key, $api_key, 'd' );  
    $data = lr_ciam_get_authentication($api_key, $decryt_secret_key);
    if (isset($data['status']) && $data['status'] != 'status') {
      drupal_set_message($data['message'], $data['status']);
      return FALSE;
    }

    Database::getConnection()->delete('config')
      ->condition('name', 'lr_ciam.settings')->execute();

    $this->config('lr_ciam.settings') 
      ->set('user_fields', $form_state->getValue('user_fields'))  
      ->set('login_redirection', $form_state->getValue('login_redirection'))   
      ->set('custom_login_url', $form_state->getValue('custom_login_url'))
      ->save();
    if (count(\Drupal::moduleHandler()->getImplementations('add_extra_config_settings')) > 0) {
      // Call all modules that implement the hook,
      // and let them make changes to $variables.
      $data = \Drupal::moduleHandler()->invokeAll('add_extra_config_settings');
    }

    if (isset($data) && is_array($data)) {
      foreach ($data as $value) {
        $this->config('lr_ciam.settings')
          ->set($value, $form_state->getValue($value))
          ->save();
      }
    }
    drupal_set_message($this->t('Settings have been saved.'), 'status');

    // Clear page cache.
    foreach (Cache::getBins() as $service_id => $cache_backend) {
      if ($service_id == 'dynamic_page_cache') {
        $cache_backend->deleteAll();
      }
    }
  }
}
