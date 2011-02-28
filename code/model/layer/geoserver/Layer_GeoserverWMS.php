<?php
/**
 * @package mapping
 * @subpackage geoserver
 */

/**
 * 
 *
 * @package mapping
 * @subpackage geoserver
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class Layer_GeoserverWMS extends Layer {
	
	static $db = array(
		'LayerName' => 'Varchar',
		'Format' => "Enum(array('image/png','image/jpeg','image/png24','image/gif'),'image/png')",
	);
	
	static $has_one = array(
		'Storage' => "StorageGeoserver"
	);
	
	function getJavaScript() {
		return $this->renderWith('JS_Layer_GeoserverWMS');
	}
	
	static function getFeatureInfoParserName() {
		return "GetFeatureTextPlainParser";
	}
}