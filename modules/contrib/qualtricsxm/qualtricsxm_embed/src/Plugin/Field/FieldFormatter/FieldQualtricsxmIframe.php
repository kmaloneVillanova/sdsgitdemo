<?php

namespace Drupal\qualtricsxm_embed\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * @file
 * Contains \Drupal\qualtricsxm_embed\Plugin\Field\FieldFormatter\FieldQualtricsxmIframe.
 */

/**
 * Plugin implementation of the 'field_qualtricsxm_iframe' formatter.
 *
 * @FieldFormatter(
 *  id = "field_qualtricsxm_iframe",
 *  label = @Translation("QualtricsXM iframe-embedding"),
 *  field_types = {"field_qualtricsxm_survey"},
 *  default_widget = "field_qualtricsxm_dropdown",
 * )
 */
class FieldQualtricsxmIframe extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $iframe_auto = $items->getSetting('auto')['qualtricsxm_embed_enable_iframe_auto_resize'];
    $iframe_width = !empty($items->getSetting('auto')['qualtricsxm_embed_width']) ?
      $items->getSetting('auto')['qualtricsxm_embed_width'] : "100%";
    $iframe_height = !empty($items->getSetting('auto')['qualtricsxm_embed_height']) ?
      $items->getSetting('auto')['qualtricsxm_embed_height'] : "900";
    $elements = [];

    foreach ($items as $delta => $item) {
      if (!empty($iframe_auto)) {

        $elements['#attached'] = [
          'library' => ['qualtricsxm_embed/qualtricsxm-libraries'],
        ];
      }

      $survey_id = $item->value;
      $embed_url = qualtricsxm_get_base_url() . "/$survey_id";
      $elements[$delta] = [
        '#type' => 'inline_template',
        '#template' => '<iframe src="{{ embed_url }}" height="{{ embed_height }}" width="{{ embed_width }}" frameborder="0" scrolling="no" class="qualtrics_iframe"></iframe>',
        '#context' => [
          'embed_url' => $embed_url,
          'embed_height' => $iframe_height,
          'embed_width' => $iframe_width,
        ],
      ];
    }
    return $elements;
  }

}
