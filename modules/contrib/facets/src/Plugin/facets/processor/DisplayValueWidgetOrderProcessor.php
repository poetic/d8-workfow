<?php

namespace Drupal\facets\Plugin\facets\processor;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\Processor\WidgetOrderPluginBase;
use Drupal\facets\Processor\WidgetOrderProcessorInterface;
use Drupal\facets\Result\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A processor that orders the results by display value.
 *
 * @FacetsProcessor(
 *   id = "display_value_widget_order",
 *   label = @Translation("Sort by display value"),
 *   description = @Translation("Sorts the widget results by display value."),
 *   default_enabled = TRUE,
 *   stages = {
 *     "sort" = 40
 *   }
 * )
 */
class DisplayValueWidgetOrderProcessor extends WidgetOrderPluginBase implements WidgetOrderProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * The transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TransliterationInterface $transliteration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->transliteration = $transliteration;
  }

  /**
   * Creates an instance of the plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('transliteration')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function sortResults(Result $a, Result $b) {
    $a = $this->transliteration->removeDiacritics($a->getDisplayValue());
    $b = $this->transliteration->removeDiacritics($b->getDisplayValue());
    if ($a == $b) {
      return 0;
    }
    return strnatcasecmp($a, $b);
  }

}
