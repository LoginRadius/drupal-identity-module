<?php

/**
 * @file
 * Contains \Drupal\lr_ciam\Controller\CiamController.
 */

namespace Drupal\lr_ciam\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Access\AccessResult;
use \LoginRadiusSDK\Utility\Functions;
use \LoginRadiusSDK\LoginRadiusException;
use \LoginRadiusSDK\Clients\IHttpClient;
use \LoginRadiusSDK\Clients\DefaultHttpClient;
use \LoginRadiusSDK\Utility\SOTT;
use \LoginRadiusSDK\CustomerRegistration\Social\SocialLoginAPI;
use \LoginRadiusSDK\CustomerRegistration\Authentication\UserAPI;
use \LoginRadiusSDK\CustomerRegistration\Management\AccountAPI;

/**
 * Returns responses for Social Login module routes.
 */
class CiamController extends ControllerBase {

    protected $user_manager;
    protected $connection;

   
    public function __construct($user_manager) {
        $this->user_manager = $user_manager;    
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
     * Return change password form
     *
     * Handle token and validate the user.
     *
     */
    public function userChangePassword($user) {
        $output = array(
          '#title' => t('Change Password'),
          '#theme' => 'change_password',
          '#attributes' => array('class' => array('change-password'))
        );
        return $output;
    }
    
    /**
     * Show change password form
     *        
     */
   public function changePasswordAccess() {
        $config = \Drupal::config('ciam.settings');
        $user = \Drupal::currentUser()->getRoles();
        $access_granted = in_array("administrator", $user);
        $optionVal = $config->get('ciam_email_verification_condition');    
        if ($access_granted) {
            return AccessResult::forbidden();
        }
        else if ($optionVal === '0' || $optionVal === '2') {
            if ($_SESSION['provider'] == 'Email') {
                return AccessResult::allowed();
            }
            else {        
                return AccessResult::forbidden();
            }
        }
        elseif ($optionVal === '1') {      
            if ($_SESSION['provider'] == 'Email' || $_SESSION['emailVerified']) {
                return AccessResult::allowed();
            }
            else {
                return AccessResult::forbidden();
            }
        }        
        return AccessResult::forbidden();
    }
    
    /**
     * Response for path 'user/login'
     *
     * Handle token and validate the user.
     *
     */
    public function userRegisterValidate() {
        $config = \Drupal::config('ciam.settings');
        if (isset($_GET['action_completed']) && $_GET['action_completed'] == 'register') {
            drupal_set_message('Email for verification has been sent to your provided email id, check email for further instructions');
            return $this->redirect("<front>");
        }

        if (isset($_GET['action_completed']) && $_GET['action_completed'] == 'forgotpassword') {
            drupal_set_message('Password reset information sent to your provided email id, check email for further instructions');
            return $this->redirect("<front>");
        }        

        $request_token = isset($_REQUEST['token']) ? trim($_REQUEST['token']) : '';        
        if (isset($_REQUEST['token'])) {        
            $apiKey = trim($config->get('api_key'));
            $apiSecret = trim($config->get('api_secret'));

            try {
                $socialLoginObj = new SocialLoginAPI($apiKey, $apiSecret, array('output_format' => 'json'));
            }
            catch (LoginRadiusException $e) {                   
                \Drupal::logger('ciam')->error($e);
                drupal_set_message($e->getMessage(), 'error');
                return $this->redirect('user.login');
            }
            try {
                $userObject = new UserAPI($apiKey, $apiSecret, array('output_format' => 'json'));
            }
            catch (LoginRadiusException $e) {    
                \Drupal::logger('ciam')->error($e);
                drupal_set_message($e->getMessage(), 'error');
                return $this->redirect('user.login');
            } 
            
            //Get Access token.
            try {
                $result_accesstoken = $socialLoginObj->exchangeAccessToken(trim($_REQUEST['token']));
                }
            catch (LoginRadiusException $e) {
                \Drupal::logger('ciam')->error($e);
                drupal_set_message($e->getMessage(), 'error');
                return $this->redirect('user.login');
            }

            $_SESSION['access_token'] = $result_accesstoken->access_token;     
              //Get Userprofile form Access Token.
            try {
                $userprofile = $userObject->getProfile($result_accesstoken->access_token);
                $userprofile->widget_token = isset($result_accesstoken) ? $result_accesstoken->access_token : '';
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
                    $userprofile = $this->user_manager->getUserData($userprofile);
                    $_SESSION['user_verify'] = 0;

                    if (empty($userprofile->Email_value)) {

                        $uid = $this->user_manager->checkProviderID($userprofile->ID);

                        if ($uid) {
                            $drupal_user = User::load($uid);
                        }

                        if (isset($drupal_user) && $drupal_user->id()) {
                            return $this->user_manager->provideLogin($drupal_user, $userprofile);
                        }
                    }
                    return $this->user_manager->checkExistingUser($userprofile);
                }
            } else {          
               return $this->user_manager->handleUserCallback($userprofile);
            }
        }
        else {    
            return $this->redirect('user.login');
        }
    }
}
