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
class Layer_KML extends Layer {
	
	static $db = array (
		'URL' => 'Varchar(1024)'
	);	
	
	static $has_one = array(
		'kmlFile' => "File",
	);	

	function getJavaScript() {
		return $this->renderWith('JS_Layer_KML');
	}
	
	static function getFeatureInfoParserName() {
		return "GetFeatureTextPlainParser";
	}

	public function getCMSFields($params = null) {
		$fields = parent::getCMSFields($params);
				
		$fields->addFieldToTab('Root.KML', new FileIFrameField('kmlFile','KML File'));

		$fields->removeFieldFromTab("Root.FeatureTypes", "FeatureTypes");
		$fields->removeFieldFromTab("Root", "FeatureTypes");
		return $fields;
	}	

	function getFileName() {
		$value = $this->URL;
		
		if (!$value) {
			if ($this->kmlFile()) {
				$value = $this->kmlFile()->getAbsoluteURL();
			}
		}
		return $value;
	}

}