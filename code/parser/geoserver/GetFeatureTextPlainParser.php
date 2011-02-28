<?php
/**
 * @package mapping
 * @subpackage parser
 */

/**
 * GetFeatureTextPlainParser parses a GetFeature WMS response and creates 
 * a dataobjectset which will be used to render to map bubble.
 *
 * @package mapping
 * @subpackage parser
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class GetFeatureTextPlainParser extends GetFeatureParser implements IGetFeatureParser {

	public function canParse($featureName){
		$retValue = true;
		return $retValue;
	}
	
	/**
	 * Note: Filtering of columns is done later.
	 * 
	 * @param String $itemValue
	 * @return Array Map of all feature keys to values
	 */
	protected function parseFeature($itemValue) {
		$itemValue = preg_replace("/(.*) = (.*)\n/", "$1=$2;", $itemValue, -1);
	
		// parse the key-value pairs and populate the values into an arraydata
		// object				
		$pairs = preg_split("/;/", $itemValue, -1, PREG_SPLIT_NO_EMPTY);
		$pairs = str_replace ("\n","",$pairs);

		$feature = array('properties' => array());

		// iterate through the key-value set of this feature type and create
		// a valueSet dataobject set, which will be used to model a single
		// featuretype record.
		if (count($pairs) > 0) {
			if ($pairs[0] != '') {
				foreach($pairs as $item) {
					$value = preg_split("/=/", $item, -1, PREG_SPLIT_NO_EMPTY);	
					if ($value) {
						if (isset($value[0])) {
							$value[0] = trim($value[0]);
							if (!isset($value[1])) $value[1] = '';

							// When requesting feautre information as plain text,
							// the geoserver returns 'null' for empty values.
							// This condition below removes those 'null' strings
							// and replaces them with an empty string.
							if ($value[1] == 'null') $value[1] = '';
							
							// Skip geometry for privacy reasons
							if ($value[0] == 'the_geom') continue;
							
							$feature['properties'][$value[0]] = $value[1];
						}
					}
				}
			}
		}
		
		return $feature;
	}

	/**
	 * Parses the response text (in text/plain format), which is returned by a WMS.
	 *
	 * @param string $value the body of the response text.
	 *
	 * @throws GetFeatureTextPlainParser_Exception
	 *
	 * @return Array
	 */
	public function parse($value) {

		if(preg_match('/no features were found/', $value)) return false;
		
		$features = array();
		$featureTypeName = array();

		// remove delimiter from text string
		$items = preg_split("/--------------------------------------------/", $value, -1);

		// iterate through items (a combination of feature type "headlines" and the zero or more type records)
		if($items) foreach ($items as $item) {
			
			// An item should always start with a feature type "headline"
			if(preg_match("/Results for FeatureType '(.*)':\n/", $item, $matches)) {
				$featureTypeName = $matches[1];
			} else {
				if(!trim($item)) continue;
				
				// The above if condition should always be met before
				if(!$featureTypeName) throw new Exception('Invalid feature type');
				
				if (!$this->limit || $this->itemCount < $this->limit) {

					// check if we have at least one occasion of key = item touble.
					$tokens = preg_split("/ = /", $item);

					if (count($tokens) > 1) {
						$feature = $this->parseFeature($item);
						$feature = $this->applyNiceLabels($featureTypeName, $feature);

						if ($feature) {
							// TODO $feature['id'] is not available in WMS
							if ($this->canParse($featureTypeName)) {
								
								// add this feature to the list of all features of the current feature type.
								if (!isset($features[$featureTypeName])) {
									// Create new dataobjectset object to store features of the 
									// new feature-types.
									$features[$featureTypeName] = new DataObjectSet();									
								}

								$properties = new DataObjectSet();
								foreach($feature['properties'] as $k => $v)  {
									$properties->push(new ArrayData(array(
										'key' => $k,
										'value' => $v
									)));
								}
								
								// Add arraydata object into the dataobject set for the current
								// feature-type.

								$item = new ArrayData( array(
									'Properties' => $properties,
									'scope' => 1
								));


								$features[$featureTypeName]->push($item);
								$this->itemCount++;
							}
						}
					} else {
						// skip if we can not find a ' = ' delimiter in the $item string.
						// There is a high change that we identified a geoserver error
						// message.
						// => Throw an exception
						throw new GetFeatureTextPlainParser_Exception('Geoserver response can not be parsed.');
					}
				}

				// Count total amount regardless of limit restrictions
				$this->totalCount++;
			}
		}		
		return $features;
	}			
}

class GetFeatureTextPlainParser_Exception extends Exception {
}