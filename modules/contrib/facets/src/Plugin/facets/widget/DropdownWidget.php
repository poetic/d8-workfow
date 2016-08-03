<?php

namespace Drupal\facets\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;

/**
 * The dropdown widget.
 *
 * @FacetsWidget(
 *   id = "select",
 *   label = @Translation("Dropdown"),
 *   description = @Translation("A configurable widget that shows a dropdown."),
 * )
 */
class DropdownWidget extends LinksWidget {

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

    /** @var \Drupal\facets\Result\Result[] $results */
    $results = $facet->getResults();
    $items = [];

    $configuration = $facet->getWidgetConfigs();
    $this->showNumbers = empty($configuration['show_numbers']) ? FALSE : (bool) $configuration['show_numbers'];
    $this->defaultOptionLabel = isset($configuration['default_option_label']) ? $configuration['default_option_label'] : '';

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
      '#attributes' => ['class' => ['js-facets-dropdown-links'], 'data-facet-default-option-label' => $this->defaultOptionLabel],
      '#cache' => [
        'contexts' => [
          'url.path',
          'url.query_args',
        ],
      ],
    ];
    $build['#attached']['library'][] = 'facets/drupal.facets.dropdown-widget';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, $config) {
    $message = $this->t('This widget requires "Make sure only one result can be shown." to be enabled to behave as a standard dropdown.');
    $form['warning'] = [
      '#markup' => '<div class="messages messages--warning">' . $message . '</div>',
    ];

    $form['show_numbers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show the amount of results'),
    ];

    $form['default_option_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default option label'),
    ];

    if (!is_null($config)) {
      $widget_configs = $config->get('widget_configs');
      if (isset($widget_configs['show_numbers'])) {
        $form['show_numbers']['#default_value'] = $widget_configs['show_numbers'];
      }
      if (isset($widget_configs['default_option_label'])) {
        $form['default_option_label']['#default_value'] = $widget_configs['default_option_label'];
      }
    }

    return $form;
  }

}
