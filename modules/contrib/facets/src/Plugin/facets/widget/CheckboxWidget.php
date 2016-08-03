<?php

namespace Drupal\facets\Plugin\facets\widget;

use Drupal\facets\FacetInterface;
use Drupal\facets\Result\ResultInterface;

/**
 * The checkbox / radios widget.
 *
 * @FacetsWidget(
 *   id = "checkbox",
 *   label = @Translation("List of checkboxes"),
 *   description = @Translation("A configurable widget that shows a list of checkboxes"),
 * )
 */
class CheckboxWidget extends LinksWidget {

  /**
   * The facet the widget is being built for.
   *
   * @var \Drupal\facets\FacetInterface
   */
  protected $facet;

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $this->facet = $facet;
    $build = parent::build($facet);
    $build['#attributes']['class'][] = 'js-facets-checkbox-links';
    $build['#attached']['library'][] = 'facets/drupal.facets.checkbox-widget';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildListItems(ResultInterface $result) {
    $items = parent::buildListItems($result);
    $items['#attributes']['data-drupal-facet-item-id'] = $this->facet->getUrlAlias() . '-' . $result->getRawValue();
    return $items;
  }

}
