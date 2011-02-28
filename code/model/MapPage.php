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
	
	static $css_map_page = 'mapping/css/MapPage.css';

	static $css_map_bubble = 'mapping/css/layout.css';
	
	static $css_map_layerlist = 'mapping/css/LayerList.css';
	
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

		Requirements::javascript('mapping/thirdparty/jquery-1.4.4.min.js');
		Requirements::javascript('mapping/thirdparty/jquery-ui-1.7.2.custom.min.js');


		Requirements::javascript('mapping/thirdparty/jquery.entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript('mapping/thirdparty/jquery.metadata/jquery.metadata.js');
		Requirements::javascript("mapping/thirdparty/openlayers_dev/lib/OpenLayers.js");

		Requirements::javascript("mapping/javascript/MapWrapper.js");
		Requirements::javascript('mapping/javascript/LayerList.js');
		Requirements::javascript("mapping/javascript/WMSFeatureInfo.js");
		Requirements::javascript("mapping/javascript/WFSFeatureInfo.js");
		Requirements::javascript("mapping/javascript/MapPopup.js");

		Requirements::javascript("mapping/javascript/control/GeoserverGetFeatureInfo.js");

		Requirements::css('mapping/css/MapStyle.css');
		Requirements::css(MapPage::get_css_map_page());
		Requirements::css(MapPage::get_css_map_bubble());
		
		if (MapPage::get_css_map_layerlist()) {
			Requirements::css(MapPage::get_css_map_layerlist());
		}
		
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
	 * Overload the map getter from the datamodel
	 * to inject visible states for layers based on GET parameters.
	 */
	function Categories() {
		$map = $this->dataRecord->Map();
		$categories = $map->getCategories();

		// Optionally set layer visible state from GET params
		$selectedLayerIds = explode(',', $this->request->getVar('layers'));
		if($categories) foreach($categories as $category) {
			$layers = $category->getOverlayLayersEnabled();
			if($layers) foreach($layers as $layer) {
			//	$layer->Visible = true; 
				// (
				// 	in_array($layer->ogc_name, $selectedLayerIds) 
				// 	// Only default to Visible database setting if 'layers' GET param isnt defined.
				// 	// Otherwise we assume the user wants to override these defaults.
				// 	|| ($layer->Visible && !$selectedLayerIds)
				// );
//				echo $layer->Title ." : ". $layer->isVisible();
			}
			// Works by object reference, so is accessible in the template
			$category->OverlayLayersEnabledAndVisible = $layers;
		}

		return $categories;
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

