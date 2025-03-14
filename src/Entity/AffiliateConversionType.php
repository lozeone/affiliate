<?php

namespace Drupal\affiliate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the affiliate Conversion type entity.
 *
 * @ConfigEntityType(
 *   id = "affiliate_conversion_type",
 *   label = @Translation("Affiliate Conversion type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" =
 *   "Drupal\affiliate\Entity\Handler\AffiliateConversionTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\affiliate\Form\AffiliateConversionTypeForm",
 *       "add" = "Drupal\affiliate\Form\AffiliateConversionTypeForm",
 *       "edit" = "Drupal\affiliate\Form\AffiliateConversionTypeForm",
 *       "delete" = "Drupal\affiliate\Form\AffiliateConversionTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *        "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *      },
 *   },
 *   config_prefix = "affiliate_conversion_type",
 *   admin_permission = "administer affiliate_conversion types",
 *   bundle_of = "affiliate_conversion",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "description",
 *     "default_commission",
 *     "label_pattern",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/affiliate/conversion/types/{affiliate_conversion_type}",
 *     "add-form" = "/admin/structure/affiliate/conversion/types/add",
 *     "edit-form" = "/admin/structure/affiliate/conversion/types/{affiliate_conversion_type}/edit",
 *     "delete-form" = "/admin/structure/affiliate/conversion/types/{affiliate_conversion_type}/delete",
 *     "collection" = "/admin/structure/affiliate/conversion/types"
 *   }
 * )
 */
class AffiliateConversionType extends ConfigEntityBundleBase implements AffiliateConversionTypeInterface {

  /**
   * The affiliate Conversion type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The affiliate Conversion type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The affiliate Conversion type description.
   *
   * @var string
   */
  protected $description;

  /**
   * The affiliate storage.
   *
   */
  protected $storage;

  /**
   * The conversion label token pattern
   *
   */
  protected $label_pattern;

  /**
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   *
   */
  public function getStorage() {
    return $this->storage;
  }

  /**
   * @return string
   */
  public function __toString() {
    return (string) $this->label();
  }

  /**
   * Required by EntityViewBuilder. affiliates are never revisioned.
   */
  public function isDefaultRevision() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->set('description', $description);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelPattern() {
    return (string) $this->label_pattern ?? '';
  }

  /**
   * {@inheritdoc}
   *
   * Track file usage for badges.
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
  }

  /**
   * {@inheritdoc}
   *
   * Delete file usage.
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
  }

}
