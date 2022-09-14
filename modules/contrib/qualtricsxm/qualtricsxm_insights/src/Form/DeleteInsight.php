<?php

namespace Drupal\qualtricsxm_insights\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeleteInsight extends ConfirmFormBase implements ContainerInjectionInterface {

  private $id;

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
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'qualtricsxm_insight_delete';
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion() {
    $state = $this->state->get(QUALTRICSXM_INSIGHTS_STATE_KEY) ?: [];
    return $this->t('Are you sure you want to delete %name?', ['%name' => $state[$this->id]['label']]);
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('qualtricsxm_insights.overview');
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $state = $this->state->get(QUALTRICSXM_INSIGHTS_STATE_KEY) ?: [];
    unset($state[$this->id]);
    $this->state->set(QUALTRICSXM_INSIGHTS_STATE_KEY, $state);
    $form_state->setRedirect('qualtricsxm_insights.overview');
  }

}
