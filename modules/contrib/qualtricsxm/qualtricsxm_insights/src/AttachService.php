<?php

namespace Drupal\qualtricsxm_insights;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Service for attaching insights scripts to pages.
 */
class AttachService {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Creates a AttachService object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\State\StateInterface $state
   *   State storage.
   */
  public function __construct(AccountInterface $current_user, PathMatcherInterface $path_matcher, CurrentPathStack $current_path, AliasManagerInterface $alias_manager, StateInterface $state) {
    $this->currentUser = $current_user;
    $this->pathMatcher = $path_matcher;
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function pageAttachments(&$page) {

    $state = $this->state->get(QUALTRICSXM_INSIGHTS_STATE_KEY) ?? [];
    foreach ($state as $insight) {
      if ($this->shouldTrack($insight['path_pattern'], $insight['exclude'])) {
        if (empty($page['#attached']['drupalSettings']['qualtricsxm_insights'])) {
          // Add the library and initialize settings.
          $page['#attached']['drupalSettings']['qualtricsxm_insights'] = [];
          $page['#attached']['library'][] = 'qualtricsxm_insights/insights';
        }
        $page['#attached']['drupalSettings']['qualtricsxm_insights'][] = [
          'domain' => $insight['domain'],
          'id' => $insight['id'],
          'sample' => $insight['sample'],
          'sample_rate' => $insight['sample_rate'],
        ];
      }
    }
  }

  /**
   * Resolve if the current page should be tracked by the rule.
   *
   * @param string $path_match_pattern
   *   A set of patterns separated by a newline.
   * @param bool $exclude
   *   Toggle whether pattern excludes or includes matches.
   *
   * @return bool
   */
  private function shouldTrack($path_match_pattern, $exclude) {
    if (!empty($path_match_pattern)) {
      $path = $this->currentPath->getPath();
      // Standardize to lower case for matching.
      $path_match_pattern = mb_strtolower($path_match_pattern);
      $path_alias = mb_strtolower($this->aliasManager->getAliasByPath($path));
      $page_match = $this->pathMatcher->matchPath($path_alias, $path_match_pattern);
      if ($path != $path_alias) {
        $page_match |= $this->pathMatcher->matchPath($path, $path_match_pattern);
      }
      return $exclude xor $page_match;
    }

    return $exclude;
  }

}
