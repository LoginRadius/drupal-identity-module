<?php

namespace Drupal\lr_ciam\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Access\AccessResult;
use LoginRadiusSDK\LoginRadiusException;
use LoginRadiusSDK\CustomerRegistration\Authentication\UserAPI;
use LoginRadiusSDK\CustomerRegistration\Account\AccountAPI;
use LoginRadiusSDK\Advance\ConfigAPI;

/**
 * Returns responses for user Login routes.
 */
class CiamController extends ControllerBase {

  protected $usermanager;
  protected $connection;

  /**
   * Handle user registration.
   */
  public function __construct($user_manager) {
    $this->usermanager = $user_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('lr_ciam.user_manager')
    );
  }

  /**
   * Set user password.
   *
   * Return change password form.
   */
  public function userChangePassword($user) {
    $post_value = \Drupal::request()->request->all();
    $config = \Drupal::config('lr_ciam.settings');
    $apiKey = trim($config->get('api_key'));
    $apiSecret = trim($config->get('api_secret'));
    $apiSigning = trim($config->get('api_request_signing'));
    $apiRequestSigning = FALSE;
    if (isset($apiSigning) && $apiSigning == 'true') {
      $apiRequestSigning = TRUE;
    }

    if (isset($post_value['setpasswordsubmit']) && $post_value['setpasswordsubmit'] == 'submit') {

      if (isset($post_value['setnewpassword']) && !empty($post_value['setnewpassword']) && isset($post_value['setconfirmpassword']) && !empty($post_value['setconfirmpassword'])) {

        if ($post_value['setnewpassword'] == $post_value['setconfirmpassword']) {

          try {
            $accountObject = new AccountAPI($apiKey, $apiSecret, ['output_format' => 'json', 'api_request_signing' => $apiRequestSigning]);
     		    $userProfileUid = \Drupal::service('session')->get('user_profile_uid', []);
            $result = $accountObject->setPassword($userProfileUid, $post_value['setnewpassword']);
            if (isset($result) && $result) {
              drupal_set_message($this->t('Password set successfully.'));
            }
          }
          catch (LoginRadiusException $e) {
            \Drupal::logger('ciam')->error($e);
            drupal_set_message($e->getMessage(), 'error');
          }
        }
        else {
            drupal_set_message($this->t('The Confirm Password field does not match the Password field.'), 'error');
        }
      }
      else {               
        drupal_set_message($this->t('The password and confirm password fields are required.'), 'error');
      }
    }

    try {
      $userObject = new UserAPI($apiKey, $apiSecret, ['output_format' => 'json']);
      $userprofile = $userObject->getProfile(\Drupal::service('session')->get('access_token', []), 'Password');
    }
    catch (LoginRadiusException $e) {
      \Drupal::logger('ciam')->error($e);
    }

    if (isset($userprofile->Password) && $userprofile->Password != '') {
      $output = [
        '#title' => $this->t('Change Password'),
        '#theme' => 'change_password',
        '#attributes' => ['class' => ['change-password']],
      ];
    }
    else {
      $output = [
        '#title' => $this->t('Set Password'),
        '#theme' => 'set_password',
        '#attributes' => ['class' => ['set-password']],
      ];
    }
    return $output;
  }

  /**
   * Show change password form.
   */
  public function changePasswordAccess() {
    $config = \Drupal::config('lr_ciam.settings');
    $user = \Drupal::currentUser()->getRoles();
    $access_granted = in_array("administrator", $user);
    $apiKey = $config->get('api_key');
    $apiSecret = $config->get('api_secret');
    try {
      $configObject = new ConfigAPI($apiKey, $apiSecret, ['output_format' => 'json']);
      $configData = $configObject->getConfigurationList();
    }
    catch (LoginRadiusException $e) {
      \Drupal::logger('ciam')->error($e);
    }
    $optionVal = isset($configData->EmailVerificationFlow) ? $configData->EmailVerificationFlow : '';
    $lrProvider = \Drupal::service('session')->get('provider', []);
    $isEmailVerified = \Drupal::service('session')->get('emailVerified', []);
    if ($access_granted) {
      return AccessResult::forbidden();
    }

    elseif ($optionVal === 'required' || $optionVal === 'disabled') {
      if (isset($lrProvider) && $lrProvider == 'Email') {
        return AccessResult::allowed();
      }
      else {
        return AccessResult::forbidden();
      }
    }
    elseif ($optionVal === 'optional') {
      if (isset($lrProvider) && $lrProvider == 'Email' || isset($isEmailVerified) && $isEmailVerified) {
        return AccessResult::allowed();
      }
      else {
        return AccessResult::forbidden();
      }
    }
    return AccessResult::forbidden();
  }

  /**
   * Response for path 'user/login'.
   *
   * Handle token and validate the user.
   */
  public function userRegisterValidate() {
    $config = \Drupal::config('lr_ciam.settings');
    if (isset($_GET['action_completed']) && $_GET['action_completed'] == 'register') {
      drupal_set_message($this->t('Email for verification has been sent to your provided email id, check email for further instructions'));
      return $this->redirect("<front>");
    }

    if (isset($_GET['action_completed']) && $_GET['action_completed'] == 'forgotpassword') {
      drupal_set_message($this->t('Password reset information sent to your provided email id, check email for further instructions'));
      return $this->redirect("<front>");
    }

    $request_token = isset($_REQUEST['token']) ? trim($_REQUEST['token']) : '';
    if (isset($_REQUEST['token'])) {
      $apiKey = trim($config->get('api_key'));
      $apiSecret = trim($config->get('api_secret'));
      $userObject = new UserAPI($apiKey, $apiSecret, ['output_format' => 'json']);
      \Drupal::service('session')->set('access_token', $request_token);

      // Get Userprofile form Access Token.
      try {
        $userprofile = $userObject->getProfile($request_token);
        $userprofile->widget_token = $request_token;
  
	    \Drupal::service('session')->set('user_profile_uid', $userprofile->Uid);
	    \Drupal::service('session')->set('user_profile_fullName', $userprofile->FullName);
		  \Drupal::service('session')->set('user_profile_phoneId', $userprofile->PhoneId);
      } 
      catch (LoginRadiusException $e) {
        \Drupal::logger('ciam')->error($e);
        drupal_set_message($e->getMessage(), 'error');
        return $this->redirect('user.login');
      }
	  
	   
      // Advanced module LR Code Hook Start.
      // Make sure at least one module implements our hook.
      if (count(\Drupal::moduleHandler()->getImplementations('add_loginradius_userdata')) > 0) {
        // Call all modules that implement the hook, and let them.
        // Make changes to $variables.
        $result = \Drupal::moduleHandler()->invokeAll('add_loginradius_userdata', [$userprofile, $userprofile->widget_token]);
        $value = end($result);
        if (!empty($value)) {
          $userprofile = $value;
        }
      }

      // Advanced module LR Code Hook End.
      if (\Drupal::currentUser()->isAnonymous()) {
        if (isset($userprofile) && isset($userprofile->ID) && $userprofile->ID != '') {
          $userprofile = $this->usermanager->getUserData($userprofile);

          \Drupal::service('session')->set('user_verify', 0);

          if (empty($userprofile->Email_value)) {

            $uid = $this->usermanager->checkProviderID($userprofile->ID);

            if ($uid) {
              $drupal_user = User::load($uid);
            }

            if (isset($drupal_user) && $drupal_user->id()) {
              return $this->usermanager->provideLogin($drupal_user, $userprofile);
            }
          }
          return $this->usermanager->checkExistingUser($userprofile);
        }
        else {
          drupal_set_message($this->t('Something went wrong, check your settings.'), 'error');
          return $this->redirect("<front>");
        }
      }
      else {
        return $this->usermanager->handleUserCallback($userprofile);
      }
    }
    else {
      return $this->redirect('user.login');
    }
  }

}
