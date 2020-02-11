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
class AdvancedSettingsForm extends ConfigFormBase {

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
  
    $form['lr_advanced_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced Options'),
      '#open' => TRUE,
    ];

    $form['lr_advanced_settings']['ciam_instant_link_login'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable Passwordless Link Login<a title="This feature enables Passwordless Link Login on the login form."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#default_value' => $config->get('ciam_instant_link_login') ? $config->get('ciam_instant_link_login') : 'false',
      '#options' => [
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ],
    ];

    if (isset($configOptions) && $configOptions->IsInstantSignin->EmailLink) {
      $form['lr_advanced_settings']['ciam_instant_link_login_email_template'] = [
        '#type' => 'select',
        '#title' => $this->t('Passwordless link login email template<a title="Select the name of Passwordless link login email template which is created in the LoginRadius Dashboard."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
        '#options' => $this->getEmailTemplate($email_templates->EmailTemplates->InstantSignIn),
        '#default_value' => $config->get('ciam_instant_link_login_email_template'),
      ];
    }

    if (isset($configOptions) && $configOptions->IsPhoneLogin) {
    $form['lr_advanced_settings']['ciam_instant_otp_login'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable passwordless OTP login<a title="Turn on, if you want to enable Passwordless OTP login."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#default_value' => $config->get('ciam_instant_otp_login') ? $config->get('ciam_instant_otp_login') : 'false',
      '#options' => [
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ],
    ];

    if (isset($configOptions) && $configOptions->IsInstantSignin->SmsOtp) {
      $form['lr_advanced_settings']['ciam_sms_template_one_time_passcode'] = [
        '#type' => 'select',
        '#title' => $this->t('Passwordless OTP login SMS template<a title="Select the name of Passwordless OTP template which is created in the LoginRadius Dashboard."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
        '#options' => $this->getEmailTemplate($email_templates->SMSTemplates->OneTimePassCode),
        '#default_value' => $config->get('ciam_sms_template_one_time_passcode'),
      ];
    }
  }

    $form['lr_advanced_settings']['ciam_display_password_strength'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable password strength<a title="This feature when enabled, shows the strength bar under the password field on registration form, reset password form and change password form."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#default_value' => $config->get('ciam_display_password_strength') ? $config->get('ciam_display_password_strength') : 'false',
      '#options' => [
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ],
    ];

    $form['lr_advanced_settings']['ciam_notification_timeout_setting'] = [
      '#type' => 'number',
      '#title' => $this->t('Messages timeout setting (in seconds)<a title="Enter the duration (in seconds) to hide response message."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#default_value' => $config->get('ciam_notification_timeout_setting'),
      '#min' => 0,
      '#step' => 1,
    ];  
    
    $form['lr_advanced_settings']['ciam_save_mail_in_db'] = [
      '#type' => 'radios',
      '#title' => $this->t("Do you want to store user's email address in the database?"),
      '#default_value' => $config->get('ciam_save_mail_in_db') ? $config->get('ciam_save_mail_in_db') : 'true',
      '#options' => [
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ],
    ];

    $form['lr_advanced_settings']['ciam_save_name_in_db'] = [
      '#type' => 'radios',
      '#title' => $this->t("Do you want to store user's first and last name as their username in the database?"),
      '#default_value' => $config->get('ciam_save_name_in_db') ? $config->get('ciam_save_name_in_db') : 'true',
      '#options' => [
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ],
    ]; 

    $form['lr_advanced_settings']['ciam_terms_and_condition_html'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Terms and Conditions<a title="Enter the content which needs to be displayed on the registration form."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#default_value' => $config->get('ciam_terms_and_condition_html') ? $config->get('ciam_terms_and_condition_html')['value'] : '',
    ];
   
    $form['lr_advanced_settings']['ciam_custom_options'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom options for LoginRadius interface<a title="This feature allows custom CIAM options to be enabled on the LoginRadius interface."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#id' => 'ciam_custom_options',
      '#rows' => 4,
      '#default_value' => $config->get('ciam_custom_options'),
      '#attributes' => [
        'onchange' => "lrCheckValidJson();",
      ],
      '#description' => $this->t('Insert custom option like commonOptions.usernameLogin = true;'),
    ];    

    $form['lr_advanced_settings']['ciam_registation_form_schema'] = [
      '#type' => 'textarea',
      '#id' => 'ciam_registration_schema',
      '#title' => $this->t('Registration form schema<a title="From here, you can customize the default registration form according to your desired fields, validation rules and field types."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#rows' => 4,
      '#default_value' => $config->get('ciam_registation_form_schema'),
      '#suffix' => "<div class='registation_form_schema' style='display:none;'></div>"
    ];

   // Submit button.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#attributes' => ['class' => ['advancedSettingSave']],
      '#value' => $this->t('Save configurations'),
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
   * Form submit handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $sl_config = \Drupal::config('lr_ciam.settings');
    $api_key = $sl_config->get('api_key');
    $api_secret = $sl_config->get('api_secret');
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

    if (count(\Drupal::moduleHandler()->getImplementations('add_advance_config_settings')) > 0) {
      // Call all modules that implement the hook,
      // and let them make changes to $variables.
      $data = \Drupal::moduleHandler()->invokeAll('add_advance_config_settings');
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
