<?php

class Layer_WFS extends Layer {

	static $db = array(
		'Namespace' => 'Varchar',  // tiger
		'FeatureType' => 'Varchar',  // poi
		'Projection' => 'Varchar', // EPSG:4326
		'Version' => 'Varchar',    // 1.1.0
	);
	
	function getJavaScript() {
		throw new Layer_WFS_Exception('getJavaScript not implemented');
	}
	
	static function getFeatureInfoParserName() {
		throw new Layer_WFS_Exception('getFeatureInfoParserName not implemented');
	}	
}

class Layer_WFS_Exception extends Exception {
	
}