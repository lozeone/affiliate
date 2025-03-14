<?php

namespace Drupal\affiliate\Entity\Handler;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the AffiliateCampaign entity.
 *
 * @see \Drupal\affiliate\Entity\AffiliateCampaign
 */
class AffiliateCampaignAccessControlHandler extends EntityAccessControlHandler {

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
        return AccessResult::allowedIfHasPermission($account, 'view any affiliate_campaign entities');

      case 'update':
        $permission = $this->checkOwn($entity, $operation, $account);
        if (!empty($permission)) {
          return AccessResult::allowed();
        }
        return AccessResult::allowedIfHasPermission($account, 'edit any affiliate_campaign entities');

      case 'delete':
        // The default campaign cannot be deleted.
        if ($entity->isDefault()) {
          return AccessResult::forbidden();
        }
        $permission = $this->checkOwn($entity, $operation, $account);
        if (!empty($permission)) {
          return AccessResult::allowed();
        }
        return AccessResult::allowedIfHasPermission($account, 'delete any affiliate_campaign entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $permissions[] = 'create affiliate_campaign entities';
    $result = AccessResult::allowedIfHasPermissions($account, $permissions, 'OR');

    return $result;
  }

  /**
   * Test for given 'own' permission.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param $operation
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
      'view' => 'view own affiliate_campaign entities',
      'update' => 'edit own affiliate_campaign entities',
      'delete' => 'delete own affiliate_campaign entities',
    ];
    $permission = $ops[$operation];

    if ($account->hasPermission($permission)) {
      return $permission;
    }

    return NULL;
  }

}
