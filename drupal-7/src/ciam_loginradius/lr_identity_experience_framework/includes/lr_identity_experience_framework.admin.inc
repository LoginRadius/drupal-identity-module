<?php
/**
 * @file
 * Admin page callback file for the advance_social_sharing module.
 */
module_load_include('inc', 'lr_ciam', 'includes/lr_ciam.admin');
/**
 * Function that build an abstract representation of a HTML form for Social Sharing settings.
 *
 * @global string $base_url URL to home page.
 * @param array $form Nested array of form elements that comprise the form.
 * @param array $form_state A keyed array containing the current state of the form.
 * @return array A renderable form array for the respective request.
 */
function lr_ief_settings_form($form, &$form_state) {
  drupal_add_css(drupal_get_path('module', 'lr_ciam') . '/css/lr_ciam.admin.min.css');
  $ief_group = 'ief';
  $form[$ief_group] = array(
    '#type' => 'fieldset',
    '#title' => t('Identity Experience Framework Settings'),
    '#id' => 'setup',
    '#collapsible' => TRUE,
  );
  $form[$ief_group]['lr_ief_enable'] = array(
    '#type' => 'radios',
    '#title' => t('Enable Identity Experience Framework<a title="From here, Identity Experience Framework functionality can be enabled. It is recommended that SSO should be enabled with the Identity Experience Framework." class="lrTooltip"> (<span class="lrTooltipColor">?</span>)</a>'),
    '#default_value' => variable_get('lr_ief_enable', 0),
    '#options' => array(
      1 => t('Yes'),
      0 => t('No'),
    )
  );  

  $form['#submit'][] = 'lr_ief_form_submit';
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration'),
  );
  return $form;
}

/**
 * Submit ief settings.
 *
 * @param array $form Nested array of form elements that comprise the form.
 * @param array $form_state A keyed array containing the current state of the form
 * @return bool
 */
function lr_ief_form_submit($form, &$form_state) {
  if (!lr_ciam_save_settings($form_state)) {
    return FALSE;
  }
}
