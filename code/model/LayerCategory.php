<?php
/**
 * @package mapping
 * @subpackage model
 */

/**
 * 
 *
 * @package mapping
 * @subpackage model
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class LayerCategory extends DataObject {

	static $db = array(
		'Title' => 'Text',
		'Sort' => 'Int'     // combined sorting with Layer->Sort
	);
	
	static $has_many = array(
		'Layers' => 'Layer'
	);
	
	static $casting = array(
		'TitleNice' => 'Text'
	);
	
	static $singular_name = 'Category';
	
	static $plural_name = 'Categories';
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$layersCTF = $fields->dataFieldByName('Layers');
		if ($layersCTF) {
			$layersCTF->setPermissions(array('show'));
		}
		return $fields;
	}

	/**
	 * @return ComponentSet
	 */
	function getOverlayLayersEnabled() {
		return $this->getComponents('Layers', '"Enabled" = 1 AND "Type" = \'overlay\'','"Sort" ASC');
	}

	/**
	 * Really just a temporary helper to make the filenames in our test data more readable.
	 * 
	 * @return String
	 */
	function getTitleNice() {
		return str_replace(array('_', '-'), ' ', $this->Title);
	}
}