<?php

namespace Drupal\lr_identity_experience_framework\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Displays the IdentityExperienceFrameworkSettingsForm settings form.
 */
class IdentityExperienceFrameworkSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['lr_ief.settings'];
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormId() {
    return 'ief-settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $ief_config = $this->config('lr_ief.settings');
    // Configuration of which forms to protect, with what challenge.
    $form['ief'] = [
      '#type' => 'details',
      '#title' => $this->t('Identity Experience Framework Settings'),
      '#open' => TRUE,
    ];

    $form['ief']['lr_ief_enable'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable Identity Experience Framework<a title="From here, Identity Experience Framework functionality can be enabled. It is recommended that SSO should be enabled with the Identity Experience Framework."  style="text-decoration:none; cursor:pointer;"> (<span style="color:#3CF;">?</span>)</a>'),
      '#default_value' => $ief_config->get('lr_ief_enable') ? $ief_config->get('lr_ief_enable') : 0,
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
    $config = $this->config('lr_ciam.settings');
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
    parent::SubmitForm($form, $form_state);
    $this->config('lr_ief.settings')
      ->set('lr_ief_enable', $form_state->getValue('lr_ief_enable'))
      ->save();
  }

}
