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
class GetFeatureCsvParser extends GetFeatureParser implements IGetFeatureParser {
	
	/**
	 * 
	 * @throws OLFeature_Controller_Exception
	 * 
	 * @param string $value the body of the response text.
	 * @return DataObjectSet
	 */
	public function parse($value) {
		
		$features = array();
		
		$tmpFile = tempnam(TEMP_FOLDER, 'GetFeatureCsvParser');
		file_put_contents($tmpFile, $value);
		
		$parser = new CSVParser($tmpFile);
		
		$this->totalCount = count($parser) - 1;
		
		// Excludes things like the_geom
		foreach($parser as $i => $item) {
			
			// Count total amount regardless of limit or offset restrictions
			$this->totalCount++;
			
			if($this->offset && $i <= $this->offset) continue;
			
			if (!$this->limit || $this->itemCount < $this->limit) {
				
				if(!isset($item['FID'])) return;
				$feature = array('properties' => array());
				
				$feature['id'] = $item['FID'];
				// Assumption: GeoServer composes feature ids as <layername>.<count>, e.g. MyLayer.1
				$feature['type'] = preg_replace('/\.\d*$/', '', $item['FID']);
				unset($item['FID']);
				$feature['properties'] = $item;
				
				$features[] = $feature;
				$this->itemCount++;
			}
		}
		
		unlink($tmpFile);
		return $features;
	}		
	
}