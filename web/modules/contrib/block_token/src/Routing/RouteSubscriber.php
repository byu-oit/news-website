<?php

namespace Drupal\block_token\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // For block configuration route grant permission if
    // block token permission has been checked.

    // admin/structure/block/manage/{block}
    if ($route = $collection->get('entity.block.edit_form')) {
      $route->setRequirements(array(
        '_custom_access' => '\block_token_route_access',
      ));
    }

    if ($route = $collection->get('block.admin_display_theme')) {
      $route->setRequirements(array(
        '_custom_access' => '\block_token_route_access',
      ));
    }
  }
}
