<?php
/**
 * @package mapping
 * @subpackage layer
 */

/**
 * 
 *
 * @package mapping
 * @subpackage layer
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class Layer extends DataObject {
	
	static $db = array (
		"Enabled" => "Boolean",
		"Title" => "Varchar(255)",
		"Type" => "Enum(array('overlay','background','contextual'),'overlay')",
		"Visible" => "Boolean",
		"Queryable" => "Boolean",
		"Sort" => "Int"
	);

	static $summary_fields = array(
		'Enabled',
		'Title',
		'Type',
		'Map.Title',
		'LayerCategory.Title'
	);

	static $has_one = array(
		'Map' => 'MapObject',
		'LayerCategory' => 'LayerCategory'
	);
	
	static $has_many = array(
		'FeatureTypes' => 'FeatureType'
	);
	
	function isTransparent() {
		$result = 'false';
		if ($this->Type == 'overlay' || $this->Type == 'background') $result = 'true';
		return $result;
	}
	
	function isVisible() {
		if ($this->Visible) return 'true';
		return 'false';
	}
	
	function isQueryable(){
		if($this->Queryable) return 'true';
		return 'false';
	}

	/**
	 * Really just a temporary helper to make the filenames in our test data more readable.
	 * 
	 * @return String
	 */
	function getTitleNice() {
		return str_replace(array('_', '-'), ' ', $this->Title);
	}
		
	function getActionName($action) {
		$name = str_replace("Layer_","",$this->ClassName);
		return sprintf("%s_%s",$name,$action);
	}

}