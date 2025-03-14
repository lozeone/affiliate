<?php

namespace Drupal\affiliate\Entity\Handler;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the AffiliateClick entity.
 *
 * @see \Drupal\affiliate\Entity\AffiliateClick
 */
class AffiliateClickAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\affiliate\Entity\AffiliateCampaignInterface $entity */
    switch ($operation) {
      case 'view':
        $permission = $this->checkOwn($entity, $operation, $account);
        if (!empty($permission)) {
          return AccessResult::allowed();
        }
        return AccessResult::allowedIfHasPermission($account, 'view any affiliate_click entities');

      case 'delete':
        $permission = $this->checkOwn($entity, $operation, $account);
        if (!empty($permission)) {
          return AccessResult::allowed();
        }
        return AccessResult::allowedIfHasPermission($account, 'delete any affiliate_click entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * Test for given 'own' permission.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string $operation
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return string|null
   *   The permission string indicating it's allowed.
   */
  protected function checkOwn(EntityInterface $entity, $operation, AccountInterface $account) {
    $is_own = $account->isAuthenticated() && $account->id() == $entity->getOwnerId();
    if (!$is_own) {
      return;
    }

    $ops = [
      'view' => 'view own affiliate_click entities',
      'delete' => 'delete own affiliate_click entities',
    ];
    $permission = $ops[$operation];

    if ($account->hasPermission($permission)) {
      return $permission;
    }

    return NULL;
  }

}
