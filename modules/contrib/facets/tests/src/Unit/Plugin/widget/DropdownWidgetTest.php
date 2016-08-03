<?php

namespace Drupal\Tests\facets\Unit\Plugin\widget;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Url;
use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\widget\DropdownWidget;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * Unit test for widget.
 *
 * @group facets
 */
class DropdownWidgetTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \Drupal\facets\Plugin\facets\widget\DropdownWidget
   */
  protected $widget;

  /**
   * An array containing the results before the processor has ran.
   *
   * @var \Drupal\facets\Result\Result[]
   */
  protected $originalResults;

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\facets\Result\Result[] $original_results */
    $original_results = [
      new Result('llama', 'Llama', 10),
      new Result('badger', 'Badger', 20),
      new Result('duck', 'Duck', 15),
      new Result('alpaca', 'Alpaca', 9),
    ];

    foreach ($original_results as $original_result) {
      $original_result->setUrl(new Url('test'));
    }
    $this->originalResults = $original_results;

    $this->widget = new DropdownWidget();
  }

  /**
   * Tests widget without filters.
   */
  public function testNoFilterResults() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->originalResults);
    $facet->setWidgetConfigs(['show_numbers' => 1]);

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $this->assertEquals(['js-facets-dropdown-links'], $output['#attributes']['class']);

    $expected_links = [
      $this->buildLinkAssertion('Llama', 10),
      $this->buildLinkAssertion('Badger', 20),
      $this->buildLinkAssertion('Duck', 15),
      $this->buildLinkAssertion('Alpaca', 9),
    ];
    foreach ($expected_links as $index => $value) {
      $this->assertInternalType('array', $output['#items'][$index]);
      $this->assertEquals($value, $output['#items'][$index]['#title']);
      $this->assertInstanceOf(FormattableMarkup::class, $output['#items'][$index]['#title']);
      $this->assertEquals('link', $output['#items'][$index]['#type']);
      $this->assertEquals(['facet-item'], $output['#items'][$index]['#wrapper_attributes']['class']);
    }
  }

  /**
   * Build a formattable markup object to use in the other tests.
   *
   * @param string $text
   *   Text to display.
   * @param int $count
   *   Number of results.
   * @param bool $active
   *   Link is active.
   * @param bool $show_numbers
   *   Numbers are displayed.
   *
   * @return \Drupal\Component\Render\FormattableMarkup
   *   Formattable markup object for link.
   */
  protected function buildLinkAssertion($text, $count = 0, $active = FALSE, $show_numbers = TRUE) {
    $text = new FormattableMarkup('@text', ['@text' => $text, '@count' => $count]);
    if ($show_numbers !== FALSE) {
      $text->string .= ' <span class="facet-count">(@count)</span>';
    }
    if ($active) {
      $text->string = '<span class="facet-deactivate">(-)</span> ' . $text->string;
    }
    return $text;
  }

}
