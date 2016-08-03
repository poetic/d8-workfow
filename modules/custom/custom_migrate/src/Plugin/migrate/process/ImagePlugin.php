<?php

/**
 * @file
 * Contains \Drupal\custom_migrate\Plugin\migrate\process\ImagePlugin.
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
use \Drupal\file\Entity\File;

/**
 * @MigrateProcessPlugin(
 *   id = "image_plugin"
 * )
 */


class ImagePlugin extends ProcessPluginBase{

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {    

    //grab the migration configuration and turn it into an array
    $row_array = $row->getSource();
    $migration_id = $row_array['migration_name'];
    $configuration = entity_load('migration', $migration_id);
    $configuration_array = $configuration->toArray();

    //creates an array with all element names from the configuration file.  The foreach below allows it to find the elemenents that start out with the name
    $source_element = $configuration_array['process'][$destination_property]['source_element'];
    $source_array = array();
    $search_length = strlen($source_element);
    foreach ($row_array as $key => $value) {
        if (substr($key, 0, $search_length) == $source_element) {
            array_push($source_array, $value);
        }
    }
    $array_nid = array();
    foreach($source_array as $source){
        $source = str_replace(' ','%20',$source);
        $data = file_get_contents($source);
        $file = file_save_data($data, 'public://' . basename($source), FILE_EXISTS_REPLACE);
        if ($file) {
            $nodes = $file->id();
            array_push($array_nid,$nodes);
        }
    } 
    return $array_nid;
  }
}