<?php

namespace Drupal\affiliate\EventSubscriber;

use Drupal\affiliate\AffiliateManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribe to KernelEvents::RESPONSE events.
 *
 * Logs clicks if the request has the affiliate/campaign parameter
 * Sets the Affiliate cookie.
 */
class AffiliateRequestSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * AffiliateManager definition.
   *
   * @var \Drupal\affiliate\AffiliateManager
   */
  protected $affiliateManager;

  /**
   * The affiliate config settings.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManager $entity_type_manager, AffiliateManager $affliate_click_manager, ConfigFactory $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->affiliateManager = $affliate_click_manager;
    $this->config = $config_factory->get('affiliate.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // $events[KernelEvents::REQUEST][] = ['onRequest'];
    $events[KernelEvents::RESPONSE][] = ['onResponse'];
    return $events;
  }

  /*  public function onRequest(RequestEvent $event) {
  $request = $event->getRequest();
  $response = $event->getResponse();
  dsm($response, 'response');
  dsm($request, '$request');
  }*/

  /**
   * Handles the query-string-style urls (for example, node/20?a=190&c=22).
   *
   * @todo Apparently this is called multiple times per page request. Which in
   *   turn inserts multiple clicks. So I am setting a static variable and only
   *   doing things if it's not set. KernelEvents::REQUEST only fires once, but
   *   there is no response to set the cookie. So unless there is a more
   *   appropriate event to use, or I'm overlooking something this will do for
   *   now.
   */
  public function onResponse(ResponseEvent $event) {
    static $checked;
    if ($checked) {
      return;
    }
    if (!$this->config->get('affiliate_key')) {
      return;
    }

    $request = $event->getRequest();
    $response = $event->getResponse();
    $affiliate_code = $request->get($this->config->get('affiliate_key'));

    if ($affiliate_code) {
      if ($this->config->get('click_precedence') == 'overwrite' || !$this->affiliateManager->getStoredAffiliateCode()) {
        /** @var \Drupal\user\UserInterface $affiliate */
        $affiliate = $this->affiliateManager->getAccountFromCode($affiliate_code);
        $campaign_code = $request->get($this->config->get('campaign_key'));
        if ($affiliate) {
          $campaign = $this->affiliateManager->getCampaignFromCode($campaign_code);
          $click = $this->affiliateManager->registerClick($affiliate, $campaign, $request->getPathInfo());
          // If our click was successful, set a cookie.
          if ($click) {
            $cookie_lifetime = strtotime('+' . $this->config->get('cookie_lifetime'));
            $affiliate_code = $this->config->get('affiliate_code_type') == 'username' ? $affiliate->getAccountName() : $affiliate->id();
            $response->headers->setCookie(new Cookie('affiliate_id', $affiliate_code, $cookie_lifetime));
            $response->headers->setCookie(new Cookie('affiliate_campaign', $campaign->id(), $cookie_lifetime));
          }
        }
      }
    }
    $checked = TRUE;
  }

}
