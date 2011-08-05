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
	
}

/**
 *
 */
class MapPage_Controller extends Page_Controller {

	/**
	 * Initialisation function that is run before any action on the controller is called.
	 */
	public function init() {
		parent::init();
		$this->extend('extendInit');
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

