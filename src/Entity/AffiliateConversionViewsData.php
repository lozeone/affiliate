<?php

namespace Drupal\affiliate\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Conversion entities.
 */
class AffiliateConversionViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    // We are changing the entity_id string value to a rendered value of the
    // parent title.
    $data['affiliate_conversion']['entity_id']['title'] = $this->t('Parent Entity');
    $data['affiliate_conversion']['entity_id']['field']['id'] = 'affiliate_conversion_parent_entity';
    unset($data['affiliate_conversion']['entity_id']['relationship']);

    return $data;
  }

}
