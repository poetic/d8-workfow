<?php

namespace Drupal\facets\Processor;

use Drupal\facets\FacetInterface;

/**
 * Processor runs after the build processor for sorting.
 */
interface SortProcessorInterface extends ProcessorInterface {

  /**
   * Runs after the build processor for sorting.
   *
   * @param \Drupal\facets\FacetInterface $facet
   *   The facet being changed.
   * @param \Drupal\facets\Result\Result[] $results
   *   The results being changed.
   *
   * @return \Drupal\facets\Result\Result[] $results
   *   The changed results.
   */
  public function sort(FacetInterface $facet, array $results);

}
