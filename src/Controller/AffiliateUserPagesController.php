<?php

namespace Drupal\affiliate\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AffiliateUserPagesController.
 */
class AffiliateUserPagesController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\affiliate\AffiliateManager
   */
  protected $affiliateManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->currentUser = $container->get('current_user');
    $instance->affiliateManager = $container->get('affiliate.manager');
    return $instance;
  }

  /**
   * Affiliate Campaign add form page callback.
   */
  public function addCampaignPage(UserInterface $user) {
    // Show a create campaign entity form.
    $campaign = $this->entityTypeManager()->getStorage('affiliate_campaign')
      ->create([
        'affiliate' => $user->id(),
      ]);

    // Get the entity form.
    $formObject = $this->entityTypeManager()
      ->getFormObject('affiliate_campaign', 'add')
      ->setEntity($campaign);

    $build['form'] = \Drupal::formBuilder()->getForm($formObject);

    // Hide the default and user_id fields, they are only relevant on the
    // global campaign form.
    $build['form']['user_id']['#access'] = FALSE;
    $build['form']['is_default']['#access'] = FALSE;

    return $build;
  }

  /**
   * Affiliate campaign overview page access.
   */
  public function addCampaignPageAccess(UserInterface $user = NULL) {
    if ($user->id() != $this->currentUser->id() && !$this->currentUser->hasPermission('administer users')) {
      return AccessResult::forbidden();
    }

    $is_active = $this->affiliateManager->isActiveAffiliate($user);
    return AccessResult::allowedIf($is_active && $user->hasPermission('create affiliate_campaign entities'));
  }

  /**
   * Affiliate Center page callback.
   */
  public function overviewPage(UserInterface $user) {
    $url = Url::fromRoute('<front>');
    $url = affiliate_url_set_affiliate_params($url, $user->id());
    $build['intro']['#markup'] = '<p>' . $this->t('Your affiliate link is: @url', ['@url' => $url->toString()]) . '</p>';
    $build['placeholder']['#markup'] = $this->t('Affiliate overview goes here.');

    return $build;
  }

  /**
   * Access callback for overview page.
   */
  public function overviewPageAccess(UserInterface $user) {
    // Admins can view anyone.
    // @todo maybe a 'manage affiliates' perm so admins who dosent have
    //   'admin users' perm can edit affiliate user profile settings.
    if ($user->id() != $this->currentUser->id() && !$this->currentUser->hasPermission('administer users')) {
      return AccessResult::forbidden();
    }

    // The account viewed either belongs to an anonymous user, or isn't an
    // affiliate.
    if ($user->id() && $this->currentUser->id() == $user->id() && $this->affiliateManager->isActiveAffiliate($user)) {
      return AccessResult::allowed();
    }

    return AccessResult::allowedIfHasPermission($this->currentUser, 'administer users');
  }

}
