<?php

/**
 * @file
 * Contains view_page_domain_filter.module..
 */

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\ViewsData;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;
use Drupal\views\Plugin\views\filter\FilterPluginBase;


/**
 * Filters nodes on current domain_id
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("my_custom_domain_filter")
 */


class NodeDomainFilterPlugin extends FilterPluginBase {
  /**
   * {@inheritdoc}
   */
  public function init(Drupal\views\ViewExecutable $view, Drupal\views\Plugin\views\display\DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Node Domain filter');
  }

  public function query() {
    //Get the current domain.  
    $domain = domain_get_domain();


   $configuration = [
      'table' => 'node_access',
      'field' => 'nid',
      'left_table' => 'node_field_data',
      'left_field' => 'nid',
      'operator' => '='
    ];
    $join = Views::pluginManager('join')->createInstance('standard', $configuration);


    $this->query->addRelationship('node_access', $join, 'node_field_data');
    $this->query->addWhere('AND', 'node_access.gid', $domain->getDomainId());
  }
}