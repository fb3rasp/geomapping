<?php
/**
 * @package mapping
 * @subpackage parser
 */

/**
 * Parses feature request.
 *
 * @package mapping
 * @subpackage parser
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class GetFeatureParser {
	
	static protected $default_column_visibility = true;

	static function get_default_column_visibility() {
		return self::$default_column_visibility;
	}

	static function set_default_column_visibility($value) {
		self::$default_column_visibility = $value;
	}
	
	protected $itemCount = 0;
	
	protected $totalCount = 0;
	
	/**
	 * @var Int 
	 * This should be the same limit thats passed to the WFS/WMS requests.
	 * It is different from a "limit by feature type" as applied in {@link groupByType()}.
	 */
	protected $limit = null;
	
	/**
	 * @var Int Currently computed in memory because WFS doesn't support paging.
	 * Caution: Setting this only makes sense if you don't mix feature types in the response.
	 */
	protected $offset = null;

	/**
	 * Returns itemCount.
	 * @return int
	 */
	public function getItemCount() {
		return $this->itemCount;
	}

	/**
	 * Returns getTotalCount.
	 * @return int
	 */
	public function getTotalCount() {
		return $this->totalCount;
	}

	/**
	 * @return int
	 */
	public function getLimit() {
		return $this->limit;
	}
	
	/**
	 * @param int $value new limit value;
	 */
	public function setLimit($value) {
		$this->limit = $value;
	}
	
	/**
	 * @return int
	 */
	public function getOffset() {
		return $this->offset;
	}
	
	/**
	 * @param int $value
	 */
	public function setOffset($value) {
		$this->offset = $value;
	}
	
	/**
	 * Returns an array representation of all features, similiar to GeoJSON,
	 * but without the actual geometry information.
	 * 
	 * Cauton: Features can belong to different layers, see 'type' and 'id' columns.
	 * 'type' is assumed to have the layer name without a namespace,
	 * and map to a {@link OLFeatureType} entry.
	 * 
	 * Example:
	 * [0]=>
	 *   array(3) {
	 *     ["id"]=>
	 *     string(36) "MyOtherLayer.0"
	 *     ["type"]=>
	 *     string(34) "MyOtherLayer"
	 *     ["properties"]=>
	 *     array(6) {
	 *       ["STATE"]=>
	 *       string(2) "WA"
	 *       ["SUBURB"]=>
	 *       string(8) "Dianella"
	 *     }
	 *   }
	 *   [1]=>
	 *   array(3) {
	 *     ["id"]=>
	 *     string(36) "MyLayer.1"
	 *     ["type"]=>
	 *     string(34) "MyLayer"
	 *     ["properties"]=>
	 *     array(6) {
	 *       ["OF_SCHOOL"]=>
	 *       string(27) "Canning Vale Primary School"
	 *       ["P_CODE"]=>
	 *       int(6155)
	 * 			...
	 *     }
	 *   },
	 *   ...
	 * 
	 * @return array
	 */
	function parse($str) {
	}
	
	/**
	 * WFS/WMS requests can handle querying multiple
	 * layers at once, but don't group the data structures
	 * in the responses.  
	 * 
	 * @param DataObjectSet
	 * @param int $limit
	 *
	 * @return Array
	 */
	function groupByType($features, $limit = null) {
		// Group results by layer name = FeatureType
		$layers = array();
		$featuresByType = array();
		foreach($features as $feature) {
			$featureType = $this->getFeatureType($feature);
			
			// if featureType is not defined in the CMS, then skip it.
			if(!$featureType) continue;

			if(!isset($featuresByType[$featureType->Name])) $featuresByType[$featureType->Name] = array();
			
			// Don't allow more than a certain number of features. (WE WANT EVERY RETURNED FEATURE)
			//if($limit && count($featuresByType[$featureType->Name]) >= $limit) continue;
			$featuresByType[$featureType->Name]['items'][] = $feature;
		}
		
		// populate items into return-value
		$newFeatureByType = array();
		foreach($featuresByType as $featureType){
			
			// save total of features returned
			$count = count($featureType['items']);
			
			// limit the number of features to show
			$featureType['items'] = array_slice($featureType['items'],0,$limit);
			
			// add the total of features returned to the featureType so we know the total
			$featureType['count'] = $count;

			$newFeatureByType[$featureType['items'][0]['type']] = $featureType;
		}
		return $newFeatureByType;
	}
	
	function mapFeatureAttributes($featureTypeName, $feature) {
		user_error("mapFeatureAttributes deprecated. Use applyNiceLabels");
	}
	
	/**
	 * Uses {@link OLFeatureTypeLabel} to remap column names to their human-readable
	 * equivalents. Also blacklists or whitelists column names (based on {@link get_default_column_visibility()}).
	 * 
	 * @param Array
	 * @return Array
	 */
	function applyNiceLabels($featureTypeName, $feature) {
		$featureType = $this->getFeatureType($featureTypeName);

		$sortedProperties = array();
		$visibleByDefaut = self::get_default_column_visibility();

		// new approach to improve database performance. load and cache
		// all label objects and iterate through them via array-index.
		if($featureType) {
			$labels = $featureType->Labels('"IsGeometry" = 0','Sort,Label');
			$labelsArray = $labels->toArray('RemoteColumnName');

			foreach($feature['properties'] as $k => $v) {
				// skip the_geom column
				if ($k == 'the_geom') continue;
				
				$label = null;
				
				if (isset($labelsArray[$k])) {
					$label = $labelsArray[$k];
				}
				if($label) {
					unset($feature['properties'][$k]);
					if($label->Retrieve || (!$label->Retrieve && $label->Visible)) {
						$feature['properties'][$label->Label] = $v;
					}
				} else {
					// If label doesnt exist, rely on default visibility settings
					if(!$visibleByDefaut) unset($feature['properties'][$k]);
				}
			}

			// add extra labels from cms (composite fields)
			$feature = $this->extraCmsAttributes($feature,$featureType);
		}
		return $feature;
	}
	
	/**
	 * We have feature with attributes from geoserver, now we need to embed attributes added in the CMS
	 * @param Array $feature. array with feature type name and its attributes
	 * @param Object $featureType. OLFeatureType to retrieve attributes from CMS.
	 * @return Array $feature, with attributes from CMS. 
	 */
	function extraCmsAttributes($feature,$featureType){
		$externalAttr = $feature['properties'];
		$sortedProperties = array();
		$cmsAttrs = $featureType->Labels('"IsGeometry" = 0','Sort,Label');

		// loop through cms labels
		foreach($cmsAttrs as $cmsAttr){
			
			$sortedArray = array();
			if(!array_key_exists($cmsAttr->Label,$externalAttr) && $cmsAttr->Visible){
				$tempValue = $cmsAttr->RemoteColumnName;
				// if the label exists in the CMS but not in geoserver check if it has variables 
				if(preg_match_all('/\$[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $tempValue, $matches)){
					foreach($matches as $subMatches){
						foreach($subMatches as $subMatch){
							// Hack to avoid variables with _ being replaced by a variable with the same name 
							// but without _
							// We put the valiables with _ at the beginning of the array, so they will be
							// reaplaced first.
							if(strpos($subMatch,"_")) array_unshift($sortedArray,$subMatch);
							else array_push($sortedArray,$subMatch);
						}
					}
					
					foreach($sortedArray as $subMatch){
						$subMatchClean = str_replace('$','',$subMatch);
						foreach($externalAttr as $key => $value){
							if($subMatchClean == $key){
									
								// we need to get rid of not visible labels since we added visible and composite before
								$tempLabels = $featureType->Labels("\"RemoteColumnName\" = '" . $subMatchClean . "'");
								$tempLabel = $tempLabels->First();
								if($tempLabel && !$tempLabel->Visible) unset($feature['properties'][$key]);
									
								// replace variables with values
								$tempValue = str_replace($subMatch,$value,$tempValue);									
								continue;
							}
								
						}
					}
					
				}

				$feature['properties'][$cmsAttr->Label] = $tempValue;
			} 
			if($cmsAttr->Visible) {
				$sortedProperties[$cmsAttr->Label] = $feature['properties'][$cmsAttr->Label];
			}
		}
		$feature['properties'] = $sortedProperties;
		return $feature;		
	}
	
	
	/**
	 * @param Array Feature in array notation, see parse()
	 *
	 * @throws GetFeatureParser_Exception
	 *
	 * @return OLFeatureType
	 */
	function getFeatureType($featureTypeName) {
		if(empty($featureTypeName)) {
			throw new GetFeatureParser_Exception(sprintf('Featuretype cannot be found for %s', $featureTypeName));
		}

		// This is cached in the ORM to not expensive to call for multiple rows
		return DataObject::get_one("FeatureType",sprintf("\"Name\" = '%s'", Convert::raw2sql($featureTypeName)));	
	}
}

class GetFeatureParser_Exception extends Exception {}