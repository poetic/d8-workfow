<?php

/**
 * @file
 * Contains \Drupal\iframe\Plugin\Field\FieldFormatter\IframeAsurlwithuriFormatter.
 */

namespace Drupal\iframe\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\SafeMarkup;

/**
 * @FieldFormatter(
 *  id = "iframe_asurlwithuri",
 *  label = @Translation("A link with the uri as title"),
 *  field_types = {"iframe"}
 * )
 */
class IframeAsurlwithuriFormatter extends IframeDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    foreach ($items as $delta => $item) {
      if (empty($item->url)) {
        continue;
      }
      if (!isset($item->title)) {
        $item->title = '';
      }
      $linktext = $item->url;
      $elements[$delta] = array(
        '#markup' =>  \Drupal::l($linktext, Url::fromUri($item->url, ['title' => $item->title])),
        '#allowed_tags' => array('iframe', 'a', 'h3'),
      );
    }
    return $elements;
  }

}
