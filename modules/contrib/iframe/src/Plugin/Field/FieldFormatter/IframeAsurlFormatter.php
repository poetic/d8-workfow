<?php /**
 * @file
 * Contains \Drupal\iframe\Plugin\Field\FieldFormatter\IframeAsurlFormatter.
 */

namespace Drupal\iframe\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;

/**
 * @FieldFormatter(
 *  id = "iframe_asurl",
 *  label = @Translation("A link with the given title"),
 *  field_types = {"iframe"}
 * )
 */
class IframeAsurlFormatter extends IframeDefaultFormatter {

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
      $linktext = !empty($item->title)? $item->title : $item->url;
      $elements[$delta] = array(
        '#markup' =>  \Drupal::l($linktext, Url::fromUri($item->url, ['title' => $item->title])),
        '#allowed_tags' => array('iframe', 'a', 'h3'),
      );
    }
    return $elements;
  }

}
