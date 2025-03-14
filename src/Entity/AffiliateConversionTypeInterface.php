<?php

namespace Drupal\affiliate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Provides an interface for defining Affiliate Conversion type entities.
 */
interface AffiliateConversionTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {

  /**
   * Gets the conversion type description.
   *
   * @return string
   *   The description.
   */
  public function getDescription();

  /**
   * Sets the conversion type description.
   *
   * @param string $description
   *   The description.
   *
   * @return \Drupal\affiliate\Entity\AffiliateConversionTypeInterface
   *   The Conversion Type/Bundle
   */
  public function setDescription($description);

}
