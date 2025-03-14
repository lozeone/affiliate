<?php

namespace Drupal\commerce_affiliate\EventSubscriber;

use Drupal\affiliate\AffiliateManager;
use Drupal\affiliate\Entity\AffiliateCampaignInterface;
use Drupal\commerce_email\Plugin\Commerce\EmailEvent\OrderPlaced;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CommerceAffiliateCreateOrderConversion.
 *
 * This is fired when an order is marked as complete.
 * Here we check if the order is referencing an affiliate and add a conversion.
 *
 * @package commerce_affiliate
 */
class CommerceAffiliateCompleteConversion implements EventSubscriberInterface {

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
   * The Affiliate Conversion EntityStorageInterface.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $conversionStorage;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManager $entity_type_manager, AffiliateManager $affiliate_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->affiliateManager = $affiliate_manager;
    $this->conversionStorage = $this->entityTypeManager->getStorage('affiliate_conversion');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['commerce_order.place.post_transition'] = ['orderCompleteHandler'];
    return $events;
  }

  /**
   * Adds an affiliate conversion to an order when completed.
   *
   * The commission calculation happens in hook_affiliate_conversion_presave().
   *
   * @see commerce_affiliate_affiliate_conversion_presave()
   */
  public function orderCompleteHandler(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();

    // Check if there is an affiliate account tied to this product.
    $affiliate = $order->affiliate_account->entity ?? NULL;
    $campaign = $order->affiliate_campaign->entity ?? NULL;
    if ($affiliate) {
      $logStorage = $this->entityTypeManager->getStorage('commerce_log');
      // Loop through all the conversion types.
      $conversionTypes = $this->entityTypeManager->getStorage('affiliate_conversion_type')->loadMultiple();
      foreach ($conversionTypes as $conversionType) {
        $create_for = $conversionType->getThirdPartySetting('commerce_affiliate', 'commission_create');
        // $create_for tells us when to create a conversion.
        switch ($create_for) {
          case 'commerce_order':
            /** @var \Drupal\affiliate\Entity\AffiliateConversion $conversion */
            // Add one conversion per order.
            $conversion = $this->conversionStorage->create([
              'type' => $conversionType->id(),
              'affiliate' => $affiliate->id(),
              'campaign' => $campaign->id(),
            ]);
            $conversion->setParentEntity($order);
            commerce_affiliate_calculate_comission($conversion);
            $conversion->save();

            // Add a commerce log that a conversion was created for the order.
            $logStorage->generate($order, 'commerce_affiliate_conversion', [
              'affiliate' => $affiliate->toLink(),
              'campaign' => $campaign->toLink(),
              'product_title' => $order->label(),
            ])->save();
            break;

          case 'commerce_order_item':
            /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
            foreach ($order->getItems() as $key => $order_item) {
              /** @var \Drupal\affiliate\Entity\AffiliateConversion $conversion */
              // Add one conversion per item.
              $conversion = $this->conversionStorage->create([
                'type' => $conversionType->id(),
                'affiliate' => $affiliate->id(),
                'campaign' => $campaign->id(),
              ]);
              $conversion->setParentEntity($order_item);
              commerce_affiliate_calculate_comission($conversion);
              $conversion->save();
              // Add a commerce log that a conversion was created for the item.
              $logStorage->generate($order, 'commerce_affiliate_conversion', [
                'affiliate' => $affiliate->toLink(),
                'campaign' => $campaign->toLink(),
                'product_title' => $order_item->getPurchasedEntity()->label(),
              ])->save();
            }
            break;
        }
      }
    }
  }

}
