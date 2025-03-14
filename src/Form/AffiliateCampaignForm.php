<?php

namespace Drupal\affiliate\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the default form handler for the Affiliate Campaign entity.
 */
class AffiliateCampaignForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state, $user = NULL) {
    $form = parent::form($form, $form_state);
    $entity = $form_state->getFormObject()->getEntity();

    // Only allow users to change the affiliate and default status if they
    // can globally manage campaigns.
    if (!$this->currentUser()->hasPermission('manage global campaigns')) {
      $form['user_id']['#access'] = FALSE;
      $form['is_default']['#access'] = FALSE;
    }

    // If we are editing the default campaign, disable the checkbox.
    // You can only make it not the default by choosing another one as
    // the default.
    if ($entity->isDefault()) {
      $form['is_default']['#disabled'] = TRUE;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $saved = parent::save($form, $form_state);
    $form_state->setRedirectUrl($this->entity->toUrl('canonical'));

    switch ($saved) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created campaign %label.', [
          '%label' => $this->entity->label(),
        ]));
        break;

      default:
        $this->messenger()
          ->addMessage($this->t('Updated the %label campaign.', [
            '%label' => $this->entity->label(),
          ]));
    }

    return $saved;
  }

}
