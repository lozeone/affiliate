<?php

namespace Drupal\affiliate\Plugin\views\field;

use Drupal\views\Plugin\views\field\EntityField;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to render the title of an affiliate_conversions parent entity.
 *
 * @ViewsField("affiliate_conversion_parent_entity")
 */
class AffiliateConversionParentEntity extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $this->getEntity($values);
    if ($parent = $entity->getParentEntity()) {
      $build[]['#markup'] = $parent->label();
      return $build;
    }
  }

}
