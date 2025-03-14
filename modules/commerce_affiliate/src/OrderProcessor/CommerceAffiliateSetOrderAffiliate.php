<?php

namespace Drupal\commerce_affiliate\OrderProcessor;

use Drupal\affiliate\AffiliateManager;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class CommerceAffiliateOrderProcessor.
 *
 * This is run whenever the cart is recalculated. Here we check if we have an
 * affiliate/campaign cookie and set a field on the order that we check later
 * in the order complete subscriber.
 *
 * @see \Drupal\commerce_affiliate\EventSubscriber\CommerceAffiliateCompleteConversion
 *   where conversion is saved on order complete.
 */
class CommerceAffiliateSetOrderAffiliate implements OrderProcessorInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The affiliate manager service.
   *
   * @var \Drupal\affiliate\AffiliateManager
   */
  protected $affiliateManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManager $entity_type_manager, AffiliateManager $affiliate_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->affiliateManager = $affiliate_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order) {
    if ($order->getCustomerId() == \Drupal::currentUser()->id()) {
      if ($order->hasField('affiliate_account') && $order->affiliate_account->isEmpty()) {
        if ($affiliate = $this->affiliateManager->getSessionAffiliate()) {
          $order->set('affiliate_account', $this->affiliateManager->getAffiliateId($affiliate));
          $order->set('affiliate_campaign', $this->affiliateManager->getSessionCampaign()->id());
          // Add a commerce log that an affiliate was assigned to the order.
          $logStorage = $this->entityTypeManager->getStorage('commerce_log');
          $logStorage->generate($order, 'commerce_affiliate_added', [
            'affiliate' => $affiliate->toLink(),
            'campaign' => !empty($campaign) ? $campaign->toLink() : $this->t('none'),
          ])->save();
        }
      }
    }
  }

}
