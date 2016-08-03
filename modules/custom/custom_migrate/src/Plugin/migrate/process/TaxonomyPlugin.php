<?php

/**
 * @file
 * Contains \Drupal\custom_migrate\Plugin\migrate\process\TaxonomyPlugin.
 */

namespace Drupal\custom_migrate\Plugin\migrate\process;

use Drupal\migrate\Row;
use Drupal\migrate\Plugin\SourceEntityInterface;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\node\Entity\Node;
use Drupal\custom_migrate\Plugin\migrate\process\PluginBase;

/**
 * @MigrateProcessPlugin(
 *   id = "taxonomy_plugin"
 * )
 */


class TaxonomyPlugin extends ProcessPluginBase{

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    //grab the migration configuration and turn it into an array
    $row_array = $row->getSource();
    $migration_id = $row_array['migration_name'];
    $configuration = entity_load('migration', $migration_id);
    $configuration_array = $configuration->toArray();
    
    $taxonomy_type = $configuration_array['process'][$destination_property]['content_type'];
    $source_element = $configuration_array['process'][$destination_property]['source_element'];
    $taxonomy_name = $row_array[$taxonomy_type];

    //creates an array with all element names from the configuration file.  The foreach below allows it to find the elemenents that start out with the name
    $taxonomy_source_array = array();
    $search_length = strlen($source_element);
    foreach ($row_array as $key => $value) {
        if (substr($key, 0, $search_length) == $source_element) {
            array_push($taxonomy_source_array, $value);
        }
    }

    $array_nid = array();
    foreach($taxonomy_source_array as $taxonomy_source){
      $nodes = \Drupal::entityQuery('taxonomy_term')->condition('vid', $taxonomy_type)->condition('name', $taxonomy_source)->execute();
      $array_nid[] = array_pop($nodes);
    } 

    return $array_nid;
  }
}