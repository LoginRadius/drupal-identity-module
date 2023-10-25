<?php

namespace Drupal\lr_ciam;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\user\Entity\User;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;
use LoginRadiusSDK\LoginRadiusException;
use LoginRadiusSDK\CustomerRegistration\Authentication\AuthenticationAPI;
use LoginRadiusSDK\CustomerRegistration\Account\AccountAPI;

/**
 * Returns responses for Simple FB Connect module routes.
 */
class CiamUserManager {

  public $moduleconfig;
  protected $connection;
  protected $apiSecret;
  protected $apiKey;
  protected $apirequestsigning;
  protected $redirectMiddleware;

  /**
   * Here LR configuration fetch from database and used in functions.
   */
  public function __construct() {
    $this->connection = Database::getConnection();
    $this->moduleconfig = \Drupal::config('lr_ciam.settings');
    $this->apiSecret = trim((string) $this->moduleconfig->get('api_secret'));
    $this->apiKey = trim((string) $this->moduleconfig->get('api_key'));
    $this->apirequestsigning = trim((string) $this->moduleconfig->get('api_request_signing'));
    $this->redirectMiddleware = \Drupal::service('lr_ciam.http_middleware');
  }

  /**
   * Update UID in database table.
   *
   * @param string $ciam_uid
   *   Uid get from user profile data.
   * @param object $user_id
   *   User id get from drupal database.
   *
   * @return mixed
   */
  public function lrCiamUpdateUserTable($ciam_uid, $user_id) {
    try {
      $this->connection->update('users')
        ->fields(['lr_ciam_uid' => $ciam_uid])
        ->condition('uid', $user_id)
        ->execute();
    }
    catch (Exception $e) {
    }
  }

  /**
   * User delete.
   *
   * @param object $user_id
   *   User id get from database.
   *
   * @return mixed
   */
  public function userDelete($user_id) {
    try {
      $accountObj = new AccountAPI();
      return $accountObj->deleteAccountByUid($user_id);
    }
    catch (LoginRadiusException $e) {
    }
  }

  /**
   * Get CIAM UID from users table.
   *
   * @param object $user_id
   *   User id get from drupal database.
   *
   * @return mixed
   */
  public function lrCiamGetCiamUid($user_id) {
    $query = \Drupal::database()->select('users', 'u');
    $query->addField('u', 'lr_ciam_uid');
    $query->condition('u.uid', $user_id);
    $uid = $query->execute()->fetchField();
    return $uid;
  }

  /**
   * Get username from users_field_data table.
   *
   * @param object $user_id
   *   User id get from drupal database.
   *
   * @return username
   */
  public function lrCiamGetCiamUname($user_id) {
    $query = \Drupal::database()->select('users_field_data', 'u');
    $query->addField('u', 'name');
    $query->condition('u.uid', $user_id);
    $uname = $query->execute()->fetchField();
    return $uname;
  }

  /**
   * Block user at CIAM.
   */
  public function lrCiamBlockUser($uid) {
    try {
      $accountObj = new AccountAPI();
      $data = ["IsActive" => "false"];
      return $accountObj->updateAccountByUid($data, $uid);
    }
    catch (LoginRadiusException $e) {
      \Drupal::logger('ciam')->error($e);
    }
  }

  /**
   * Unblock user at CIAM.
   */
  public function lrCiamUnblockUser($uid) {
    try {
      $accountObj = new AccountAPI();
      $data = ["IsActive" => "true"];
      return $accountObj->updateAccountByUid($data, $uid);
    }
    catch (LoginRadiusException $e) {
      \Drupal::logger('ciam')->error($e);
    }
  }

  /**
   * Delete mapped provider.
   */
  public function deleteMapUser($aid) {
    return $this->connection->delete('loginradius_mapusers')
      ->condition('user_id', $aid)
      ->execute();
  }

  /**
   * Get user profile data.
   */
  public function getUserData($userprofile) {
    $userprofile->Email_value = (count($userprofile->Email) > 0 ? $userprofile->Email[0]->Value : '');
    $userprofile->Company_name = (isset($userprofile->Company->Name) ? $userprofile->Company->Name : '');
    $userprofile->Country_name = (isset($userprofile->Country->Name) ? $userprofile->Country->Name : '');
    $userprofile->PhoneNumber = (isset($userprofile->PhoneNumbers) && count($userprofile->PhoneNumbers) > 0 ? $userprofile->PhoneNumbers[0]->PhoneNumber : '');
    return $userprofile;
  }

  /**
   * Insert social provider data.
   */
  public function insertSocialData($user_id, $provider_id, $provider) {
    $this->connection->insert('loginradius_mapusers')
      ->fields([
        'user_id' => $user_id,
        'provider' => $provider,
        'provider_id' => $provider_id,
      ])
      ->execute();
  }

  /**
   * Get user by mail.
   */
  public function getUserByEmail($email) {
    return user_load_by_mail($email);
  }

  /**
   * Removed unescaped character.
   */
  public function removeUnescapedChar($str) {
    $in_str = str_replace([
      '<',
      '>',
      '&',
      '{',
      '}',
      '*',
      '/',
      '(',
      '[',
      ']',
      '!',
      ')',
      '&',
      '*',
      '#',
      '$',
      '%',
      '^',
      '|',
      '?',
      '+',
      '=',
      '"',
      ',',
    ], [''], $str);
    $cur_encoding = mb_detect_encoding($in_str);
    if ($cur_encoding == "UTF-8" && mb_check_encoding($in_str, "UTF-8")) {
      return $in_str;
    }
    else {
      return mb_convert_encoding($in_str,"UTF-8");
    }
  }

  /**
   * Check exist username.
   *
   * @param object $userprofile
   *
   * @return string Username of user
   */
  public function checkUserName($userprofile) {
    $user_name = $this->usernameOption($userprofile);    
    $index = 0;

    while (TRUE) {
      if (user_load_by_name($user_name)) {
        $index++;
        $user_name = $user_name . $index;
      }
      else {
        break;
      }
    }
    $data['username'] = $this->removeUnescapedChar($user_name);
    $data['fname'] = (!empty($userprofile->FirstName) ? $userprofile->FirstName : '');
    $data['lname'] = (!empty($userprofile->LastName) ? $userprofile->LastName : '');

    if (empty($data['fname'])) {
      $data['fname'] = $this->getDisplayName($userprofile);
    }

    if (empty($data['lname'])) {
      $data['lname'] = $this->getDisplayName($userprofile);
    }
    return $data;
  }

    /**
   * Get username from user profile data.
   *
   * @param object $userprofile
   *   User profile information.
   *
   * @return string Username of user
   */
  public function usernameOption($userprofile) {
    if (isset($userprofile->Provider) && $userprofile->Provider == 'Email' && isset($userprofile->UserName) && $userprofile->UserName != '') {
      $username = $userprofile->UserName;   
    }
    elseif (!empty($userprofile->FirstName) && !empty($userprofile->LastName)) {
      $username = $userprofile->FirstName . ' ' . $userprofile->LastName;
    }
    elseif (!empty($userprofile->Email_value) && (isset($userprofile->Random_email_generated) && !$userprofile->Random_email_generated)) {
      $user_name = explode('@', $userprofile->Email_value);
      $username = $user_name[0];
    }
    else {
      $username = $this->getDisplayName($userprofile);
    }
    return $username;
  }

  
  /**
   * Get username
   */
  public function getDisplayName($userprofile) {
    if (!empty($userprofile->FullName)) {
      $username = $userprofile->FullName;
    }
    elseif (!empty($userprofile->ProfileName)) {
      $username = $userprofile->ProfileName;
    }
    elseif (!empty($userprofile->NickName)) {
      $username = $userprofile->NickName;
    }
    elseif (!empty($userprofile->PhoneId)) {
      $username = str_replace([
        "-",
        "+",
      ], "", $userprofile->PhoneId);
    }
    elseif (!empty($userprofile->Email_value)) {
      $user_name = explode('@', $userprofile->Email_value);
      $username = $user_name[0];
    }
    else {
      $username = $userprofile->ID;
    }
    return $username;
  }


  /**
   * Check exist username on update profile.
   *
   * @param object $userprofile
   *
   * @return string Username of user
   */

  public function getUserNameOnUpdateProfile($userprofile) {
    $user_name = $this->usernameOption($userprofile);    
  
    $data['username'] = $this->removeUnescapedChar($user_name);
    $data['fname'] = (!empty($userprofile->FirstName) ? $userprofile->FirstName : '');
    $data['lname'] = (!empty($userprofile->LastName) ? $userprofile->LastName : '');

    if (empty($data['fname'])) {
      $data['fname'] = $this->getDisplayName($userprofile);
    }

    if (empty($data['lname'])) {
      $data['lname'] = $this->getDisplayName($userprofile);
    }
    return $data;
  }

  /**
   * Provider login to user.
   *
   * @param object $new_user
   * @param object $userprofile
   * @param boolean status
   *
   * @return mixed
   */
  public function provideLogin($new_user, $userprofile, $status = FALSE) {
    if (isset($userprofile) && !empty($userprofile)) {
      if (is_array($userprofile) || is_object($userprofile)) {
        $query = \Drupal::database()->select('loginradius_mapusers', 'lu');
        $query->addField('lu', 'user_id');
        $query->condition('lu.user_id', $new_user->id());
        $query->condition('lu.provider_id', $userprofile->ID);
        $check_aid = $query->execute()->fetchField();
        if (isset($check_aid) && !$check_aid) {
          $this->insertSocialData($new_user->id(), $userprofile->ID, $userprofile->Provider);
        }
      }
    }
    

    if ($new_user->isActive()) {
      $url = '';
      $isNew = FALSE;

      if (!$new_user->isNew()) {
        $this->fieldCreateUserObject($new_user, $userprofile);
        $new_user->save();

        $this->downloadProfilePic($userprofile->ImageUrl, $userprofile->ID, $new_user);
      }

      \Drupal::service('session')->migrate();
      \Drupal::service('session')->set('emailVerified', FALSE);
      if (isset($userprofile->EmailVerified)) {
        \Drupal::service('session')->set('emailVerified', $userprofile->EmailVerified);
      }

      if (\Drupal::moduleHandler()->moduleExists('lr_ciam')) {
        if (isset($userprofile->Provider) && $userprofile->Provider == 'Email' && isset($userprofile->UserName) && $userprofile->UserName != '') {
          $user_name = $userprofile->UserName;
        }

        $user_manager = \Drupal::service('lr_ciam.user_manager');
        $db_uname = $user_manager->lrCiamGetCiamUname($new_user->id());

        if (isset($db_uname) && $db_uname != '') {
          if (isset($user_name) && $user_name != '' && $db_uname != $user_name) {
              try {
              $this->connection->update('users_field_data')
                ->fields(['name' => $user_name])
                ->condition('uid', $new_user->id())
                ->execute();
            }
            catch (Exception $e) {
              \Drupal::logger('ciam')->error($e);
              \Drupal::messenger()->addError($e->getMessage());
            }
          }
        }
      }

      user_login_finalize($new_user);
      if ($status) {        
        \Drupal::messenger()->addStatus(t('You are now logged in as %username.', ['%username' => $new_user->getDisplayName()]));
      }
      else {        
        \Drupal::messenger()->addStatus(t('You are now logged in as %username.', ['%username' => $new_user->getDisplayName()]));
      }
      return $this->redirectUser($url);
    }
    else {
      
      $sso_config = \Drupal::config('lr_sso.settings');
      \Drupal::messenger()->addError(t('You are either blocked, or have not activated your account. Please check your email.'));
      if ($sso_config->get('sso_enable') == 1) {
        $domain = Url::fromRoute('<front>')->setAbsolute()->toString();
        $redirectUrl = $domain . 'user/logout';
        $response = new TrustedRedirectResponse($redirectUrl);
        return $this->redirectMiddleware->setRedirectResponse($response);

      }else {
        $domain = Url::fromRoute('<front>')->setAbsolute()->toString();
        $redirectUrl = $domain . 'user/login';
        $response = new TrustedRedirectResponse($redirectUrl);
        return $this->redirectMiddleware->setRedirectResponse($response);
      }
    }
}
  /**
   * Check provider id exist or not.
   *
   * @param string $pid
   *   provider id.
   *
   * @return provider id
   */
  public function checkProviderId($pid) {
   // $query = db_select('users', 'u');
    $query = \Drupal::database()->select('users', 'u');
    $query->join('loginradius_mapusers', 'lu', 'u.uid = lu.user_id');
    $query->addField('u', 'uid');
    $query->condition('lu.provider_id', $pid);
    $check_aid = $query->execute()->fetchField();
    return $check_aid;
  }

  /**
   * Redirect user.
   *
   * @param string $variable_path
   *
   * @return mixed
   */
  public function redirectUser($variable_path = '') {
    $user = \Drupal::currentUser();
    $variable_path = (!empty($variable_path) ? $variable_path : 'login_redirection');
    $variable_custom_path = (($variable_path == 'login_redirection') ? 'custom_login_url' : ''); 
    $request_uri = \Drupal::request()->getRequestUri();
    
    if (strpos($request_uri, 'redirect_to') !== FALSE) {
      // Redirect to front site.
      $redirectUrl = \Drupal::request()->query->get('redirect_to');
      $response = new TrustedRedirectResponse($redirectUrl);
      return $this->redirectMiddleware->setRedirectResponse($response);
    }
    elseif ($this->moduleconfig->get($variable_path) == 1) {
      // Redirect to profile.
      $response = new RedirectResponse($user->id() . '/edit');
      return $this->redirectMiddleware->setRedirectResponse($response);
    }
    elseif ($this->moduleconfig->get($variable_path) == 2) {
      // Redirect to custom page.
      $custom_url = $this->moduleconfig->get($variable_custom_path);
      if (!empty($custom_url)) {
        $response = new RedirectResponse($custom_url);
        return $this->redirectMiddleware->setRedirectResponse($response);
      }
      else {
        return new RedirectResponse(Url::fromRoute('<front>')->toString());
      }
    }
    else {
      // Redirect to same page.     
      $referer_url = \Drupal::service('session')->get('referer_url', []);
      if (!empty($referer_url)) {
        $response = new RedirectResponse($referer_url);
        return $this->redirectMiddleware->setRedirectResponse($response);
      }
      else {
        $destination = (\Drupal::destination()->getAsArray());
        if (isset($destination['destination']) && $destination['destination'] != '') {
          $response = new RedirectResponse($destination['destination']);
        }
        else {
          $response = new RedirectResponse(Url::fromRoute('<front>')->toString());
        }
        return $this->redirectMiddleware->setRedirectResponse($response);
      }
    }
  }

  /**
   * Get profile pic.
   *
   * @param string $picture_url
   * @param string $id
   * @param string $user
   *
   * @return mixed
   */
  public function downloadProfilePic($picture_url, $id, $user) {
    if (user_picture_enabled()) {
      // Make sure that we have everything we need.
      if (!$picture_url || !$id) {
        return FALSE;
      }
      $picture_config = \Drupal::config('field.field.user.user.user_picture');
      $pictureDirectory = $picture_config->get('settings.file_directory');
      $data = ['user' => $user];
      $pictureDirectory = \Drupal::token()->replace($pictureDirectory, $data);
      // Check target directory from account settings and make sure it's writeable.
    //  $directory = file_default_scheme() . '://' . $pictureDirectory;
     // if (!file_prepare_directory($directory, FILE_CREATE_DIRECTORY)) {
      $directory = \Drupal::config('system.file')->get('default_scheme') . '://' . $pictureDirectory;
      if (!\Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY)) {
        \Drupal::logger('ciam')
          ->error('Could not save profile picture. Directory is not writeable: @directory', ['@dir' => $directory]);
      }
      // Download the picture. Facebook API always serves the images in jpg format.
      $destination = $directory . '/' . Html::escape($id) . '.jpg';
      $request = @file_get_contents($picture_url);
      if ($request) {
     //   $picture_file_data = file_save_data($request, $destination, FILE_EXISTS_REPLACE);
        $picture_file_data = file_save_data($request, $destination, FileSystemInterface::EXISTS_REPLACE);
        $maxResolution = $picture_config->get('settings.max_resolution');
        $minResolution = $picture_config->get('settings.min_resolution');
        file_validate_image_resolution($picture_file_data, $maxResolution, $minResolution);
        $user->set('user_picture', $picture_file_data->id());
        $user->save();
        unset($_SESSION['messages']['status']);
        return TRUE;
      }

      // Something went wrong.
      \Drupal::logger('ciam')->error('Could not save profile picture. Unhandled error.');
      return FALSE;
    }
  }

  /**
   * Create user.
   *
   * @param string $userprofile
   *
   * @return mixed
   */
  public function createUser($userprofile) {
    if (isset($userprofile->ID) && !empty($userprofile->ID)) {
      $user_config = \Drupal::config('user.settings');

      $user_register = $user_config->get('register');
      if ($user_register == 'visitors' || $user_register == 'visitors_admin_approval') {
        $newUserStatus = 0;
        if ($user_register != 'visitors_admin_approval' && ($user_register == 'visitors')) {
          $newUserStatus = 1;
        }
        $data = $this->checkUserName($userprofile);
        // Set up the user fields.
        $password =  \Drupal::service('password_generator')->generate(32);

        $fields = [
          'name' => ($this->moduleconfig->get('ciam_save_name_in_db') == 'false') ? $userprofile->ID : $data['username'],
          'mail' => $userprofile->Email_value,
          'init' => $userprofile->Email_value,
          'pass' => $password,
          'status' => $newUserStatus,
        ];

        $new_user = User::create($fields);
        $this->fieldCreateUserObject($new_user, $userprofile);
        $new_user->save();

        // Log notice and invoke Rules event if new user was succesfully created.
        if ($new_user->id()) {
          \Drupal::logger('ciam')
            ->notice('New user created. Username %username, UID: %uid', [
              '%username' => $new_user->getDisplayName(),
              '%uid' => $new_user->id(),
            ]);
          // Return $new_user;.
          $this->connection->insert('loginradius_mapusers')
            ->fields([
              'user_id' => $new_user->id(),
              'provider' => $userprofile->Provider,
              'provider_id' => $userprofile->ID,
            ])
            ->execute();
          $this->downloadProfilePic($userprofile->ImageUrl, $userprofile->ID, $new_user);

          // Advanced module LR Code Hook Start.
          if (\Drupal::moduleHandler()->hasImplementations('add_user_data_after_save')) {
            // Call all modules that implement the hook, and let them make changes to $variables.
            \Drupal::moduleHandler()->invokeAll('add_user_data_after_save', [$new_user, $userprofile]);
          }
          // Advanced module LR Code Hook End.
          $status = FALSE;
          if (($user_config->get('verify_mail') == 1) || !$user_config->get('verify_mail')) {
            $status = TRUE;
          }

          if ($new_user->isActive() && $status && \Drupal::service('session')->get('user_verify', []) != 1) {
            $new_user->setLastLoginTime(\Drupal::time()->getRequestTime());
          }
        }
        else {
          // Something went wrong.
          \Drupal::messenger()->addError(t('Creation of user account failed. Please contact site administrator.'));
          \Drupal::logger('ciam')->error('Could not create new user.');
          return FALSE;
        }

        if ($new_user->isActive() && $status && \Drupal::service('session')->get('user_verify', []) != 1) {
          return $this->provideLogin($new_user, $userprofile);
        }
        elseif ($user_register != 'visitors_admin_approval' && ($new_user->isActive() || (\Drupal::service('session')->get('user_verify', []) == 1 && $status))) {
          // Require email confirmation.
          _user_mail_notify('status_activated', $new_user);
          \Drupal::service('session')->set('user_verify', 0);
          \Drupal::messenger()->addStatus(t('Once you have verified your e-mail address, you may log in via Social Login.'));
          return new RedirectResponse(Url::fromRoute('user.login')->toString());
        }
        else {
          _user_mail_notify('register_pending_approval', $new_user);
          \Drupal::messenger()->addStatus(t('Thank you for applying for an account. Your account is currently pending approval by the site administrator.<br />In the meantime, a welcome message with further instructions has been sent to your e-mail address.'));
           return new RedirectResponse(Url::fromRoute('user.login')->toString());
        }
      }
      else {
        \Drupal::messenger()->addError(t('Only site administrators can create new user accounts.'));
        return new RedirectResponse(Url::fromRoute('user.login')->toString());
      }
    }
  }

 /**
   * Check existing user.
   *
   * @param $userprofile
   *
   * @return mixed
   */
  public function checkExistingUser($userprofile) {
    $drupal_user = NULL;
    if (isset($userprofile->ID) && !empty($userprofile->ID)) {

      $uid = $this->checkProviderId($userprofile->ID);
      // Advanced module LR Code Hook End.
      if ($uid) {
        $drupal_user = User::load($uid);
      }
    }

    if (!$drupal_user) {
      if (empty($userprofile->Email_value) || $this->moduleconfig->get('ciam_save_mail_in_db') == 'false') {
          $userprofile->Email_value = $this->randomEmailGeneration(); 
          $userprofile->Random_email_generated = true;               
      } else{
          $userprofile->Random_email_generated = false;
      }
      if (!empty($userprofile->Email_value)) {
        $drupal_user = $this->getUserByEmail($userprofile->Email_value);
        if ($drupal_user) {
          $this->insertSocialData($drupal_user->id(), $userprofile->ID, $userprofile->Provider);
        }
      }
    }

    if ($drupal_user) {
      
      return $this->provideLogin($drupal_user, $userprofile, TRUE);
    }
    else {
      
      return $this->createUser($userprofile);
    }
  }

  /**
   * Get random email.
   *
  */
  public function randomEmailGeneration()
  {
     $randomNo = $this->getRandomNumber(4);
     $base_root = Url::fromRoute('<front>')->setAbsolute()->toString();
     $site_domain = str_replace(array("http://","https://"), "", $base_root);
     $email = $randomNo . '@' . $site_domain.'.com';
     $variable = substr($email, 0, strpos($email, ".com"));
     $result = explode('.com', $variable);
     $email = $result[0].'.com';
     return $email;
  }

   /*
   * function to generate a random string
   */
  function getRandomNumber($n) {            
      $characters = 'abcdefghijklmnopqrstuvwxyz'.time(); 
      $randomString = ''; 
  
      for ($i = 0; $i < $n; $i++) { 
          $index = rand(0, strlen($characters) - 1); 
          $randomString .= $characters[$index]; 
      }         
      return $randomString. time(); 
  } 

  /**
   * Create user.
   *
   * @param $payload
   *
   * @return array
   */
  public function lrCiamCreateUser($payload) {
    try {
      $accountObj = new AccountAPI();
      return $accountObj->createAccount($payload);
    }
    catch (LoginRadiusException $e) {    
      if (isset($e->getErrorResponse()->Description) && $e->getErrorResponse()->Description) {
        return $e->getErrorResponse()->Description;
      }
    }
  }

  /**
   * Set password.
   *
   * @param $uid
   * @param $password
   *
   * @return mixed
   */
  public function lrCiamSetPassword($uid, $password) {
    try {
      $accountObj = new AccountAPI();
      $accountObj->setAccountPasswordByUid($password, $uid);
    }
    catch (LoginRadiusException $e) {
    }
  }

  /**
   * Handle Forgot password.
   *
   * @param $email
   * @param $reset_password_url
   *
   * @return mixed
   */
  public function lrCiamForgotPassword($email, $reset_password_url) {
    try {
      $authObj = new AuthenticationAPI();
      return $authObj->forgotPassword($email, $reset_password_url);
    }
    catch (LoginRadiusException $e) {
      if (isset($e->getErrorResponse()->Description) && $e->getErrorResponse()->Description) {
        return $e->getErrorResponse()->Description;
      }
    }
  }

  /**
   * Handle user callback.
   *
   * @param object $userprofile
   *   User profile data that you got from traditional/social network.
   *
   * @return mixed
   */
  public function handleUserCallback($userprofile) {
    return new RedirectResponse(Url::fromRoute('user.page')->toString());
  }
  

  /**
   * Convert field data.
   *
   * @return array
   */
  public function fieldFieldConvertInfo() {
    $convert_info = [
      'text' => [
        'label' => t('Text'),
        'callback' => 'fieldFieldConvertText',
      ],
      'email' => [
        'label' => t('Text'),
        'callback' => 'fieldFieldConvertText',
      ],
      'string' => [
        'label' => t('String'),
        'callback' => 'fieldFieldConvertText',
      ],
      'string_long' => [
        'label' => t('Long String'),
        'callback' => 'fieldFieldConvertText',
      ],
      'text_long' => [
        'label' => t('Long text'),
        'callback' => 'fieldFieldConvertText',
      ],
      'list_string' => [
        'label' => t('List String'),
        'callback' => 'fieldFieldConvertList',
      ],
      'boolean' => [
        'label' => t('Boolean'),
        'callback' => 'fieldFieldConvertBool',
      ],
      'datetime' => [
        'label' => t('Date'),
        'callback' => 'fieldFieldConvertDate',
      ],
      'date' => [
        'label' => t('Date'),
        'callback' => 'fieldFieldConvertDate',
      ],
      'datestamp' => [
        'label' => t('Date'),
        'callback' => 'fieldFieldConvertDate',
      ],
    ];

    \Drupal::moduleHandler()->alter('fieldFieldConvertInfo', $convert_info);
    return $convert_info;
  }

  /**
   * Convert text and text_long data.
   *
   * @param string $property_name
   *   User profile property name through which data mapped.
   * @param object $userprofile
   *   User profile data that you got from traditional/social network.
   * @param string $field
   *   User field name stored in database.
   * @param string $instance
   *   Field instance.
   *
   * @return array
   *   Contain value of field map data
   */
  public function fieldFieldConvertText($property_name, $userprofile, $field, $instance) {
    $value = NULL;
    if (!empty($property_name)) {
  
      if (isset($userprofile->$property_name)) {
        if (is_string($userprofile->$property_name)) {   
          $value = $userprofile->$property_name;
        }
        elseif (is_object($userprofile->$property_name)) {  
          $value = $userprofile->$property_name;
          if (isset($value->Name)) {           
            $value = $value->Name;      
          }       
        }elseif (is_bool($userprofile->$property_name)) {  
          $value = $userprofile->$property_name;    
          $value ? ['value' => (isset($value) && $value == 'true') ? true : '0'] : NULL;      
        }
      }


      return $value ? ['value' => $value] : NULL;
    }
  }


   /**
   * Convert bool data.
   *
   * @param string $property_name
   *   User profile property name through which data mapped.
   * @param object $userprofile
   *   User profile data that you got from traditional/social network.
   * @param string $field
   *   User field name stored in database.
   * @param string $instance
   *   Field instance.
   *
   * @return array
   *   Contain value of field map data
   */

  public function fieldFieldConvertBool($property_name, $userprofile, $field, $instance) {
    $value = NULL;
    $property_name =  explode("_", $property_name);  

      if(is_array($property_name)){
       if(isset($property_name[0]) && $property_name[0] == 'cf') {
          $name = $property_name[1];
          $value = $userprofile->CustomFields->$name;       
        }
      }
      elseif (is_string($userprofile->$property_name)) {
              $value = $userprofile->$property_name;
      }
      elseif (is_object($userprofile->$property_name)) {
            $object = $userprofile->$property_name;
            if (isset($object->name)) {
              $value = $object->name;
            }
      }
    return $value ? ['value' => (isset($value) && $value == 'true') ? true : '0'] : NULL;
  }

  /**
   * Convert list data.
   *
   * @param string $property_name
   *   User profile property name through which data mapped.
   * @param object $userprofile
   *   User profile data that you got from traditional/social network.
   * @param string $field
   *   User field name stored in database.
   * @param string $instance
   *   Field instance.
   *
   * @return array
   *   Contain value of field map data
   */
  public function fieldFieldConvertList($property_name, $userprofile, $field, $instance) {

    if (!empty($property_name)) {
      if (!isset($userprofile->$property_name) && !is_string($userprofile->$property_name)) { 
        return;
      }
   
      $options = options_allowed_values($field); 
      $best_match = 0.0;
      $match_sl = strtolower($userprofile->$property_name);

 
      foreach ($options as $key => $option) {
        $option = trim((string) $option);
        $match_option = strtolower($option);
        $this_match = 0;
        similar_text($match_option, $match_sl, $this_match);

        if ($this_match > $best_match) {
          $best_match = $this_match;
          $best_key = $key;
        }
      }
      return isset($best_key) ? ['value' => $best_key] : NULL;
    }
  }

  /**
   * Convert date data.
   *
   * @param string $property_name
   *   User profile property name through which data mapped.
   * @param object $userprofile
   *   User profile data that you got from traditional/social network.
   * @param string $field
   *   User field name stored in database.
   * @param string $instance
   *   Field instance.
   *
   * @return array
   *   Contain value of field map data
   */
  public function fieldFieldConvertDate($property_name, $userprofile, $field, $instance) {
    if (!empty($property_name)) {
      if (isset($userprofile->$property_name)) {
        $value = NULL;

        if (strpos($userprofile->$property_name, '/') !== FALSE) {
          $sldate = explode('/', $userprofile->$property_name);
          $date = strtotime($userprofile->$property_name);
          $formatDate = date('Y-m-d\TH:i:s', $date);
        }
        else {
          $sldate = explode('-', $userprofile->$property_name);
          $month = isset($sldate[0]) ? trim((string) $sldate[0]) : '';
          $date = isset($sldate[1]) ? trim((string) $sldate[1]) : '';
          $year = isset($sldate[2]) ? trim((string) $sldate[2]) : '';
          $formatDate = trim((string) $year . '-' . $month . '-' . $date, '-');
          $formatDate = $formatDate . 'T00:00:00';
        }

        if (count($sldate) == 3) {
          if (!empty($formatDate)) {
            $value = [
              'value' => $formatDate,
              'date_type' => $instance['type'],
            ];
          }
        }
        return $value;
      }
    }
  }

  /**
   * Field create user array.
   *
   * @param string $drupal_user
   *   User data.
   * @param object $userprofile
   *   User profile data that you got from traditional/social network.
   *
   * @return array
   *   Contain value of user field map data
   */
  public function fieldCreateUserArray(&$drupal_user, $userprofile) {
    $this->fieldCreateUser(NULL, $drupal_user, $userprofile, TRUE);
  }

   /**
   * Field update user object.
   *
   */
  public function fieldUpdateUserObject($uid, $userprofile) {
      if ($uid) {
        $drupal_user = User::load($uid);
      }
   
      if (isset($drupal_user) && $drupal_user->id()) {
        $this->fieldUpdateUser($uid, $drupal_user, $drupal_user, $userprofile, FALSE);
      }
  }

  /**
   * Field create user object.
   *
   * @param string $drupal_user
   *   User data.
   * @param object $userprofile
   *   User profile data that you got from traditional/social network.
   *
   * @return array
   *   Contain value of user field map data
   */
  public function fieldCreateUserObject($drupal_user, $userprofile) {
    $this->fieldCreateUser($drupal_user, $drupal_user, $userprofile, FALSE);
  }

  /**
   * Field create user.
   *
   * @param string $drupal_user
   *   User data.
   * @param string $drupal_user_ref
   *   user data reference.
   * @param object $userprofile
   *   User profile data that you got from traditional/social network.   *.
   * @param $register_form
   *   Passed boolean true or false
   *
   * @return array
   *   Contain value of user field map data
   */
  public function fieldCreateUser($drupal_user, &$drupal_user_ref, $userprofile, $register_form = FALSE) {
    $field_map = $this->moduleconfig->get('user_fields');  
    $field_convert_info = $this->fieldFieldConvertInfo();

    $entity_type = 'user';
    $instances = [];
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

      if (isset($field_map[$field_name]) && isset($field_convert_info[$field->getType()]['callback'])) {
        $callback = $field_convert_info[$field->getType()]['callback'];
        $field_property_name = $field_map[$field_name];

        if ($value = $this->$callback($field_property_name, $userprofile, $field, $instance)) {
     
          if (FALSE) {      
            $drupal_user_ref[$field_name]['widget']['0']['value']['#default_value'] = isset($value['value']) ? $value['value'] : $value;
          }
          else {            
            $drupal_user->set($field_name, $value);
          }
        }
      }
    }
  }

   /**
   * Field update user.
   *
   * @param string $uid
   *   User id.
   * @param string $drupal_user
   *   User data.
   * @param string $drupal_user_ref
   *   user data reference.
   * @param object $userprofile
   *   User profile data that you got from traditional/social network.   *.
   * @param $register_form
   *   Passed boolean true or false
   *
   * @return array
   *   Contain value of user field map data
   */

  public function fieldUpdateUser($uid, $drupal_user, &$drupal_user_ref, $userprofile, $register_form = FALSE) {
    $field_map = $this->moduleconfig->get('user_fields');  
    $field_convert_info = $this->fieldFieldConvertInfo();

    $entity_type = 'user';
    $instances = [];
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

      if (isset($field_map[$field_name]) && isset($field_convert_info[$field->getType()]['callback'])) {
        $callback = $field_convert_info[$field->getType()]['callback'];
        $field_property_name = $field_map[$field_name];

        if ($value = $this->$callback($field_property_name, $userprofile, $field, $instance)) {
     
          if ($register_form) {      
            $drupal_user_ref[$field_name]['widget']['0']['value']['#default_value'] = isset($value['value']) ? $value['value'] : $value;
          }
          else {
      
            try {
              $this->connection->update('user__'.$field_name)
                ->fields([$field_name.'_value' => $value['value']])
                ->condition('entity_id', $uid)
                ->execute();
            }
            catch (Exception $e) {
            }
          }
        }
      }
    }
  }

}
