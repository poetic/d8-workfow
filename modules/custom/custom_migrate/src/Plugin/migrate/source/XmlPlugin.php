<?php

/**
 * @filetrue
 * Contains \Drupal\custom_migrate\Plugin\migrate\source\XmlPlugin.
 */

namespace Drupal\custom_migrate\Plugin\migrate\source;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;
use Drupal\custom_migrate\XMLObject;
use Symfony\Component\DomCrawler\Crawler;


/**
 * Source plugin for news node.
 *
 * @MigrateSource(
 *   id = "xml_plugin"
 * )
 */
class XmlPlugin extends SourcePluginBase {

	public function initializeIterator() {

	  $sxe = new \SimpleXMLElement($this->configuration['path'], NULL, TRUE);
	  $configuration = entity_load('migration', $this->configuration['migration_name']);
    $configuration_array = $configuration->toArray();

	  //convert to XML to find and replace the elements with the attributes
	  $toXML = $sxe->asXML();
	  //converts all tags lowercase
	 	$toXML = preg_replace_callback("/(<\/?[^!][^>]+)/", function ($matches) {return strtolower($matches[1]);}, $toXML);
	  $attributes = $configuration_array['source']['attributes'];
	  if(!$attributes == NULL){
	  	foreach($attributes as $attribute){
	  		$attribute = strtolower($attribute);
		  	$attribute_each = explode('|', $attribute);
				$toXML = preg_replace('/<' . $attribute_each[0] .' ' . $attribute_each[1] . '>(.*?)<\/' . $attribute_each[0] . '>/', '<' . $attribute_each[2] . '>$1</' . $attribute_each[2] . '>', $toXML);
			}
		}
		$new_sxe = simplexml_load_string($toXML);
	  $nodes = $new_sxe->xpath($this->configuration['base_query']);
	  $configuration = entity_load('migration', $this->configuration['migration_name']);
    $configuration_array = $configuration->toArray();
	  $xpath_map = $configuration_array['source']['xpath'];
	  // $xpath_map_values = array_values($xpath_map);
	  // $xpath_map_keys = array_keys($xpath_map);

	  $new_node = '<root>';
	  foreach($nodes as $node) {
	  	$new_node .= '<node>';
	  	foreach($xpath_map as $key=>$new_element_name){
	  		// $items_array = explode('|',$xpath_map_value);
	  		$new_element_name = strtolower($new_element_name);
	  		$xpath = str_replace('.','/',$new_element_name);
	  		$items = $node->xpath($xpath);
	  		$single_item = '';
	  		foreach($items as $item){
	  			$key = strtolower($key);
		  		$single_item .= str_replace($key, $new_element_name, $item->asXML());
		  	}
		  	$new_node .= $single_item;
  		}
  		$new_node .= '</node>';
	  	$xml .= $node->asXML();
	  }
	  $new_node .= '</root>';

	  $final = new XMLObject($new_node);
	  return $final;
	}

	public function getIDs() {
	  $ids = array();
	  foreach ($this->configuration['keys'] as $key) {

	    $ids[$key]['type'] = 'string';
	  }
	  return $ids;
	}

	public function __toString() {
	  return (string) $this->query;
	}

	public function fields() {
		return;
	}
}