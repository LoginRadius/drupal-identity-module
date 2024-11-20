<?php

/**
 * @file
 * Provide views data for lr_ciam.module.
*/

/**
 * Implements hook_views_data_alter().
*/
function lr_ciam_views_data_alter(&$data) {
  $data['users']['lr_ciam_uid'] = [
    'title' => t('LoginRadius profile ID'),
    'help' => t('The LoginRadius profile ID.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
  ];
}