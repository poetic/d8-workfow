<?php
/**
 * @file
 * Contains \Drupal\custom_migrate\XMLObject.
 */

namespace Drupal\custom_migrate;

class XMLObject extends \SimpleXMLIterator {

	public function current() {
		$row = parent::current();
		$row = $this->sxiToArray($row);
		return $row;
	}

	protected function sxiToArray($row){
		$array = json_decode(json_encode($row), true);
		$flat = $this->array_flat($array);

	  return $flat;
	}

	public function array_flat($array, $prefix = ''){
	  $result = array();

	  foreach ($array as $key => $value){
	    $new_key = $prefix . (empty($prefix) ? '' : '.') . $key;
	    if (is_array($value)){
	        $result = array_merge($result, $this->array_flat($value, $new_key));
	    }else{
	      $result[$new_key] = $value;
	    }
	  }
	  return $result;
	}
}



