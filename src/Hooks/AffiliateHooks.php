<?php

namespace Drupal\affiliate\Hooks;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Contains hook implementations for the Affiliate module.
 */
class AffiliateHooks {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      case 'help.page.affiliate':
        return t("Provides common Affiliate functionality.");

      // OPTIONAL: Add additional cases for other paths that should display
      // help text.
    }
  }

}
