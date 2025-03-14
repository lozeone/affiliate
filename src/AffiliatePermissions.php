<?php

namespace Drupal\affiliate;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides dynamic permissions for Affiliate Campaign entities.
 *
 * @ingroup affiliate
 *
 */
class AffiliatePermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of permissions.
   */
  public function generatePermissions() {
    $permissions['create affiliate_campaign entities'] = [
      'title' => $this->t('Create new Affiliate Campaigns'),
    ];
    $permissions['create global affiliate_campaign entities'] = [
      'title' => $this->t('Create new Global Affiliate Campaigns'),
    ];
    $scopes = ['any', 'own'];
    foreach ($scopes as $scope) {
      $params = ['%scope' => $scope];
      $permissions['view ' . $scope . ' affiliate_campaign entities'] = [
        'title' => $this->t('View %scope affiliate campaign entities', $params),
      ];
      $permissions['edit ' . $scope . ' affiliate_campaign entities'] = [
        'title' => $this->t('Edit %scope affiliate campaign entities', $params),
      ];
      $permissions['delete ' . $scope . ' affiliate_campaign entities'] = [
        'title' => $this->t('Delete %scope affiliate campaign entities', $params),
      ];
      $permissions['view ' . $scope . ' affiliate_click entities'] = [
        'title' => $this->t('View %scope affiliate click entities', $params),
      ];
      $permissions['delete ' . $scope . ' affiliate_click entities'] = [
        'title' => $this->t('Delete %scope affiliate click entities', $params),
      ];
    }
    return $permissions;
  }

}
