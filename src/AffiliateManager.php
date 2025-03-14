<?php

namespace Drupal\affiliate;

use Drupal\affiliate\Entity\AffiliateCampaignInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AffiliateManager service.
 */
class AffiliateManager {

  use StringTranslationTrait;

  /**
   * The EntityTypeManagerInterface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user account interface.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The request stack.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The affiliate config settings.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs an AffiliateManager service.
   */
  public function __construct(
    AccountInterface $currentUser,
    EntityTypeManagerInterface $entity_type_manager,
    RequestStack $request_stack,
    ModuleHandler $module_handler,
    ConfigFactory $config_factory,
  ) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entity_type_manager;
    $this->requestStack = $request_stack;
    $this->moduleHandler = $module_handler;
    $this->config = $config_factory->get('affiliate.settings');
  }

  /**
   * Checks if a user account is an active affiliate.
   *
   * This can be called multiple times per request so statically caching.
   */
  public function isActiveAffiliate(AccountInterface $account) {
    static $access;
    if (isset($access[$account->id()])) {
      return $access[$account->id()];
    }
    $access[$account->id()] = $account->hasPermission('act as an affiliate');
    // Allow other modules to decide if this is a valid affiliate.
    $this->moduleHandler->alter('affiliate_active_affiliate', $access[$account->id()], $account);
    return $access[$account->id()];
  }

  /**
   *
   */
  public function getAffiliateId(AccountInterface $account) {
    // First check that this account is a valid affiliate.
    if ($this->isActiveAffiliate($account)) {
      // @todo make this alterable so we can use something other than the
      //   accounts userID in the url. For example the username or the value
      //   of a field stored on the user account.
      return $account->id();
    }
    return FALSE;
  }

  /**
   * Returns the default campaign.
   *
   * The default campaign is created during installation. All clicks with no
   * campaign id specified (like ref/%user) are attributed to this campaign.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The campaign entity.
   */
  public function getDefaultCampaign() {
    $campaignStorage = $this->entityTypeManager->getStorage('affiliate_campaign');
    $id = $campaignStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('user_id', 0)
      ->condition('is_default', 1)
      ->range(0, 1)
      ->execute();

    if ($id) {
      return $campaignStorage->load(reset($id));
    }
    return NULL;
  }

  /**
   * Registers a click.
   *
   * Creates click entity.
   *
   * @param \Drupal\Core\Session\AccountInterface $affiliate
   *   The user object of the affiliate getting the click.
   * @param \Drupal\affiliate\Entity\AffiliateCampaignInterface $campaign
   *   The campaign entity.
   * @param string $destination
   *   The end destination (requested URL)
   */
  public function registerClick(AccountInterface $affiliate, AffiliateCampaignInterface $campaign, string $destination) {
    // Check if campaign is active.
    // @todo if campaign is inactive should it fall back to the default
    //   campaign or cancel the click?
    if (!$campaign->isPublished()) {
      return;
    }

    // Check if the affiliate is the same as the current user.
    if (!$this->config->get('allow_owner') && $affiliate->id() == $this->currentUser->id()) {
      return;
    }

    $request = $this->requestStack->getCurrentRequest();

    if ($affiliate_id = $this->getAffiliateId($affiliate)) {
      // Register the click.
      $click = $this->entityTypeManager->getStorage('affiliate_click')->create([
        'campaign' => $campaign->id(),
        'affiliate' => $affiliate_id,
        'hostname' => $request->getClientIp(),
        'referrer' => $request->server->get('HTTP_REFERER'),
        'destination' => $destination,
      ]);
      $click->save();
      return $click;
    }
    return FALSE;
  }

  /**
   * Adds an affiliate_conversion entity for an affiliate.
   *
   * The conversion amount in calculated in the preSave method of the
   * affiliate_conversion entity. To alter this with your own logic for
   * calculating commission you can implement
   * hook_affiliate_conversion_presave($conversion).
   *
   * @param string $type
   *   The conversion bundle name.
   * @param \Drupal\Core\Entity\EntityInterface $parent
   *   The parent entity the conversion was awarded for.
   * @param \Drupal\Core\Session\AccountInterface $affiliate
   *   The user account of the affiliate.
   * @param \Drupal\affiliate\Entity\AffiliateCampaignInterface $campaign
   *   The affiliate_campaign entity.
   *
   * @see \Drupal\affiliate\Entity\AffiliateConversion::preSave()
   */
  public function addConversion(string $type, EntityInterface $parent, AccountInterface $affiliate, ?AffiliateCampaignInterface $campaign = NULL, $amount = NULL, $currency = NULL) {
    if (!$campaign) {
      $campaign = $this->getDefaultCampaign();
    }
    if ($affiliate_id = $this->getAffiliateId($affiliate)) {
      $values = [
        'type' => $type,
        'affiliate' => $affiliate_id,
        'campaign' => $campaign->id(),
      ];
      $conversion = $this->entityTypeManager->getStorage('affiliate_conversion')
        ->create($values);
      $conversion->setParentEntity($parent);
      if (!is_null($amount)) {
        $conversion->setCommission($amount, $currency);
      }
      $conversion->save();
      return $conversion;
    }
    return FALSE;
  }

  /**
   *
   */
  public function getSessionAffiliate() {
    $id = $this->requestStack->getCurrentRequest()->cookies->get('affiliate_id');
    if ($id && is_numeric($id)) {
      return $this->entityTypeManager->getStorage('user')->load($id);
    }
    return NULL;
  }

  /**
   *
   */
  public function getSessionCampaign() {
    $id = $this->requestStack->getCurrentRequest()->cookies->get('campaign_id');
    if ($id && is_numeric($id)) {
      $campaign = $this->entityTypeManager->getStorage('affiliate_campaign')->load($id);
    }
    return !empty($campaign) ? $campaign : $this->getDefaultCampaign();
  }

}
