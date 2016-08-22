<?php

namespace Drupal\facets\Plugin\facets\processor;

use Drupal\facets\Processor\WidgetOrderPluginBase;
use Drupal\facets\Processor\WidgetOrderProcessorInterface;
use Drupal\facets\Result\Result;

/**
 * A processor that orders the results by raw value.
 *
 * @FacetsProcessor(
 *   id = "raw_value_widget_order",
 *   label = @Translation("Sort by raw value"),
 *   description = @Translation("Sorts the widget results by raw value."),
 *   stages = {
 *     "sort" = 50
 *   }
 * )
 */
class RawValueWidgetOrderProcessor extends WidgetOrderPluginBase implements WidgetOrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function sortResults(Result $a, Result $b) {
    return strnatcasecmp($a->getRawValue(), $b->getRawValue());
  }

}
