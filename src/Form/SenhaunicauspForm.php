<?php

namespace Drupal\senhaunicausp\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SenhaunicauspForm.
 */
class SenhaunicauspForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'senhaunicausp.senhaunicausp',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'senhaunicausp_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('senhaunicausp.senhaunicausp');
    $form['key_id'] = [
      '#type' => 'key',
      '#title' => $this->t('key id'),
      '#description' => $this->t('key id'),
      '#default_value' => $config->get('key_id'),
    ];
    $form['secret_key'] = [
      '#type' => 'text_format',
      '#title' => $this->t('secret key'),
      '#description' => $this->t('secret key'),
      '#default_value' => $config->get('secret_key'),
    ];
    $form['callback_id'] = [
      '#type' => 'number',
      '#title' => $this->t('callback id'),
      '#description' => $this->t('callback id'),
      '#default_value' => $config->get('callback_id'),
    ];
    return parent::buildForm($form, $form_state);
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

    $this->config('senhaunicausp.senhaunicausp')
      ->set('key_id', $form_state->getValue('key_id'))
      ->set('secret_key', $form_state->getValue('secret_key'))
      ->set('callback_id', $form_state->getValue('callback_id'))
      ->save();
  }

}
