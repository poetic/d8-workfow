<?php

namespace Drupal\facets\Plugin\facets\widget;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facets\FacetInterface;
use Drupal\facets\Result\ResultInterface;
use Drupal\facets\Widget\WidgetInterface;

/**
 * The links widget.
 *
 * @FacetsWidget(
 *   id = "links",
 *   label = @Translation("List of links"),
 *   description = @Translation("A simple widget that shows a list of links"),
 * )
 */
class LinksWidget implements WidgetInterface {

  use StringTranslationTrait;

  /**
   * A flag that indicates if we should display the numbers.
   *
   * @var bool
   */
  protected $showNumbers = FALSE;

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    /** @var \Drupal\facets\Result\Result[] $results */
    $results = $facet->getResults();
    $items = [];

    $configuration = $facet->getWidgetConfigs();
    $this->showNumbers = empty($configuration['show_numbers']) ? FALSE : (bool) $configuration['show_numbers'];

    foreach ($results as $result) {
      if (is_null($result->getUrl())) {
        $text = $this->extractText($result);
        $items[] = ['#markup' => $text];
      }
      else {
        $items[] = $this->buildListItems($result);
      }
    }

    $build = [
      '#theme' => 'item_list',
      '#items' => $items,
      '#attributes' => ['data-drupal-facet-id' => $facet->id()],
      '#cache' => [
        'contexts' => [
          'url.path',
          'url.query_args',
        ],
      ],
    ];

    if (!empty($configuration['soft_limit'])) {
      $build['#attached']['library'][] = 'facets/soft-limit';
      $build['#attached']['drupalSettings']['facets']['softLimit'][$facet->id()] = (int) $configuration['soft_limit'];
    }

    return $build;
  }

  /**
   * Builds a renderable array of result items.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   A result item.
   *
   * @return array
   *   A renderable array of the result.
   */
  protected function buildListItems(ResultInterface $result) {

    $classes = ['facet-item'];

    if ($children = $result->getChildren()) {
      $items = $this->prepareLink($result);

      $children_markup = [];
      foreach ($children as $child) {
        $children_markup[] = $this->buildChildren($child);
      }

      $classes[] = 'expanded';
      $items['children'] = [$children_markup];

      if ($result->isActive()) {
        $items['#attributes'] = ['class' => 'active-trail'];
      }
    }
    else {
      $items = $this->prepareLink($result);

      if ($result->isActive()) {
        $items['#attributes'] = ['class' => 'is-active'];
      }
    }

    $items['#wrapper_attributes'] = ['class' => $classes];

    return $items;
  }

  /**
   * Returns the text or link for an item.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   A result item.
   *
   * @return array
   *   The item, as a renderable array.
   */
  protected function prepareLink(ResultInterface $result) {
    $text = $this->extractText($result);

    if (is_null($result->getUrl())) {
      $link = ['#markup' => $text];
    }
    else {
      $link = new Link($text, $result->getUrl());
      $link = $link->toRenderable();
    }

    return $link;
  }

  /**
   * Builds a renderable array of a result.
   *
   * @param \Drupal\facets\Result\ResultInterface $child
   *   A result item.
   *
   * @return array
   *   A renderable array of the result.
   */
  protected function buildChildren(ResultInterface $child) {
    $text = $this->extractText($child);

    if (!is_null($child->getUrl())) {
      $link = new Link($text, $child->getUrl());
      $item = $link->toRenderable();
    }
    else {
      $item = ['#markup' => $text];
    }

    $item['#wrapper_attributes'] = ['class' => ['leaf']];

    return $item;
  }

  /**
   * {@inheritdoc}
   *
   * @todo This is inheriting nothing. We need a method on the interface and,
   *   probably, a base class.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, $config) {
    $widget_configs = !is_null($config) ? $config->get('widget_configs') : [];
    // Assure sane defaults.
    // @todo This should be handled upstream, in facet entity. Facet schema
    //   should be fixed and all configs should get sane defaults.
    $widget_configs += ['show_numbers' => FALSE, 'soft_limit' => 0];

    $form['show_numbers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show the amount of results'),
      '#default_value' => $widget_configs['show_numbers'],
    ];
    $options = [50, 40, 30, 20, 15, 10, 5, 3];
    $form['soft_limit'] = [
      '#type' => 'select',
      '#title' => $this->t('Soft limit'),
      '#default_value' => $widget_configs['soft_limit'],
      '#options' => [0 => $this->t('No limit')] + array_combine($options, $options),
      '#description' => $this->t('Limits the number of displayed facets via JavaScript.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType($query_types) {
    return $query_types['string'];
  }

  /**
   * Extracts the text for a result to display in the UI.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   The result to extract the text for.
   *
   * @return string
   *   The text to display.
   */
  protected function extractText(ResultInterface $result) {
    $text = new FormattableMarkup('@text', ['@text' => $result->getDisplayValue(), '@count' => $result->getCount()]);
    if ($this->showNumbers && $result->getCount()) {
      $text->string .= ' <span class="facet-count">(@count)</span>';
    }
    if ($result->isActive()) {
      $text->string = '<span class="facet-deactivate">(-)</span> ' . $text->string;
    }
    return $text;
  }

}
