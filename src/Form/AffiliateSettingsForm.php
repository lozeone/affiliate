<?php

namespace Drupal\affiliate\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AffiliateSettingsForm.
 *
 * @ingroup affiliate
 */
class AffiliateSettingsForm extends ConfigFormBase {

  public function __construct(
    ConfigFactoryInterface $config_factory,
  ) {
    parent::__construct($config_factory);
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'affiliate.settings',
    ];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'affiliate_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
    $this->config('affiliate.settings')
      ->set('cookie_lifetime', $form_state->getValue('cookie_lifetime'))
      ->set('affiliate_key', $form_state->getValue('affiliate_key'))
      ->set('campaign_key', $form_state->getValue('campaign_key'))
      ->set('allow_owner', $form_state->getValue('allow_owner'))
      ->set('click_precedence', $form_state->getValue('click_precedence'))
      ->set('affiliate_code_type', $form_state->getValue('affiliate_code_type'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Defines the settings form for affilaites.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('affiliate.settings');
    $form['cookie_lifetime'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie Lifetime'),
      '#description' => $this->t('How long should the cookie be valid. <a href="http://php.net/strtotime">Strtotime</a> syntax. The value 0 means "until the browser is closed."'),
      '#default_value' => $config->get('cookie_lifetime'),
      '#required' => TRUE,
    ];
    $form['affiliate_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Affiliate ID key'),
      '#description' => $this->t('The url variable for the affiliate id'),
      '#default_value' => $config->get('affiliate_key'),
      '#required' => TRUE,
    ];
    $form['affiliate_code_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Affiliate variable type'),
      '#options' => [
        'user_id' => $this->t('User ID'),
        'username' => $this->t('Username'),
      ],
      '#description' => $this->t('The type of variable to use as the affiliate id.'),
      '#default_value' => $config->get('affiliate_code_type'),
      '#required' => TRUE,
    ];
    $form['campaign_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Campaign ID key'),
      '#description' => $this->t('The url variable for the campaign id'),
      '#default_value' => $config->get('campaign_key'),
      '#required' => TRUE,
    ];
    $form['allow_owner'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Record clicks and conversions from own account.'),
      '#description' => $this->t('Allow affiliates to act on their own links.'),
      '#default_value' => $config->get('allow_owner'),
    ];
    $form['click_precedence'] = [
      '#type' => 'radios',
      '#title' => $this->t('Click precedence'),
      '#options' => [
        'overwrite' => $this->t('Overwrite the affiliate cookie (new visit takes precedence)'),
        'deny' => $this->t('Reject the affiliate cookie (first visit takes precedence)'),
      ],
      '#description' => $this->t('When a user that already has an affiliate cookie visits the site through an affiliate link.'),
      '#default_value' => $config->get('click_precedence'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

}
