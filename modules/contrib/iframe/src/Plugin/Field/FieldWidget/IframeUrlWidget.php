<?php

/**
 * @file
 * Contains \Drupal\iframe\Plugin\Field\FieldWidget\IframeUrlWidget.
 */

namespace Drupal\iframe\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'iframe' widget with url.
 *
 * @FieldWidget(
 *   id = "iframe_url",
 *   label = @Translation("URL only"),
 *   field_types = {"iframe"}
 * )
 */
class IframeUrlWidget extends IframeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $elements = parent::formElement($items, $delta, $element, $form, $form_state);
    unset($elements['width']);
    unset($elements['height']);

    return $elements;
  }
 
}

