<?php

module_load_include('php', 'lr_ciam', 'LoginRadiusSDK/Utility/Functions');
module_load_include('php', 'lr_ciam', 'LoginRadiusSDK/LoginRadiusException');
module_load_include('php', 'lr_ciam', 'LoginRadiusSDK/Clients/IHttpClientInterface');
module_load_include('php', 'lr_ciam', 'LoginRadiusSDK/CustomerRegistration/Advanced/ConfigurationAPI');

use \LoginRadiusSDK\Utility\Functions;
use \LoginRadiusSDK\LoginRadiusException;
use \LoginRadiusSDK\Clients\IHttpClientInterface;
use \LoginRadiusSDK\CustomerRegistration\Advanced\ConfigurationAPI;

/**
 * create user profile2 fields.
 *
 * @param object $account User account information
 * @param object $userprofile User profile data that you got from social network
 * @param array $form Nested array of form elements that comprise the form.
 * @param array $form_state A keyed array containing the current state of the form.
 */
function lr_field_mapping_ciam_create_profile_user($account, $userprofile, $form, $form_state) {
  $field_map = variable_get('lr_ciam_profile_fields', array());

  foreach ($field_map as $key => $value) {
    $mapped_value[$key] = array_values($value);
  }
  $field_convert_info = lr_field_mapping_ciam_field_convert_info();
  foreach (field_info_instances('profile2') as $field_name => $instances) {
    if (!empty($mapped_value[$field_name]) && strlen(implode($mapped_value[$field_name])) != 0) {
      $profile = profile2_load_by_user($account->uid, $field_name);

      if (!$profile) {
        $profile = profile_create(array(
          'type' => $field_name,
          'uid' => $account->uid
        ));
      }

      foreach ($instances as $instance => $array) {
        $field = field_info_field($array['field_name']);

        if (isset($field_map[$field_name][$array['field_name']]) && isset($field_convert_info[$field['type']]['callback'])) {
          $callback = $field_convert_info[$field['type']]['callback'];
          $lr_field_mapping_property_name = $field_map[$field_name][$array['field_name']];
          $value = $callback($lr_field_mapping_property_name, $userprofile, $field, $array);
          $profile->{$array['field_name']}[LANGUAGE_NONE][0]['value'] = $value; // department info got from 3rd party site  
        }
        $form_state['values']['profile_' . $field_name] = array();
        $form = array();
        $form['#parents'] = array();
        field_attach_submit('profile2', $profile, $form, $form_state); // attach $profile to profile2 submit
        $profile->bundle = $field_name; // main is the profile type which is created in step 3
        profile2_save($profile);
      }
    }
  }
}

/**
 * Function that adding field data.
 *
 * @param array $form_state A keyed array containing the current state of the form.
 * @param object $userprofile User profile data that you got from social network
 */
function lr_field_mapping_ciam_create_user(&$form_state, $userprofile, $account = '') {
  $field_map = variable_get('lr_ciam_user_fields', array());
  $field_convert_info = lr_field_mapping_ciam_field_convert_info();
  $instances = field_info_instances('user', 'user');

  foreach ($instances as $field_name => $instance) {
    $field = field_info_field($instance['field_name']); 

    if (isset($field_map[$field_name]) && isset($field_convert_info[$field['type']]['callback'])) {
      $callback = $field_convert_info[$field['type']]['callback'];
      $lr_field_mapping_property_name = $field_map[$field_name];
    
      if ($value = $callback($lr_field_mapping_property_name, $userprofile, $field, $instance)) {         
        $form_state[$field_name][LANGUAGE_NONE][0] = $value;
      }
    }
  }
}


/**
 * Function that Update field data.
 *
 * @param array $form_state A keyed array containing the current state of the form.
 * @param object $userprofile User profile data that you got from social network
 */
function lr_field_mapping_ciam_update_user(&$form_state, $userprofile, $account = '') {

  $field_map = variable_get('lr_ciam_user_fields', array());
  $field_convert_info = lr_field_mapping_ciam_field_convert_info();
  $instances = field_info_instances('user', 'user');

  foreach ($instances as $field_name => $instance) {
    $field = field_info_field($instance['field_name']);
   
    if (isset($field_map[$field_name]) && isset($field_convert_info[$field['type']]['callback'])) {
      $callback = $field_convert_info[$field['type']]['callback'];
      $lr_field_mapping_property_name = $field_map[$field_name];
 
      if ($value = $callback($lr_field_mapping_property_name, $userprofile, $field, $instance)) {    
        try {
          db_update('field_data_'.$field_name)
              ->fields(array(
                $field_name.'_value' => $value['value'],
              ))
              ->condition('entity_id', $account->uid)
              ->execute();
        }
        catch (Exception $e) {
            watchdog('loginradius_logging', $e, array(), WATCHDOG_ERROR);
        }
      }
    }
  }
}

/**
 * Function that provide a callback for data map.
 *
 * @return array Get array of label and callback through which we can field data
 */
function lr_field_mapping_ciam_field_convert_info() {
  $convert_info = array(
    'text' => array(
      'label' => t('Text'),
      'callback' => 'lr_field_mapping_ciam_field_convert_text',
    ),
    'text_long' => array(
      'label' => t('Long text'),
      'callback' => 'lr_field_mapping_ciam_field_convert_text',
    ),
    'list_text' => array(
      'label' => t('List (\'text\')'),
      'callback' => 'lr_field_mapping_ciam_field_convert_list',
    ),    
    'list_boolean' => [
      'label' => t('List (\'boolean\')'),
      'callback' => 'lr_field_mapping_ciam_field_convert_bool',
    ],
    'datetime' => array(
      'label' => t('Date'),
      'callback' => 'lr_field_mapping_ciam_field_convert_date',
    ),
    'date' => array(
      'label' => t('Date'),
      'callback' => 'lr_field_mapping_ciam_field_convert_date',
    ),
    'datestamp' => array(
      'label' => t('Date'),
      'callback' => 'lr_field_mapping_ciam_field_convert_date',
    ),
  );

  drupal_alter('lr_field_mapping_ciam_field_convert_info', $convert_info);
  return $convert_info;
}

/**
 * Convert text and text_long data.
 *
 * @param string $lr_field_mapping_property_name User profile property name thorugh which data mapped
 * @param object $userprofile User profile data that you got from social network
 * @param string User field name stored in database
 * @param string $instance Field instance
 * @return array  Contain value of field map data
 */
function lr_field_mapping_ciam_field_convert_text($lr_field_mapping_property_name, $userprofile, $field, $instance) {
  $value = NULL;
  if(!empty($lr_field_mapping_property_name) && !(strpos($lr_field_mapping_property_name, 'ciam|') !== false)){

  if (isset($userprofile->$lr_field_mapping_property_name)) {
    if (is_string($userprofile->$lr_field_mapping_property_name)) {
      $value = $userprofile->$lr_field_mapping_property_name;
    }
    elseif (is_object($userprofile->$lr_field_mapping_property_name)) {
      $object = $userprofile->$lr_field_mapping_property_name;
      if (isset($object->Name)) {
        $value = $object->Name;
      }
    }
    elseif (is_bool($userprofile->$lr_field_mapping_property_name)) {  
      $value = $userprofile->$lr_field_mapping_property_name;    
      $value ? ['value' => (isset($value) && $value == 'true') ? true : '0'] : NULL;      
    }
  }

  return $value ? array('value' => $value) : NULL;
  }
}

/**
 * Convert boolean data.
 *
 * @param string $lr_field_mapping_property_name User profile property name thorugh which data mapped
 * @param object $userprofile User profile data that you got from social network
 * @param string User field name stored in database
 * @param string $instance Field instance
 * @return array  Contain value of field map data
 */

function lr_field_mapping_ciam_field_convert_bool($lr_field_mapping_property_name, $userprofile, $field, $instance) {
  $value = NULL;
 
  if(!empty($lr_field_mapping_property_name) && !(strpos($lr_field_mapping_property_name, 'ciam|') !== false)){
    $lr_field_mapping_property_name =  explode("_", $lr_field_mapping_property_name); 

    if(is_array($lr_field_mapping_property_name)){
      if(isset($lr_field_mapping_property_name[0]) && $lr_field_mapping_property_name[0] == 'cf') {
         $name = $lr_field_mapping_property_name[1];
         $value = (isset($userprofile->CustomFields->$name) ? $userprofile->CustomFields->$name : '') ;       
       }
     }
     elseif (is_string($userprofile->$lr_field_mapping_property_name)) {
             $value = $userprofile->$lr_field_mapping_property_name;
     }
     elseif (is_object($userprofile->$lr_field_mapping_property_name)) {
           $object = $userprofile->$lr_field_mapping_property_name;
           if (isset($object->name)) {
             $value = $object->name;
           }
     }

    return $value ? ['value' => (isset($value) && $value == 'true') ? true : '0'] : NULL;    
  }
}

/**
 * Convert list data.
 *
 * @param string $lr_field_mapping_property_name User profile property name thorugh which data mapped
 * @param object $userprofile User profile data that you got from social network
 * @param string $field User field name stored in database
 * @param string $instance Field instance
 * @return array Contain value of field map data
 */
function lr_field_mapping_ciam_field_convert_list($lr_field_mapping_property_name, $userprofile, $field, $instance) {
  if(!empty($lr_field_mapping_property_name) && !(strpos($lr_field_mapping_property_name, 'ciam|') !== false)){
  if (!isset($userprofile->$lr_field_mapping_property_name) && !is_string($userprofile->$lr_field_mapping_property_name)) {
    return;
  }

  $options = list_allowed_values($field);
  $best_match = 0.0;
  $match_sl = strtolower($userprofile->$lr_field_mapping_property_name);

  foreach ($options as $key => $option) {
    $option = trim($option);
    $match_option = strtolower($option);
    $this_match = 0;
    similar_text($match_option, $match_sl, $this_match);

    if ($this_match > $best_match) {
      $best_match = $this_match;
      $best_key = $key;
    }
  }

  return isset($best_key) ? array('value' => $best_key) : NULL;
  }
}

/**
 * Convert date data.
 *
 * @param $lr_field_mapping_property_name User profile property name thorugh which data mapped
 * @param $userprofile User profile data that you got from social network
 * @param $field User field name stored in database
 * @param $instance Field instance
 * @return array Contain value of field map data
 *
 */
function lr_field_mapping_ciam_field_convert_date($lr_field_mapping_property_name, $userprofile, $field, $instance) {
if(!empty($lr_field_mapping_property_name) && !(strpos($lr_field_mapping_property_name, 'ciam|') !== false)){
  if (isset($userprofile->$lr_field_mapping_property_name)) {
    $value = NULL;
    $sldate = explode('/', $userprofile->$lr_field_mapping_property_name);

    if (count($sldate) == 3) {
      $date = new DateObject($userprofile->$lr_field_mapping_property_name);

      if (date_is_date($date)) {
        $format = $field['type'] == 'datestamp' ? DATE_FORMAT_UNIX : DATE_FORMAT_ISO;
        $value = array(
          'value' => $date->format($format, TRUE),
          'date_type' => $field['type'],
        );
      }
    }

    return $value;
  }
  }
}

/**
 * Return list of social user properties.
 *
 * @return array Get all list of user properties that will show at field mapping
 */
function lr_field_mapping_ciam_user_properties() {

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
              $common[str_replace(' ', '', $val->display)]['label'] = t($val->display);
              $common[str_replace(' ', '', $val->display)]['field_types'] = ['list_text'];          
            }else if($val->type == 'string' || $val->type == 'text'){
              $common[str_replace(' ', '', $val->display)]['label'] = t($val->display);
              $common[str_replace(' ', '', $val->display)]['field_types'] = ['text']; 
            } else if($val->type == 'multi'){
              $common[str_replace(' ', '', $val->name)]['label'] = t($val->display);
              $common[str_replace(' ', '', $val->name)]['field_types'] = ['list_boolean']; 
            }else if($val->name == 'birthdate'){
              $common[str_replace(' ', '', $val->display)]['label'] = t($val->display);
              $common[str_replace(' ', '', $val->display)]['field_types'] = ['date', 'datetime', 'datestamp'];              
            } else {
              $common[str_replace(' ', '', $val->display)]['label'] = t($val->display);
              $common[str_replace(' ', '', $val->display)]['field_types'] = [$val->type];         
            }
          }
        }
    }
  
    drupal_alter('lr_field_mapping_ciam_user_properties', $common);
    ksort($common);
    $common = array_map("unserialize", array_unique(array_map("serialize", $common)));
    return $common;
  }


/**
 * Fill user fields and profile fields to register form.
 *
 * @param $form Nested array of form elements that comprise the form.
 * @param $userprofile User profile data that you got from social network
 */
function lr_field_mapping_show_ciam_profile_field_to_register_form(&$form, $userprofile) {
  $field_map = variable_get('lr_ciam_user_fields', array());
  $field_convert_info = lr_field_mapping_ciam_field_convert_info();
  $instances = field_info_instances('user', 'user');

  foreach ($instances as $field_name => $instance) {
    $field = field_info_field($instance['field_name']);

    if (isset($field_map[$field_name]) && isset($field_convert_info[$field['type']]['callback'])) {
      $callback = $field_convert_info[$field['type']]['callback'];
      $lr_field_mapping_property_name = $field_map[$field_name];

      if ($value = $callback($lr_field_mapping_property_name, $userprofile, $field, $instance)) {
        $form[$field_name][LANGUAGE_NONE][0]['value']['#default_value'] = $value;
      }
    }
  }

  if (module_exists('profile2')) {
    $field_map = variable_get('lr_ciam_profile_fields', array());
    $field_convert_info = lr_field_mapping_ciam_field_convert_info();

    foreach (field_info_instances('profile2') as $field_name => $instances) {
      foreach ($instances as $instance => $array) {
        $field = field_info_field($array['field_name']);

        if (isset($field_map[$field_name][$array['field_name']]) && isset($field_convert_info[$field['type']]['callback'])) {
          $callback = $field_convert_info[$field['type']]['callback'];
          $lr_field_mapping_property_name = $field_map[$field_name][$array['field_name']];

          if ($value = $callback($lr_field_mapping_property_name, $userprofile, $field, $array)) {
            $form['profile_' . $field_name][$array['field_name']][LANGUAGE_NONE][0]['value']['#default_value'] = $value;
          }
        }
      }
    }
  }
}