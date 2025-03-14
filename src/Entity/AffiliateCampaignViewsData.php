<?php

namespace Drupal\affiliate\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for campaign entities.
 */
class AffiliateCampaignViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Provide a relationship for Conversions from Campaign entities.
    $data['affiliate_campaign']['conversions'] = [
      'title' => $this->t('Campaign Conversions'),
      'help' => $this->t('Relate this campaign to all of its conversions.'),
      'relationship' => [
        'group' => $this->t('Affiliate Campaign'),
        'label' => $this->t('Campaign Conversions'),
        'base' => 'affiliate_conversion',
        'base field' => 'campaign',
        'relationship field' => 'id',
        'id' => 'standard',
      ],
    ];
    // Provide a relationship for Clicks from Campaign entities.
    $data['affiliate_campaign']['clicks'] = [
      'title' => $this->t('Campaign Clicks'),
      'help' => $this->t('Relate this campaign to all of its clicks.'),
      'relationship' => [
        'group' => $this->t('Affiliate Campaign'),
        'label' => $this->t('Campaign Clicks'),
        'base' => 'affiliate_click',
        'base field' => 'campaign',
        'relationship field' => 'id',
        'id' => 'standard',
      ],
    ];

    return $data;
  }

}
