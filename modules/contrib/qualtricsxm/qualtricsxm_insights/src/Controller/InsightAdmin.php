<?php

namespace Drupal\qualtricsxm_insights\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InsightAdmin extends ControllerBase implements ContainerInjectionInterface {

  /**
   * @var \Drupal\workflows\StateInterface
   */
  private $state;

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

  public function index() {
    $list = $this->state->get(QUALTRICSXM_INSIGHTS_STATE_KEY) ?: [];
    $headers = [
      $this->t('Label'),
      $this->t('Sample Rate'),
      $this->t('Sample'),
      $this->t('ID'),
      $this->t('Domain'),
      $this->t('Operations'),
    ];
    $rows = [];
    foreach ($list as $k => $insight) {
      $id = $insight['id'];
      // With bad id's things will fall apart so if things go south fix it.
      if (empty($id)) {
        unset($list[$k]);
        $this->state->set(QUALTRICSXM_INSIGHTS_STATE_KEY, $list);
        continue;
      }
      $rows[] = [
        $insight['label'],
        $insight['sample_rate'],
        $insight['sample'],
        $id,
        $insight['domain'],
        [
          'data' => [
            '#type' => 'dropbutton',
            '#links' => [
              'edit' => [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute('qualtricsxm_insights.edit', ['id' => $id], [
                  'query' => [
                    'destination' => \Drupal::destination()->get(),
                  ],
                ]),
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('qualtricsxm_insights.delete', ['id' => $id]),
              ],
            ],
          ],
        ],
      ];
    }

    return [
      '#theme' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('<a href="@add_link">Add</a> an insight to get started.', [
        '@add_link' => Url::fromRoute('qualtricsxm_insights.add')->toString(),
      ]),
    ];
  }

}
