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
class MapObject extends DataObject {

	static $create_demo_map = true;

	static $module_path = 'geomapping';

	static $db = array(
		"Title" => "Varchar(255)",
		"Enabled" => "Boolean",
		"Lat" => "Float",
		"Long" => "Float",
		"ZoomLevel" => "Int",
		
		'Resolutions' => 'Varchar(1024)',
		'Projection' => "Enum(array('EPSG:4326','EPSG:900913'),'EPSG:4326')",
	);

	static $has_many = array(
		'Layers' => 'Layer'
	);

	static function set_create_demo_map($value) {
		self::$create_demo_map = $value;
	}

	static function get_create_demo_map() {
		return self::$create_demo_map;
	}

	static function set_module_path($value) {
		self::$module_path = $value;
	}

	static function get_module_path() {
		return self::$module_path;
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->addFieldsToTab('Root.Main', array (
	//		new LiteralField('MapPreview',sprintf('<a href=\'$s\' target=\'_mappreview\'>Map preview</a>'))
		));
		
		return $fields;
	}

	function getJavaScript() {
		$layers = $this->Layers("\"Enabled\" = 1");
		$js = '';
		
		foreach($layers as $layer) {
			$js .= $layer->getJavaScript();
			
		}
		return $js;
	}
	
	function GetResolutionsAsJSON() {
		return json_encode(explode(",", $this->getField('Resolutions')));
	}
	
	function getCategories() {
		
		$categories = DataObject::get(
			'LayerCategory', 
			sprintf('"Layer"."ID" IS NOT NULL AND "MapID" = %d', $this->ID), 
			'"Sort" ASC, "Title" ASC', 
			'LEFT JOIN "Layer" ON "LayerCategory"."ID" = "Layer"."LayerCategoryID"'
		);
		// TODO Workaround because the Postgres implementation fails at grouping...
		// See DataObject->buildSQL()
		if($categories) $categories->removeDuplicates('ID');
		
		return $categories;
	}

	/** 
	 * Create Example map, if required.
	 */
	public function requireDefaultRecords() {
		
		parent::requireDefaultRecords();
		
		// check if demo-map-page has been created
		$page = DataObject::get_one('MapPage',"Title = 'New York - Map Demo'");
				
		if ($page == false && self::get_create_demo_map()) {
		
			$map = new MapObject();
			$map->Title = 'Map - New York - Demo';
			$map->Enabled = true;
			$map->Lat = 40.71;
			$map->Long = -74;
			$map->ZoomLevel = 13;
			$map->Resolutions = "0.703125, 0.3515625, 0.17578125, 0.087890625, 0.0439453125, 0.02197265625, 0.010986328125, 0.0054931640625, 0.00274658203125, 0.001373291015625, 6.866455078125E-4, 3.4332275390625E-4, 1.71661376953125E-4, 8.58306884765625E-5, 4.291534423828125E-5, 2.1457672119140625E-5, 1.0728836059570312E-5, 5.364418029785156E-6, 2.682209014892578E-6, 1.341104507446289E-6, 6.705522537231445E-7, 3.3527612686157227E-7, 1.6763806343078613E-7, 8.381903171539307E-8, 4.190951585769653E-8, 2.0954757928848267E-8, 1.0477378964424133E-8, 5.238689482212067E-9, 2.6193447411060333E-9, 1.3096723705530167E-9, 6.548361852765083E-10";
			$map->Projection = "EPSG:4326";
			$map->write();
		
			$storage = new StorageGeoserver();
			$storage->Title = 'GeoServer - New York - Demo'; 
			$storage->URL = 'http://localhost:8080/geoserver/wms'; 
			$storage->URL_WFS = 'http://localhost:8080/geoserver/wfs'; 
			$storage->Enable = true;
			$storage->write();

			$layer = new Layer_GeoserverWMS();
			$layer->Title = 'New York - Demo';
			$layer->Enabled = true;
			$layer->Type = 'contextual';
			$layer->Visible = true;
			$layer->Queryable = false;
			$layer->Sort = 1;
			$layer->LayerName = 'tiger:giant_polygon,tiger:poly_landmarks,tiger:tiger_roads';
			$layer->Format = 'image/png';
			$layer->StorageID = $storage->ID;
			$layer->MapID = $map->ID;
			$layer->write();
		
			$layer = new Layer_GeoserverWFS();
			$layer->Title = 'New York - Point of Interests - Demo';
			$layer->Enabled = true;
			$layer->Visible = true;
			$layer->Sort = 500;
			$layer->Namespace = 'tiger';
			$layer->FeatureType = 'poi';
			$layer->Projection = 'EPSG:4326';
			$layer->Version = '1.1.0';
			$layer->StorageID = $storage->ID;
			$layer->MapID = $map->ID;
			$layer->write();	
		
			$style = new StyleMap();	
			$style->Name = 'Point of Interests - Demo';
			$style->default = 'new OpenLayers.Style({ pointRadius: 16, externalGraphic: "geomapping/images/icons/flag_blue.png" })';
			$style->select = 'new OpenLayers.Style({ pointRadius: 16, externalGraphic: "geomapping/images/icons/flag_blue.png" })';
			$style->temporary = 'new OpenLayers.Style({ pointRadius: 16, externalGraphic: "geomapping/images/icons/flag_blue.png" })';
			$style->write();

			$layers = $style->WFSLayers();
			$layers->add($layer);
			$layers->write();

			$page = new MapPage();
			$page->Title = 'New York - Map Demo';
			$page->MapID = $map->ID;
			$page->write();
			$page->doPublish();
			
			// create google maps demo page
			$map = new MapObject();
			$map->Title = 'Google-Maps - Demo';
			$map->Enabled = true;
			$map->Lat = 4970052.7560407;
			$map->Long = -8237950.5056889;
			$map->ZoomLevel = 14;
			$map->Resolutions = "156543.03390625, 78271.516953125, 39135.7584765625, 19567.87923828125, 9783.939619140625, 4891.9698095703125, 2445.9849047851562, 1222.9924523925781, 611.4962261962891, 305.74811309814453, 152.87405654907226, 76.43702827453613, 38.218514137268066, 19.109257068634033, 9.554628534317017, 4.777314267158508, 2.388657133579254, 1.194328566789627, 0.5971642833948135, 0.29858214169740677, 0.14929107084870338, 0.07464553542435169, 0.037322767712175846, 0.018661383856087923, 0.009330691928043961, 0.004665345964021981";
			$map->Projection = "EPSG:900913";
			$map->write();

			$layer = new Layer_GoogleMap();
			$layer->Title = 'Google Maps - Street Map';
			$layer->Enabled = true;
			$layer->Type = 'contextual';
			$layer->Visible = true;
			$layer->Queryable = false;
			$layer->Sort = 1;
			$layer->MapID = $map->ID;
			$layer->GMapTypeName = 'Map';
			$layer->write();		
			
			$layer = new Layer_GeoserverWFS();
			$layer->Title = 'Google Maps - New York - Point of Interests';
			$layer->Enabled = true;
			$layer->Visible = true;
			$layer->Sort = 500;
			$layer->Namespace = 'tiger';
			$layer->FeatureType = 'poi';
			$layer->Projection = 'EPSG:4326';
			$layer->Version = '1.1.0';
			$layer->StorageID = $storage->ID;
			$layer->MapID = $map->ID;
			$layer->write();	
			
			$layers = $style->WFSLayers();
			$layers->add($layer);
			$layers->write();
			
			$page = new MapPage();
			$page->Title = 'Google Maps - Demo';
			$page->MapID = $map->ID;
			$page->write();
			$page->doPublish();									
		}
	}

}