<?php

namespace Drupal\lr_sso\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use LoginRadiusSDK\LoginRadiusException;
use LoginRadiusSDK\CustomerRegistration\Authentication\AuthenticationAPI;
use Drupal\user\Entity\User;

class CheckSSORole extends ControllerBase
{
  //The below function will check wether the user is permitted to login or not using SSO
  public function checkSSO()
  {
    $token = $_POST['ssotoken'];
    $response = array();
    try {
      $authObject = new AuthenticationAPI();
      $userprofile = $authObject->getProfileByAccessToken($token);
      $sso_config = \Drupal::config('lr_sso.settings');
      $uid = $this->checkProviderId($userprofile->ID);
      $drupal_user = User::load($uid);
      $user = $drupal_user->getRoles();
      $permited_roles_SSO = $sso_config->get('sso_role_data');
      $is_permitted_to_sso = !empty(array_intersect($user, $permited_roles_SSO));
      $response["ispermitted"] = !$is_permitted_to_sso;
    } catch (LoginRadiusException $e) {
      $response["ispermitted"] = false;
    }
    return new JsonResponse($response);
  }
  //This function will conver our ID data point to the Uid of an drupal if user exist 
  public function checkProviderId($pid)
  {
    // $query = db_select('users', 'u');
    $query = \Drupal::database()->select('users', 'u');
    $query->join('loginradius_mapusers', 'lu', 'u.uid = lu.user_id');
    $query->addField('u', 'uid');
    $query->condition('lu.provider_id', $pid);
    $check_aid = $query->execute()->fetchField();
    return $check_aid;
  }
}
