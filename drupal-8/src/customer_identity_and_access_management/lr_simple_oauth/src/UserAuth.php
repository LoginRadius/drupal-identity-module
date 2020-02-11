<?php

namespace Drupal\lr_simple_oauth;

use Drupal\user\UserAuthInterface;
use Drupal\user\Entity\User;
use LoginRadiusSDK\LoginRadiusException;
use LoginRadiusSDK\CustomerRegistration\Authentication\AuthenticationAPI;

/**
 * Validates user authentication credentials via LoginRadius.
 */
class UserAuth implements UserAuthInterface {

  public $moduleconfig;
  protected $apiKey;
  protected $apiSecret;

  /**
   * Constructs a UserAuth object.
   */
  public function __construct() {
    $this->moduleconfig = \Drupal::config('lr_ciam.settings');
    $this->apiKey = trim($this->moduleconfig->get('api_key'));
    $this->apiSecret = trim($this->moduleconfig->get('api_secret'));
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate($username, $password) {
    // Authenticate the user with the LoginRadius service.
    $data = '{
    "email": "' . $username . '",
    "password": "' . $password . '",
    "securityanswer": ""
    }';

    // Get a user profile using email and password.
    try {
      $authObj = new AuthenticationAPI();
      $result = $authObj->loginByEmail($data);
    }
    catch (LoginRadiusException $e) {
      return FALSE;
    }

    // Check if the user was authenticated with LoginRadius service.
    if (isset($result->access_token) && $result->access_token != '') {
      // Get uid from db using email.
      $query = \Drupal::database()->select('users_field_data', 'u');
      $query->addField('u', 'uid');
      $query->condition('u.mail', $result->Profile->Email[0]->Value);
      $uid = $query->execute()->fetchField();

      // If User exist on LoginRadius but does not exist on Drupal,
      // Then create user on Drupal.
      if (isset($uid) && $uid == '') {
        $fields = [
          'name' => $username,
          'mail' => $result->Profile->Email[0]->Value,
          'init' => $result->Profile->Email[0]->Value,
          'pass' => $password,
          'status' => '1',
        ];
        $new_user = User::create($fields);
        $new_user->save();
        return $new_user->id();
      }
      else {
        return $uid;
      }
    }
    else {
      return FALSE;
    }
  }

}
