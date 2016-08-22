<?php

/**
 * @file
 * Contains \Drupal\link_class\Plugin\Field\FieldWidget\LinkclassFieldWidget.
 */

namespace Drupal\link_class\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Drupal\Component\Utility\Html;


/**
 * Plugin implementation of the 'link_class_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "link_class_field_widget",
 *   label = @Translation("Link with class"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkclassFieldWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    /** @var \Drupal\link\LinkItemInterface $item */
    $item = $items[$delta];
    $element['classes'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link classes'),
      '#default_value' => !empty($item->options['attributes']['class']) ? $item->options['attributes']['class'] : [],
      '#description' => $this->t('Add classes to the link. The classes must be separated by a space.'),
      '#size' => '30',
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * Does as LinkWidget but adds classes, to be stored in the options array.
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      $value['uri'] = static::getUserEnteredStringAsUri($value['uri']);
      $classes = explode(' ', Html::escape($value['classes']));
      $value['options']['attributes']['class'] = $classes;
    }
    return $values;
  }

}
