<?php

namespace Drupal\affiliate\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;

/**
 * Provides the Affiliate Campaign entity.
 *
 * @ContentEntityType(
 *   id = "affiliate_campaign",
 *   label = @Translation("Affiliate Campaign"),
 *   label_collection = @Translation("Affiliate Campaigns"),
 *   label_singular = @Translation("affiliate campaign"),
 *   label_plural = @Translation("affiliate campaigns"),
 *   label_count = @PluralTranslation(
 *     singular = "@count affiliate campaign",
 *     plural = "@count affiliate campaigns",
 *   ),
 *   base_table = "affiliate_campaign",
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\affiliate\AffiliateCampaignHtmlRouteProvider",
 *     },
 *     "form" = {
 *       "default" = "Drupal\affiliate\Form\AffiliateCampaignForm",
 *       "add" = "Drupal\affiliate\Form\AffiliateCampaignForm",
 *       "edit" = "Drupal\affiliate\Form\AffiliateCampaignForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" =
 *   "Drupal\affiliate\Entity\Handler\AffiliateCampaignAccessControlHandler",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\affiliate\Entity\AffiliateCampaignViewsData",
 *   },
 *   admin_permission = "administer affiliate_campaign entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "owner" = "user_id",
 *     "uid" = "user_id",
 *     "published" = "status",
 *   },
 *   field_ui_base_route = "entity.affiliate_campaign.settings",
 *   links = {
 *     "add-form" = "/affiliate/campaign/add",
 *     "canonical" = "/affiliate/campaign/{affiliate_campaign}",
 *     "collection" = "/admin/config/affiliate/campaigns",
 *     "delete-form" = "/affiliate/campaign/{affiliate_campaign}/delete",
 *     "edit-form" = "/affiliate/campaign/{affiliate_campaign}/edit",
 *   },
 * )
 */
class AffiliateCampaign extends ContentEntityBase implements AffiliateCampaignInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use EntityPublishedTrait;

  /**
   *
   */
  public function getAffiliate() {
    $user = $this->user_id->entity ?? NULL;
    /*if (!$user) {
    $user = new AnonymousUserSession();
    }*/
    return $user;
  }

  /**
   *
   */
  public function isDefault() {
    return (bool) $this->is_default->value;
  }

  /**
   *
   */
  public function setDefault($value = TRUE) {
    $this->set('is_default', $value);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    /** @var \Drupal\user\UserInterface $affiliate */
    if ($affiliate = $this->getAffiliate()) {
      $default = $this->isDefault();
      $original_default = $this->original ? $this->original->isDefault() : FALSE;
      if ($default && !$original_default) {
        // The campaign was set as default, remove the flag from other
        // campaigns.
        $campaigns = $storage->loadByProperties([
          'user_id' => $affiliate->id(),
          'is_default' => 1,
        ]);
        foreach ($campaigns as $campaign) {
          if ($campaign->id() != $this->id()) {
            $campaign->setDefault(FALSE);
            $campaign->save();
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t("Name"))
      ->setDescription(t("The name of the campaign."))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting("max_length", 255)
      ->setDisplayOptions("form", [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable("view", TRUE)
      ->setDisplayConfigurable("form", TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t("Created"))
      ->setDescription(t("The time the campaign was created."))
      ->setTranslatable(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t("Changed"))
      ->setDescription(t("The time the campaign was updated."))
      ->setTranslatable(TRUE);

    $fields['is_default'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Default'))
      ->setDescription(t('The default campaign when none is specified.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'checkbox',
      ])
      ->setDisplayConfigurable("view", TRUE)
      ->setDisplayConfigurable("form", TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->name->getString();
  }

}
