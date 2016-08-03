<?php

/**
 * @file
 * Contains \Drupal\custom_migrate\Plugin\migrate\process\EntityReferencePlugin.
 */

namespace Drupal\custom_migrate\Plugin\migrate\process;

use Drupal\migrate\Row;
use Drupal\migrate\Plugin\SourceEntityInterface;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\node\Entity\Node;
use Drupal\migrate\Plugin\MigrateProcessInterface;

/**
 * @MigrateProcessPlugin(
 *   id = "entity_reference_plugin"
 * )
 */


class EntityReferencePlugin extends ProcessPluginBase{

    public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
        
        //grab the migration configuration and turn it into an array
        $row_array = $row->getSource();
        $migration_id = $row_array['migration_name'];
        $configuration = entity_load('migration', $migration_id);
        $configuration_array = $configuration->toArray();
        $content_type = $configuration_array['process'][$destination_property]['content_type'];
        $source_element = $configuration_array['process'][$destination_property]['source_element'];

        //creates an array with all element names from the configuration file.  The foreach below allows it to find the elemenents that start out with the name
        $content_type_source_array = array();
        $search_length = strlen($source_element);
        foreach ($row_array as $key => $value) {
            if (substr($key, 0, $search_length) == $source_element) {
                array_push($content_type_source_array, $value);
            }
        }

        $array_nid = array();
        foreach($content_type_source_array as $content_type_source){
          $nodes = \Drupal::entityQuery('node')->condition('type', $content_type)->condition('title', $content_type_source)->execute();
          $array_nid[] = array_pop($nodes);
        } 
        return $array_nid;
      }
}