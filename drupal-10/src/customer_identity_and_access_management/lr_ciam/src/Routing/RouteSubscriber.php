<?php

namespace Drupal\lr_ciam\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Add routing.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('lr_ciam.settings_form')) {
      $route->setPath('admin/config/people/ciam');

      $defaults = $route->getDefaults();
      $defaults['_title'] = "LoginRadius";

      $route->setDefaults($defaults);
    }
    if ($route = $collection->get('advanced.settings_form')) {
      $route->setPath('admin/config/people/ciam/advanced');
    }
  }

}
