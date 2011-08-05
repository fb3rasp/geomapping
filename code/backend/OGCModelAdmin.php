<?php
/**
 * @package mapping
 * @subpackage backend
 */

/**
 * OGC WebService - Model-Admin class.
 *
 * @package mapping
 * @subpackage backend
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class OGCModelAdmin extends ModelAdmin {

	static $menu_title = "Map Sources";
	
	static $url_segment = "mapsources";

	static $managed_models = array(
		"Layer_GoogleMap",
		"Layer_OpenStreetMap",
		"StorageGeoserver",
		"Layer_GeoserverWMS",
		"Layer_GeoserverWFS",
		"StyleMap",
	);

	static $allowed_actions = array(
	);
}
