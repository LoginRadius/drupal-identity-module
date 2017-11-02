<?php

/**
 * @file
 * Contains \Drupal\captcha\Form\CaptchaSettingsForm.
 */

namespace Drupal\lr_auth\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;

/**
 * Displays the auth settings form.
 */
class AuthSettingsForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return ['auth.settings'];
    }

    /**
     * Implements \Drupal\Core\Form\FormInterface::getFormID().
     */
    public function getFormId() {
        return 'auth_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('auth.settings');
        // Configuration of which forms to protect, with what challenge.
        $form['auth'] = [
          '#type' => 'details',
          '#title' => $this->t('User Authentication Settings'),
          '#open' => TRUE,
        ];
        $form['auth']['auth_enable'] = [
          '#type' => 'radios',
          '#title' => t('Do you want to enable authentication option?'),
          '#default_value' => $config->get('auth_enable') ? $config->get('auth_enable') : 0,
          '#options' => array(
            1 => t('Yes'),
            0 => t('No'),
          ),
        ];
        $form['auth']['auth_secret'] = [
          '#type' => 'textfield',
          '#title' => t('Please enter your oauth secret key.'),
          '#default_value' => $config->get('auth_secret'),
        ];
        // Submit button.
        $form['actions'] = ['#type' => 'actions'];
        $form['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => t('Save configuration'),
        ];
        
        return parent::buildForm($form, $form_state);
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
        parent::SubmitForm($form, $form_state);
        $this->config('auth.settings')
            ->set('auth_enable', $form_state->getValue('auth_enable'))
            ->set('auth_secret', $form_state->getValue('auth_secret'))
            ->save();
    }
}
