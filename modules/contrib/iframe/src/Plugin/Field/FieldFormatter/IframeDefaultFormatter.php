<?php

/**
 * @file
 * Contains \Drupal\iframe\Plugin\Field\FieldFormatter\IframeDefaultFormatter.
 */

namespace Drupal\iframe\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Drupal\Core\Template\Attribute;

/**
 * @FieldFormatter(
 *  id = "iframe_default",
 *  label = @Translation("Title, over iframe (default)"),
 *  field_types = {"iframe"}
 * )
 */
class IframeDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'url' => '',
      'title' => '',
      'width' => '',
      'height' => '',
      'class' => '',
      'expose_class' => '',
      'frameborder' => '',
      'scrolling' => '',
      'transparency' => '',
      'tokensupport' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  /* Settings form after "manage form display" page, valid for one field of content type */
  /* USE only if any further specific-Formatter-fields needed */
  /*
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['width'] = array(
      '#type' => 'textfield',
      '#title' => t('width of an iframe'),
      '#default_value' => $this->getSetting('width'), # ''
      '#description' => t('iframes need fix width and height, only numbers are allowed.'),
      '#maxlength' => 4,
      '#size' => 4,
    );
    $element['height'] = array(
      '#type' => 'textfield',
      '#title' => t('height of an iframe'),
      '#default_value' => $this->getSetting('height'), # ''
      '#description' => t('iframes need fix width and height, only numbers are allowed.'),
      '#maxlength' => 4,
      '#size' => 4,
    );
    $element['class'] = array(
      '#type' => 'textfield',
      '#title' => t('Additional CSS Class'),
      '#default_value' => $this->getSetting('class'), # ''
      '#description' => t('When output, this iframe will have this class attribute. Multiple classes should be separated by spaces.'),
    );
    return $element;
  }
  */

  /**
   * {@inheritdoc}
   */
  /* summary on the "manage display" page, valid for one content type */
  /*
  public function settingsSummary() {
    $summary = array();

    $summary[] = t('Iframe default width: @width', array('@width' => $this->getSetting('width')));
    $summary[] = t('Iframe default height: @height', array('@height' => $this->getSetting('height')));

    return $summary;
  }
  */


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    $settings = $this->getSettings();
    $field_settings = $this->getFieldSettings();
    #$entity = $items->getEntity();
    #\iframe_debug(0, __METHOD__, $entity);


    foreach ($items as $delta => $item) {
      if (empty($item->url)) {
        continue;
      }
      if (!isset($item->title)) {
        $item->title = '';
      }
      $elements[$delta] = array(
        '#markup' => self::iframe_iframe($item->title, $item->url, $item),
        '#allowed_tags' => array('iframe', 'a', 'h3'),
      );
    }
    return $elements;
  }

  /*
   * like central function
   * form the iframe code
   */
  static public function iframe_iframe($text, $path, $item) {
    $options = array();
    $options['width'] = !empty($item->width)? $item->width : '100%';
    $options['height'] = !empty($item->height)? $item->height : '701';

    if (!empty($item->frameborder) && $item->frameborder > 0) {
        $options['frameborder'] = (int)$item->frameborder;
    }
    $options['scrolling'] = !empty($item->scrolling) ? $item->scrolling : 'auto';
    if (!empty($item->transparency) && $item->transparency > 0) {
        $options['transparency'] = (int)$item->transparency;
    }

    $htmlid = '';
    if (isset($item->htmlid) && !empty($item->htmlid)) {
      $htmlid = ' id="' . htmlspecialchars($item->htmlid) . '" name="' . htmlspecialchars($item->htmlid) . '"';
    }

    // Append active class.
    $options['class'] = !empty($item->class) ? $item->class : '';
    /*
    if ($path == $_GET['q'] || ($path == '<front>' && drupal_is_front_page())) {
      if (!empty($options['class'])) {
        $options['class'] .= ' active';
      }
      else {
        $options['class'] = 'active';
      }
    }
    */

    // Remove all HTML and PHP tags from a tooltip. For best performance, we act only
    // if a quick strpos() pre-check gave a suspicion (because strip_tags() is expensive).
    $options['title'] = !empty($item->title) ? $item->title : '';
    if (!empty($options['title']) && strpos($options['title'], '<') !== FALSE) {
      $options['title'] = strip_tags($options['title']);
    }
    $options_link = array(); $options_link['attributes'] = array();
    $options_link['attributes']['title'] = $options['title'];

    $drupal_attributes = new Attribute($options);

    if (\Drupal::moduleHandler()->moduleExists('token')) {
      // Token Support for field "url" and "title"
      $tokensupport = !empty($item->tokensupport) && $item->tokensupport >= 0? (int)$item->tokensupport : 0;
      if ($tokensupport > 0) {
        $text = \Drupal::token()->replace($text, array('node' => $GLOBALS['node'], 'user' => \Drupal::currentUser()));
      }
      if ($tokensupport > 1) {
        $path = \Drupal::token()->replace($path, array('node' => $GLOBALS['node'], 'user' => \Drupal::currentUser()));
      }
    }

    $output = 
      '<div class="' . (!empty($options['class'])? \Drupal\Component\Utility\SafeMarkup::checkPlain($options['class']) : '') . '">'
        . (empty($text)? '' : '<h3 class="iframe_title">' . (isset($options['html']) && $options['html'] ? $text : \Drupal\Component\Utility\SafeMarkup::checkPlain($text)) . '</h3>')
        . '<iframe src="' . htmlspecialchars(Url::fromUri($path, $options)->toString()) . '"'
          . $drupal_attributes->__toString()
          . $htmlid
        . '>'
        . t('Your browser does not support iframes. But You can use the following link.') . ' ' . \Drupal::l('Link', Url::fromUri($path, $options_link))
        . '</iframe>'
      . '</div>'
    ;
    return $output;
  }


}
