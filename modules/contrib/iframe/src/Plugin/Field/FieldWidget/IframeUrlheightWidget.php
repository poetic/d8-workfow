<?php

/**
 * @file
 * Contains \Drupal\iframe\Plugin\Field\FieldWidget\IframeUrlheightWidget.
 */

namespace Drupal\iframe\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *  id = "iframe_urlheight",
 *  label = @Translation("URL with height"),
 *  field_types = {"iframe"}
 * )
 */
class IframeUrlheightWidget extends IframeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $elements = parent::formElement($items, $delta, $element, $form, $form_state);
    unset($elements['width']);

    return $elements;
  }

}

