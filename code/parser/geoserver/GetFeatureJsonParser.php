<?php
/**
 * @package mapping
 * @subpackage parser
 */

/**
 * Parses a WFS getFeature request as JSON.
 *
 * @package mapping
 * @subpackage parser
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class GetFeatureJsonParser extends GetFeatureParser implements IGetFeatureParser {
	
	/**
	 * 
	 * @throws OLFeature_Controller_Exception
	 * 
	 * @param string $value the body of the response text.
	 * @return DataObjectSet
	 */
	public function parse($value) {
		$features = array();
		
		if(strpos($value, '<ows:ExceptionReport') !== FALSE) {
			throw new Exception('Could not parse JSON response: ' . $value);
			return;
		};

		$data = json_decode($value);
		$items = $data->features;

		$this->totalCount = count($features);
		
		// Excludes things like the_geom
		foreach($items as $i => $item) {
			// Count total amount regardless of limit or offset restrictions
			$this->totalCount++;
			
			if($this->offset && $i <= $this->offset) continue;
			
			if (!$this->limit || $this->itemCount < $this->limit) {
				$feature = array('properties' => array());
				$feature['id'] = $item->id;
				// Assumption: GeoServer composes feature ids as <layername>.<count>, e.g. MyLayer.1
				$feature['type'] = preg_replace('/\.\d*$/', '', $item->id);
				foreach($item->properties as $attr => $value) {
					$feature['properties'][$attr] = $value;
				}
				$features[] = $feature;
				$this->itemCount++;
			}
		}

		return $features;
	}		
	
}