<?php

/**
 * @file
 * Contains \Drupal\lr_ciam\Controller\CiamController.
 */

namespace Drupal\lr_auth\Controller;

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

use Drupal\simple_oauth\Entities\UserEntity;
use Drupal\simple_oauth\Plugin\Oauth2GrantManagerInterface;
use GuzzleHttp\Psr7\Response;
use Drupal\Core\Routing\TrustedRedirectResponse;
use \League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use \League\OAuth2\Server\Grant\ImplicitGrant;
use Drupal\Core\Password\PhpassHashedPassword;

/**
 * Returns responses for Auth module routes.
 */
class AuthController extends ControllerBase {

    protected $user_manager;
    protected $connection;

   
    public function __construct($user_manager) {
        $this->user_manager = $user_manager;        
    }

    /**
     * Return change password form
     *
     * Handle token and validate the user.
     *
     */
    public function getOauthAuthorize() {
        $auth_config = \Drupal::config('auth.settings');
        $auth_enable = $auth_config->get('auth_enable'); 
        $auth_secret = $auth_config->get('auth_secret'); 
        $config = \Drupal::config('simple_oauth.settings');
        if($auth_enable == '1' && isset($auth_secret) && !empty($auth_secret)){    
        try {
        $expiration_time = new \DateInterval(sprintf('PT%dS', $config->get('access_token_expiration')));
        $scope_repository= \Drupal::service('simple_oauth.repositories.scope');
        $grantManager = \Drupal::service('plugin.manager.oauth2_grant.processor');    
    
        $implicitObj = new ImplicitGrant($expiration_time);
        $implicitObj->setScopeRepository($scope_repository);
        $auth_request = new AuthorizationRequest();
        $auth_request->setGrantTypeId('implicit');
        // Once the user has logged in set the user on the AuthorizationRequest.
        $user_entity = new UserEntity();
        $user_entity->setIdentifier(\Drupal::currentUser()->id());             
        $auth_request->setUser($user_entity);
 
        // Once the user has approved or denied the client update the status
        // (true = approved, false = denied).
        $can_grant_codes = \Drupal::currentUser()->hasPermission('grant simple_oauth codes');     
        $objEntity = \Drupal::entityTypeManager();
        
        $hashpass = new PhpassHashedPassword(2);
        $objClientRepo = new \Drupal\simple_oauth\Repositories\ClientRepository($objEntity, $hashpass);
        
        $client = $objClientRepo->getClientEntity(
            $auth_secret,
            'implicit',
            null,
            false
        );
        if(isset($client) && !empty($client)){
        $redirect = $client->getRedirectUri();
        $auth_request->setRedirectUri($redirect);        
        $auth_request->setAuthorizationApproved(true && $can_grant_codes);
        $auth_request->setClient($client);
        $server = $grantManager->getAuthorizationServer('implicit');
        // Return the HTTP redirect response.       

        $current_user = \Drupal::currentUser();
        $roles_list = $current_user->getRoles(); 
        $roles = implode(",", $roles_list);
        $scope = implode(" ", $roles_list);
        $scopes = $implicitObj->validateScopes(
            $scope,
            is_array($client->getRedirectUri())
                ? $client->getRedirectUri()[0]
                : $client->getRedirectUri()
        );
        $scopes = $scope_repository->finalizeScopes(
            $scopes,
            'implicit',
            $client,
            \Drupal::currentUser()->id()
        );
      
        $auth_request->setScopes($scopes);
        $response = $server->completeAuthorizationRequest($auth_request, new Response());   
        // Get the location and return a secure redirect response.   
        $response = new TrustedRedirectResponse($response->getHeaderLine('location').'&roles='.$roles);             
            return $response->send();     
        } else {
            if(isset($_SESSION['frontsiteurl']) && $_SESSION['frontsiteurl'] != ''){
                $response = new TrustedRedirectResponse($_SESSION['frontsiteurl'].'?response_type=error'); 
                return $response->send();
            }           
        }        
        } catch(Exception $e) {
            if(isset($_SESSION['frontsiteurl']) && $_SESSION['frontsiteurl'] != ''){
                $response = new TrustedRedirectResponse($_SESSION['frontsiteurl'].'?response_type=error'); 
                return $response->send();  
            }           
        }
      }
    }
}
