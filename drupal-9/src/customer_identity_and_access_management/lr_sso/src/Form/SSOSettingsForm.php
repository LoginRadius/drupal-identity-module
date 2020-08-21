<?php

namespace Drupal\lr_sso\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Displays the SSO settings form.
 */
class SSOSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['lr_sso.settings'];
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormId() {
    return 'sso_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('lr_sso.settings');
    // Configuration of which forms to protect, with what challenge.
    $form['sso'] = [
      '#type' => 'details',
      '#title' => $this->t('Single Sign On Settings'),
      '#open' => TRUE,
    ];
    $form['sso']['sso_enable'] = [
      '#type' => 'radios',
      '#title' => $this->t('Do you want to enable Single Sign On (SSO)<a title="This feature allows Single Sign On to be enabled on different sites with common LoginRadius app."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#default_value' => $config->get('sso_enable') ? $config->get('sso_enable') : 0,
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
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

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $sl_config = $this->config('lr_ciam.settings');
    $api_key = $sl_config->get('api_key');
    $api_secret = $sl_config->get('api_secret');
    if ($api_key == '') {
      $api_key = '';
      $api_secret = '';
    }

    $decryt_secret_key = encrypt_and_decrypt( $api_secret, $api_key, $api_key, 'd' );  
    $data = lr_ciam_get_authentication($api_key, $decryt_secret_key);
    if (isset($data['status']) && $data['status'] != 'status') {
      $this->messenger()->addError($this->t($data['message']));
      return FALSE;
    }
    parent::SubmitForm($form, $form_state);
    $this->config('lr_sso.settings')
      ->set('sso_enable', $form_state->getValue('sso_enable'))
      ->save();
  }

}
