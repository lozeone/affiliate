<?php

namespace Drupal\affiliate\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;

/**
 * Provides the Affiliate Click entity.
 *
 * @ContentEntityType(
 *   id = "affiliate_click",
 *   label = @Translation("Affiliate Click"),
 *   label_collection = @Translation("Affiliate Clicks"),
 *   label_singular = @Translation("affiliate click"),
 *   label_plural = @Translation("affiliate clicks"),
 *   label_count = @PluralTranslation(
 *     singular = "@count affiliate click",
 *     plural = "@count affiliate clicks",
 *   ),
 *   base_table = "affiliate_click",
 *   handlers = {
 *     "form" = {
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *   "route_provider" = {
 *     "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *   },
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" =
 *   "Drupal\affiliate\Entity\Handler\AffiliateClickAccessControlHandler",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *   },
 *   admin_permission = "administer affiliate_click entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "user_id",
 *     "uid" = "user_id",
 *   },
 *   links = {
 *     "delete-form" = "/affiliate/click/{affiliate_click}/delete",
 *     "collection" = "/admin/config/affiliate/clicks",
 *   },
 * )
 */
class AffiliateClick extends ContentEntityBase implements AffiliateClickInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['affiliate'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Affiliate'))
      ->setDescription(t('The affiliate account of the click'))
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

    $fields['hostname'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Hostname'))
      ->setDescription(t("The IP address that made the click (clicked the affiliate's link)."));

    $fields['referrer'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Referrer'))
      ->setDescription(t('The referring site.'));

    $fields['destination'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Destination'))
      ->setDescription(t('The click destination.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t("Created"))
      ->setDescription(t("The time of the click."));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return t('Affiliate Click #%id', ['%id' => $this->id()]);
  }

}
