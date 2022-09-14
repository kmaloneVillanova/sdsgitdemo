<?php

namespace Drupal\qualtricsxm_insights\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddInsight extends FormBase implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $form['label'] = [
      '#title' => 'Label',
      '#type' => 'textfield',
      '#description' => 'A descriptive label.',
      '#required' => TRUE,
    ];
    $form['embed_code'] = [
      '#title' => 'Embed code',
      '#type' => 'textarea',
      '#description' => 'Drop embed code here so values can be parsed out',
      '#required' => TRUE,
    ];

    $form['exclude'] = [
      '#title' => 'Toggle exclusion',
      '#type' => 'select',
      '#options' => [
        '0' => 'Exclusive',
        '1' => 'Excluding',
      ],
      '#default_value' => '1',
      '#description' => 'Choose whether path pattern should be exclusive or excluding. If the pattern is exclusive insights will only show up if the pattern patches. If the pattern is excluding insights will show up on every page except if the pattern matches.',
    ];
    $form['path_pattern'] = [
      '#title' => 'Pattern',
      '#type' => 'textfield',
      '#description' => 'URL Patterns that describe where the insight should be active. One per line, wildcards allowed.',
      '#default_value' => "/admin\n/admin/*\n/batch\n/node/add*\n/node/*/*\n/user/*/*",
    ];
    $form['parse'] = [
      '#type' => 'submit',
      '#value' => 'Parse',
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $parsed_values = $this->parseEmbed($form_state->getValue('embed_code'));
    if ($parsed_values) {
      $form_state->setTemporaryValue('parsed_values', $parsed_values);
    }
    else {
      $form_state->setError($form['embed_code'], 'Failed to parse embed code.');
    }
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $state = $this->state->get(QUALTRICSXM_INSIGHTS_STATE_KEY) ?: [];
    $parsed_values = $form_state->getTemporaryValue('parsed_values');
    $state[$parsed_values['id']] = $parsed_values + [
        'label' => $form_state->getValue('label'),
        'exclude' => $form_state->getValue('exclude'),
        'path_pattern' => $form_state->getValue('path_pattern'),
      ];
    $this->state->set(QUALTRICSXM_INSIGHTS_STATE_KEY, $state);
    $form_state->setRedirect('qualtricsxm_insights.overview');
  }

  private function parseEmbed($string) {
    preg_match("/<div id='([a-zA-Z0-9_]+)'>/", $string, $matches);
    $id = $matches[1];
    preg_match('/https:\/\/([a-zA-Z0-9\-]+).siteintercept.qualtrics.com/', $string, $matches);
    $domain = $matches[1];
    preg_match('/new g\(([0-9]+),"([a-z])"/', $string, $matches);
    $sample_rate = $matches[1];
    $sample = $matches[2];
    return [
      'id' => $id,
      'domain' => $domain,
      'sample' => $sample,
      'sample_rate' => $sample_rate,
    ];
  }

}
