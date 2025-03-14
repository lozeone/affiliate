<?php

namespace Drupal\affiliate\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Interface for Affiliate Conversion entities.
 */
interface AffiliateConversionInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, EntityPublishedInterface {

  /**
   *
   */
  public function getBundle();

  /**
   *
   */
  public function getAffiliate();

  /**
   *
   */
  public function getAffiliateId();

  /**
   *
   */
  public function getCampaign();

  /**
   *
   */
  public function getCampaignId();

  /**
   *
   */
  public function getParentEntity();

  /**
   *
   */
  public function getParentEntityTypeId();

  /**
   *
   */
  public function getParentEntityId();

  /**
   *
   */
  public function setParentEntity(EntityInterface $entity);

  /**
   * Sets the commission for a conversion.
   *
   * @param float $value
   *   The numeric value for the commission.
   * @param string $currency
   *   The currency of the value.
   */
  public function setCommission(float $value, string $currency = '');

}
