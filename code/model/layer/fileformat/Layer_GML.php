<?php
/**
 * @package mapping
 * @subpackage geoserver
 */

/**
 *
 * @package geoviewer
 * @subpackage fileformat
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class Layer_GML extends Layer {
	
	static $db = array (
		'URL' => 'Varchar(1024)'
	);	
	
	static $has_one = array(
		'gmlFile' => "File",
	);	

	function getJavaScript() {
		return $this->renderWith('JS_Layer_GML');
	}
	
	static function getFeatureInfoParserName() {
		return "GetFeatureTextPlainParser";
	}

	public function getCMSFields($params = null) {
		$fields = parent::getCMSFields($params);
				
		$fields->addFieldToTab('Root.GML', new FileIFrameField('gmlFile','GML File'));

		$fields->removeFieldFromTab("Root.FeatureTypes", "FeatureTypes");
		$fields->removeFieldFromTab("Root", "FeatureTypes");
		return $fields;
	}	

	function getFileName() {
		$value = $this->URL;
		
		if (!$value) {
			if ($this->gmlFile()) {
				$value = $this->gmlFile()->getAbsoluteURL();
			}
		}
		return $value;
	}
}