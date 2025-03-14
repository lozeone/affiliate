<?php

namespace Drupal\affiliate\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\user\EntityOwnerTrait;

/**
 * Provides the Affiliate Conversion entity.
 *
 * @ContentEntityType(
 *   id = "affiliate_conversion",
 *   label = @Translation("Affiliate Conversion"),
 *   label_collection = @Translation("Affiliate Conversions"),
 *   label_singular = @Translation("Affiliate Conversion"),
 *   label_plural = @Translation("Affiliate Conversions"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Affiliate Conversion",
 *     plural = "@count Affiliate Conversions",
 *   ),
 *   bundle_label = @Translation("Affiliate Conversion Type"),
 *   base_table = "affiliate_conversion",
 *   bundle_entity_type = "affiliate_conversion_type",
 *   field_ui_base_route = "entity.affiliate_conversion_type.edit_form",
 *   permission_granularity = "bundle",
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *    "views_data" = "Drupal\affiliate\Entity\AffiliateConversionViewsData",
 *    "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *   },
 *   admin_permission = "administer affiliate_conversion entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "owner" = "user_id",
 *     "uid" = "user_id",
 *     "published" = "status",
 *   },
 *   links = {
 *     "add-form" = "/affiliate/conversion/add",
 *     "canonical" = "/affiliate/conversion/{affiliate_conversion}",
 *     "collection" = "/admin/config/affiliate/conversions",
 *     "delete-form" = "/affiliate/conversion/{affiliate_conversion}/delete",
 *     "edit-form" = "/affiliate/conversion/{affiliate_conversion}/edit",
 *   },
 * )
 */
class AffiliateConversion extends ContentEntityBase implements AffiliateConversionInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'affiliate' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    // Replace the label pattern with parent entity tokens.
    if (empty($this->label->value)) {
      $label = $this->getBundle()->getLabelPattern();
      $label = \Drupal::token()->replace($label, [
        'affiliate_conversion' => $this,
      ]);
      $this->set('label', $label);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t("Label"))
      ->setDescription(t("The line item label for the conversion. Empty labels will be auto generated based on bundle settings."))
      ->setRequired(FALSE)
      ->setTranslatable(TRUE)
      ->setSetting("max_length", 255)
      ->setDisplayOptions("form", [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable("view", TRUE)
      ->setDisplayConfigurable("form", TRUE);

    $fields['affiliate'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Affiliate'))
      ->setDescription(t('The affiliate user account'))
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable("view", TRUE)
      ->setDisplayConfigurable("form", TRUE);

    $fields['campaign'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Campaign'))
      ->setDescription(t('The referenced campaign.'))
      ->setSetting('target_type', 'affiliate_campaign')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable("view", TRUE)
      ->setDisplayConfigurable("form", TRUE);

    $fields['entity_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Parent Entity Id'))
      ->setDescription(t("The Id of the parent entity."));

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Parent Entity Type'))
      ->setDescription(t("The Type of the parent entity."));

    $fields['amount'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t("Amount"))
      ->setDefaultValue(0)
      ->setDescription(t("The amount of the conversion."))
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable("view", TRUE)
      ->setDisplayConfigurable("form", TRUE);

    $fields['currency'] = BaseFieldDefinition::create('string')
      ->setLabel(t("Currency"))
      ->setDescription(t("The currency of the conversion amount."))
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable("view", TRUE)
      ->setDisplayConfigurable("form", TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t("Created"))
      ->setDescription(t("The time the conversion was created."))
      ->setTranslatable(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t("Changed"))
      ->setDescription(t("The time the conversion was updated."))
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   *
   */
  public function getBundle() {
    return $this->type->entity;
  }

  /**
   *
   */
  public function getAffiliate() {
    $user = $this->affiliate->entity ?? NULL;
    return $user ?? new AnonymousUserSession();
  }

  /**
   *
   */
  public function getAffiliateId() {
    return $this->affiliate->target_id ?? NULL;
  }

  /**
   *
   */
  public function getCampaign() {
    return $this->campaign->entity ?? NULL;
  }

  /**
   *
   */
  public function getCampaignId() {
    return $this->campaign->target_id ?? NULL;
  }

  /**
   *
   */
  public function getParentEntity() {
    $type = $this->getParentEntityTypeId();
    $id = $this->getParentEntityId();

    if ($type && $id) {
      return $this->entityTypeManager()->getStorage($type)->load($id);
    }

    return NULL;
  }

  /**
   *
   */
  public function getParentEntityTypeId() {
    return $this->entity_type->value ?? NULL;
  }

  /**
   *
   */
  public function getParentEntityId() {
    return $this->entity_id->value ?? NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function setParentEntity(EntityInterface $entity) {
    $this->set('entity_id', $entity->id());
    $this->set('entity_type', $entity->getEntityTypeId());
  }

  /**
   * {@inheritDoc}
   */
  public function setCommission(float $value, string $currency = '') {
    $this->set('amount', $value);
    $this->set('currency', $currency);
  }

  /**
   * {@inheritDoc}
   */
  public function getCommission() {
    return [
      'amount' => $this->amount->value ?? 0,
      'currency' => $this->currency->value ?? '',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function isApproved() {
    return $this->isPublished();
  }

}
