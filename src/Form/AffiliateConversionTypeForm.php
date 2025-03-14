<?php

namespace Drupal\affiliate\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for creating affiliate conversion type bundles.
 */
class AffiliateConversionTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $conversion_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $conversion_type->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $conversion_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\affiliate\Entity\AffiliateConversionType::load',
      ],
      '#disabled' => !$conversion_type->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#rows' => 2,
      '#title' => $this->t('Description'),
      '#default_value' => $conversion_type->getDescription(),
    ];

    $form['label_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label Pattern'),
      '#maxlength' => 255,
      '#default_value' => $conversion_type->getLabelPattern(),
      '#required' => TRUE,
      '#description' => $this->t('The pattern to use for the title of each conversion entity. The parent entity values are accessible at [affiliate_conversion:parent:*]'),
    ];

    $form['tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['affiliate_conversion'],
    ];


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
    $conversion_type = $this->entity;

    $status = $conversion_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()
          ->addMessage($this->t('Created the %label Comversion type.', [
            '%label' => $conversion_type->label(),
          ]));
        break;

      default:
        $this->messenger()
          ->addMessage($this->t('Saved the %label Conversion type.', [
            '%label' => $conversion_type->label(),
          ]));
    }
    $form_state->setRedirectUrl($conversion_type->toUrl('collection'));
    return $status;
  }

}
