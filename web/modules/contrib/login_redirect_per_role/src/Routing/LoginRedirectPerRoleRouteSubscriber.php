<?php

namespace Drupal\login_redirect_per_role\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Login And Logout Redirect Per Role route subscriber.
 */
class LoginRedirectPerRoleRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    if ($route = $collection->get('user.logout')) {

      $route->setDefault(
        '_controller',
        '\Drupal\login_redirect_per_role\Controller\LoginRedirectPerRoleUser::logout'
      );
    }
  }

}
