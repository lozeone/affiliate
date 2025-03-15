<?php

namespace Drupal\affiliate;

use Drupal\affiliate\Entity\AffiliateCampaignInterface;
use Drupal\Core\Config\ConfigFactory;
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
    // Check if the affiliate is the same as the current user.
    if (!$this->config->get('allow_owner') && $affiliate->id() == $this->currentUser->id()) {
      return FALSE;
    }

    $request = $this->requestStack->getCurrentRequest();
    // Register the click.
    $click = $this->entityTypeManager->getStorage('affiliate_click')->create([
      'campaign' => $campaign->id(),
      'affiliate' => $affiliate->id(),
      'hostname' => $request->getClientIp(),
      'referrer' => $request->server->get('HTTP_REFERER'),
      'destination' => $destination,
    ]);
    $click->save();
    return $click;
  }

  /**
   * Gets the affiliate code from the cookie.
   *
   * @return string
   */
  public function getStoredAffiliateCode() {
    return $this->requestStack->getCurrentRequest()->cookies->get('affiliate_id');
  }

  /**
   * Gets the affiliates user account from the cookie.
   *
   * @return \Drupal\user\UserInterface|null
   *   The affiliates user entity or null
   */
  public function getStoredAccount() {
    if ($affiliate_code = $this->getStoredAffiliateCode()) {
      return $this->getAccountFromCode($affiliate_code);
    }
    return NULL;
  }

  /**
   * Gets the user account from the code variable.
   *
   * The code could either be the user id or the username depending on the
   * config settings.
   *
   * @param string|int $account_code
   *   Either a username or a user_id.
   *
   * @return \Drupal\user\UserInterface|null
   *   The user account if it is an active affiliate.
   */
  public function getAccountFromCode($account_code) {
    if ($account_code) {
      switch ($this->config->get('affiliate_code_type')) {
        case 'username':
          $user = user_load_by_name($account_code);
          break;

        case 'user_id':
        default:
          if (is_numeric($account_code)) {
            $user = $this->entityTypeManager->getStorage('user')->load($account_code);
          }
          break;
      }
      // Check that this account is a valid affiliate.
      if ($user && $this->isActiveAffiliate($user)) {
        return $user;
      }
    }

    return NULL;
  }

  /**
   * Gets the campaign code form the cookie.
   */
  public function getStoredCampaignCode() {
    return $this->requestStack->getCurrentRequest()->cookies->get('campaign_id');
  }

  /**
   * Gets the campaign entity from the cookie.
   */
  public function getStoredCampaign() {
    $campaign_code = $this->getStoredCampaignCode();
    return $this->getCampaignFromCode($campaign_code);
  }

  /**
   * Gets the campaign entity from the code variable.
   *
   * @param string|int $campaign_id
   *   The campaign entity id.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The campaign entity from the $campaign_id or the default entity
   *   if it is not valid.
   */
  public function getCampaignFromCode($campaign_id) {
    if ($campaign_id && is_numeric($campaign_id)) {
      // @todo make this alterable so we can use something other than the
      //   entity ID in the url. For example a string value
      //   of a field stored on campaign entity.
      /** @var  \Drupal\affiliate\Entity\AffiliateCampaign $campaign */
      $campaign = $this->entityTypeManager->getStorage('affiliate_campaign')->load($campaign_code);
      if ($campaign && $campaign->isPublished()) {
        return $campaign;
      }
    }
    return $this->getDefaultCampaign();
  }

  /**
   * Sets up a conversion entity.
   *
   * @param string $type
   *   The conversion bundle.
   *
   * @return \Drupal\affiliate\Entity\AffiliateConversion|null
   *   A new (unsaved) conversion with the referring affiliate and campaign
   *   populated or null if there is no referring affiliate account
   *
   * @todo this may not even be needed.
   *
   * Auto populates the affiliate and campaign if the current user is cookied.
   * Otherwise returns null.
   */
  public function createConversion($type) {
    $data = [
      'type' => $type,
      'affiliate' => $affiliate ?? $this->getStoredAccount(),
      'campaign' => $campaign ?? $this->getStoredCampaign(),
    ];
    if (empty($data['affiliate'])) {
      return NULL;
    }
    /** @var \Drupal\affiliate\Entity\AffiliateConversion $conversion */
    $conversion = $this->entityTypeManager->getStorage('affiliate_conversion')->create($data);
    return $conversion;
  }

}
