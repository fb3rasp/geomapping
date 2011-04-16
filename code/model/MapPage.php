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
class MapPage extends Page {

	static $has_one = array(
		'Map' => 'MapObject'
	);
	
	static $css_map_page = '/css/MapPage.css';

	static $css_map_bubble = '/css/layout.css';
	
	static $css_map_layerlist = '/css/LayerList.css';
	
	static function get_css_map_page() {
		return self::$css_map_page;
	}

	static function get_css_map_bubble() {
		return self::$css_map_bubble;
	}
	
	static function get_css_map_layerlist() {
		return self::$css_map_layerlist;
	}
	
	static function set_css_map_layerlist($value) {
		self::$css_map_layerlist = $value;
	}
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$items = array();
		$maps  = DataObject::get("MapObject");
		if ($maps) $items = $maps->map('ID','Title');

		$fields->addFieldsToTab("Root.Content.OpenLayers", 
			array(
				new LiteralField("MapLabel","<h2>Map Selection</h2>"),
				// Display parameters
				new CompositeField( 
					new CompositeField( 
						new LiteralField("DefLabel","<h3>Default OpenLayers Map</h3>"),
						new DropdownField("MapID", "Map", $items, $this->MapID, null, true)
					)
				)
			)
		);
		return $fields;
	}
	
	function getJavaScript() {		
		return $this->renderWith('JS_MapPage');
	}
}

/**
 *
 */
class MapPage_Controller extends Page_Controller {

	static function GoogleMapAPIKey() {
		global $googlemap_api_keys;
		$environment = Director::get_environment_type();

		$api_key = null;
		$host = $_SERVER['HTTP_HOST'];
		if (isset($googlemap_api_keys["$environment"])) {
			$api_key = $googlemap_api_keys["$environment"];
		} elseif (isset($googlemap_api_keys[$host])) {
			$api_key = $googlemap_api_keys[$host];
		}
		return $api_key;
	}
	
	function init() {
		parent::init();

		// Check that the class exists before trying to use it
		if (!class_exists('CommandFactory')) {
		    user_error('MapPage_Controller::init() - Please install the command-pattern module from github: git@github.com:silverstripe-labs/silverstripe-commandpattern.git.');
			die();
		}

		$js_files = array(
			'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js',
			MapObject::get_module_path().'/thirdparty/jquery-ui-1.7.2.custom.min.js',
			MapObject::get_module_path().'/thirdparty/jquery.entwine/dist/jquery.entwine-dist.js',
			MapObject::get_module_path().'/thirdparty/jquery.metadata/jquery.metadata.js',
		);

		foreach($js_files as $file) {
			Requirements::javascript($file);
		}

		Requirements::javascript(MapObject::get_module_path()."/thirdparty/openlayers_dev/OpenLayers.js");

		$js_files = array(
			MapObject::get_module_path()."/javascript/MapWrapper.js",
			MapObject::get_module_path().'/javascript/LayerList.js',
			MapObject::get_module_path()."/javascript/WMSFeatureInfo.js",
			MapObject::get_module_path()."/javascript/WFSFeatureInfo.js",
			MapObject::get_module_path()."/javascript/MapPopup.js",
			MapObject::get_module_path()."/javascript/control/GeoserverGetFeatureInfo.js"
		);
		foreach($js_files as $file) {
			Requirements::javascript($file);
		}
		// Requirements::combine_files('mapper.js', $js_files);

		Requirements::combine_files('mapper.css',array(
			MapObject::get_module_path().'/css/MapStyle.css',
			MapObject::get_module_path()."/".MapPage::get_css_map_page(),
			MapObject::get_module_path()."/".MapPage::get_css_map_bubble(),
			MapObject::get_module_path()."/".MapPage::get_css_map_layerlist(),
		));

		// we need to add call to js maps somehow, any better way?
		$googleCheck = DataObject::get_one('Layer_GoogleMap',"MapID = ".$this->MapID." AND \"Enabled\" = 1");
		if($googleCheck){
			$api_key = self::GoogleMapAPIKey();
			Requirements::javascript("http://maps.google.com/maps?file=api&amp;v=2&amp;key={$api_key}&amp;sensor=true");
		}

		$page = $this->data();		
		$jscript = $page->getJavaScript();
		Requirements::customScript($jscript);
	}	


	/**
	 */
	function getCategoriesByLayerType($layertype) {
		$map = $this->dataRecord->Map();
		$categories = $map->getCategories();
		$retValue = new DataObjectSet();

		if($categories) {
			foreach($categories as $category) {
 				$layers = $category->getEnabledLayers($layertype);
				if ($layers->Count()) {
					$category->layers = $layers;
					$retValue->push($category);
				}
			}
		}

		return $retValue;
	}

	/**
	 */
	function getOverlayCategories() {
		return $this->getCategoriesByLayerType('overlay');
	}

	/**
	 */
	function getBackgroundCategories() {
		return $this->getCategoriesByLayerType('background');
	}	
	
	/**
	 */
	function getContextualCategories() {
		return $this->getCategoriesByLayerType('contextual');
	}
	
	/**
	 * Partial caching key. This should include any changes that would influence 
	 * the rendering of LayerList.ss
	 * 
	 * @return String
	 */
	function CategoriesCacheKey() {
		return implode('-', array(
			$this->Map()->ID, 
			DataObject::Aggregate("LayerCategory")->Max("LastEdited"), 
			DataObject::Aggregate('Layer')->Max("LastEdited"),
			$this->request->getVar('layers')
		));
	}
}

