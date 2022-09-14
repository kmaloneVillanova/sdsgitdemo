<?php

namespace Drupal\qualtricsxm_insights\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EditInsight extends FormBase implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  protected $id;

  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'qualtricsxm_insight_add';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $this->id = $id;
    $insight = $this->state->get(QUALTRICSXM_INSIGHTS_STATE_KEY)[$id];
    $form = [];

    $form['label'] = [
      '#title' => 'Label',
      '#type' => 'textfield',
      '#description' => 'A descriptive label.',
      '#default_value' => $insight['label'],
      '#required' => TRUE,
    ];
    $form['sample'] = [
      '#title' => 'Sample',
      '#type' => 'select',
      '#options' => [
        'r' => 'Sample page views',
        's' => 'Sample visitors',
      ],
      '#description' => 'How sampling functions.',
      '#default_value' => $insight['sample'],
      '#required' => TRUE,
    ];
    $form['sample_rate'] = [
      '#title' => 'Sample rate',
      '#type' => 'textfield',
      '#description' => 'Sampling rate. Value should be between 0-100.',
      '#default_value' => $insight['sample_rate'],
      '#required' => TRUE,
    ];
    $form['domain'] = [
      '#title' => 'Domain',
      '#type' => 'textfield',
      '#description' => 'Insight javascript domain.',
      '#default_value' => $insight['domain'],
      '#required' => TRUE,
    ];

    $form['exclude'] = [
      '#title' => 'Toggle exclusion',
      '#type' => 'select',
      '#options' => [
        '0' => 'Exclusive',
        '1' => 'Excluding',
      ],
      '#description' => 'Choose whether path pattern should be exclusive or excluding. If the pattern is exclusive insights will only show up if the pattern patches. If the pattern is excluding insights will show up on every page except if the pattern matches.',
      '#default_value' => $insight['exclude'],
    ];
    $form['path_pattern'] = [
      '#title' => 'Pattern',
      '#type' => 'textarea',
      '#description' => 'URL Patterns that describe where the insight should be active. One per line, wildcards allowed.',
      '#default_value' => $insight['path_pattern'],
    ];

    $form['update'] = [
      '#type' => 'submit',
      '#value' => 'Update',
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('sample_rate') > 100) {
      $form_state->setError($form['sample_rate'], 'Value must be between 1 and 100');
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $state = $this->state->get(QUALTRICSXM_INSIGHTS_STATE_KEY) ?: [];
    $state[$this->id] = [
      'id' => $this->id,
      'label' => $form_state->getValue('label'),
      'domain' => $form_state->getValue('domain'),
      'sample_rate' => $form_state->getValue('sample_rate'),
      'sample' => $form_state->getValue('sample'),
      'exclude' => $form_state->getValue('exclude'),
      'path_pattern' => $form_state->getValue('path_pattern'),
    ];
    $this->state->set(QUALTRICSXM_INSIGHTS_STATE_KEY, $state);
    $form_state->setRedirect('qualtricsxm_insights.overview');
  }

}
