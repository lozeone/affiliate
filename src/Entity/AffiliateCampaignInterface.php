<?php

namespace Drupal\affiliate\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Interface for Affiliate Campaign entities.
 */
interface AffiliateCampaignInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, EntityPublishedInterface {

  /**
   *
   */
  public function getAffiliate();

  /**
   *
   */
  public function isDefault();

  /**
   *
   */
  public function setDefault($value = TRUE);

}
