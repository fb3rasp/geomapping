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
class StorageGeoserver extends DataObject {
	
	static $db = array (
		'Enable' => 'Boolean',
		'Title' => "Varchar(255)",
		'URL' => "Varchar(255)",
		'URL_WFS' => "Varchar(255)",
		'Username' => "Varchar(255)",
		'Password' => "Varchar(255)",
	);
	
	static $has_many = array (
		"Layers" => "Layer_GeoserverWMS"
	);
}
?>