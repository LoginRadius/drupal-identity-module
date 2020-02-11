<?php

namespace Drupal\lr_ciam\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;

/**
 * Displays the settings form.
 */
class CiamSettingsForm extends ConfigFormBase {

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
    return 'ciam_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('lr_ciam.settings');
    $form['#attached']['library'][] = 'user/drupal.user.admin';
    // Configuration of which forms to protect, with what challenge.
   
    if ($config->get('api_key') != '' && $config->get('api_secret')) {
      $is_phone_login = \Drupal::service('session')->get('is_phone_login', []);
      if (isset($is_phone_login) &&  $is_phone_login) {
        $form['phone_warning_block'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Important Note:'),
          '#description' => $this->t('If only the Phone Id Login options is enabled for the App, a random Email Id will be generated if a user registered using the PhoneID. Format of random email id is: "randomid+timestamp@yourdomain.com"'),
        ];
      }
    }

    $form['lr_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('LoginRadius API Configurations'),
      '#description' => $this->t('To access the loginradius web service please enter the credentials below ( <a href="https://www.loginradius.com/docs/api/v2/admin-console/platform-security/api-key-and-secret/" target="_blank">How to get it?</a> )'),
      '#open' => TRUE,
    ];

    $form['lr_settings']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('LoginRadius API Key'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    $decrypted_secret_key = encrypt_and_decrypt( $config->get('api_secret'), $config->get('api_key'), $config->get('api_key'), 'd' );
    $form['lr_settings']['api_secret'] = [
      '#type' => 'textfield',         
      "#suffix" => '<div id="ciam_show_button">Show</div>',
      '#title' => $this->t('LoginRadius API Secret'),     
      '#default_value' => $decrypted_secret_key,
      '#id' => 'secret',
      '#required' => TRUE,
    ]; 
    
    // Submit button.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $data = lr_ciam_get_authentication($form_state->getValue('api_key'), $form_state->getValue('api_secret'));
    $configOptions = lr_ciam_get_configuration($form_state->getValue('api_key'));
    \Drupal::service('session')->set('is_phone_login', isset($configOptions->IsPhoneLogin) ? $configOptions->IsPhoneLogin : '');
    if (isset($data['status']) && $data['status'] != 'status') {
        drupal_set_message($data['message'], $data['status']);
        return FALSE;
    }

    $encrypted_secret_key = encrypt_and_decrypt( $form_state->getValue('api_secret'), $form_state->getValue('api_key'), $form_state->getValue('api_key'), 'e' );     
    parent::SubmitForm($form, $form_state);
    $this->config('lr_ciam.settings')
 
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('api_secret', $encrypted_secret_key)
      ->set('api_request_signing', (isset($configOptions['response']->ApiRequestSigningConfig->IsEnabled) && $configOptions['response']->ApiRequestSigningConfig->IsEnabled) ? 'true' : 'false')
      ->set('sso_site_name', isset($configOptions['response']->AppName) ? $configOptions['response']->AppName : '')
      ->set('custom_hub_domain', isset($configOptions['response']->CustomDomain) ? $configOptions['response']->CustomDomain : '')
      ->save();

    // Clear page cache.
    foreach (Cache::getBins() as $service_id => $cache_backend) {
      if ($service_id == 'dynamic_page_cache') {
        $cache_backend->deleteAll();
      }
    }
  }
}
