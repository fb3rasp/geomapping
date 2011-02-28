<?php
/**
 * @package mapping
 * @subpackage command
 */

/**
 * 
 *
 * @package mapping
 * @subpackage command
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class ImportFeatureTypeLabelsCommand extends ControllerCommand {
	
	static protected $xsd_namespace = "http://www.w3.org/2001/XMLSchema";

	static function set_xsd_namespace($url){
		self::$xsd_namespace = $url;
	}
	
	static function get_xsd_namespace(){
		return self::$xsd_namespace;
	}
	
	/**
	 * This method is called to update all FeatureTypeLabel objects of a
	 * given featuretype. This method sends a HTTP request to the WFS service
	 * to retrieve the xml description of the feature type and populates the 
	 * column information into the OLFeatureTypeLabel data-object.
	 *
	 * This method is called by @link addLayer, @link updateLayer and @link updateDataObject.
	 * NOTE: This method does not delete deprecated columns in the CMS.
	 *
	 * @param string $featureType Name of the feature-type (incl. namespace, i.e. tiger:poi).
	 *
	 * @return int Number of created/updated columns
	 *
	 * @throws ImportFeatureTypeLabelsCommand_Exception
	 */
	protected function createOrUpdateColumns($xml) {
		$count = 0;
		
		$parameters = $this->getParameters();
		$featureType = $parameters['FeatureType'];
		if (!$featureType) {
			throw new ImportFeatureTypeLabelsCommand_Exception("Fatal error: Unknown feature type.");			
		}

		$xml = explode('<?',$xml);
		$xml = "<?".$xml[1];
		
		$doc = new DOMDocument();
	  	$doc->loadXML($xml);

		$complexTypes = $doc->getElementsByTagNameNS(self::get_xsd_namespace(), "complexType" );
		if ($complexTypes->length > 1) {
			throw new ImportFeatureTypeLabelsCommand_Exception("Fatal error: WFS DescribeFeatureType returned invalid number of feature types: ".$complexTypes->length);			
		}

		if ($complexTypes->length == 1) {
			//
			// compare attribute of the describefeaturetype response with the feature
			// type name and append 'Type' to the end to match the WFS response.
		    if ( $complexTypes->item(0)->getAttribute('name') != $featureType->Name."Type") {
				throw new ImportFeatureTypeLabelsCommand_Exception("Fatal error: WFS DescribeFeatureType returned a wrong feature type name.");			
			}
		
			// get children (elements items only)
			$elements = $complexTypes->item(0)->getElementsByTagName( "element" );
		
			for($i=0; $i < $elements->length; $i++) {
				$name = $elements->item($i)->getAttribute('name');
				$type = $elements->item($i)->getAttribute('type');
			
				$ns_type = explode(":",$type);
			
				$labels = $featureType->Labels(sprintf("\"RemoteColumnName\"='%s'",Convert::raw2sql($name)));

				if ($labels->Count() > 1) {
					throw new ImportFeatureTypeLabelsCommand_Exception("Fatal error: Constraint error - labels for feature types must be unique.");			
				}
				$labelObj = $labels->First();

				if (!$labelObj) {
					$labelObj = new FeatureTypeLabel();
				
					$labelObj->Visible = true;
					$labelObj->Retrieve = true;
					$labelObj->Sort = $i+1;
					// if datatype is a gml object, then hide layer from ui and don't retrieve it
					// from geoserver.
					if ($ns_type[0] == 'gml') {
						$labelObj->Visible = false;
						$labelObj->Retrieve = false;
					}
				}
			
				// update remote information only (remote column name - this will never change) and data type.
				$labelObj->RemoteColumnName = $name;
				$labelObj->DataType         = $type;
			
				// if datatype is a gml object, set geometry flag
				if ($ns_type[0] == 'gml') {
					$labelObj->IsGeometry = true;
				}

				// update local field when it hasn't been populated yet.
				if ($labelObj->Label == '') {			
					$labelObj->Label = $name;
				}
				
				$count++;
				$labelObj->write();
				$featureType->Labels()->add($labelObj);
			}
		}
		return $count;
	}
	
	public function execute() {
		$response = null;
		$parameters = $this->getParameters();
		$featureType = $parameters['FeatureType'];		
		
		$data = array(
			'FeatureType' => $featureType
		);
		
		// get command and execute command
		$cmd = $this->getController()->getCommand('GeoserverWFS_DescribeFeatureType', $data);
		$xml = $cmd->execute();

		$response = $this->createOrUpdateColumns($xml);

		return $response;
	}
}

class ImportFeatureTypeLabelsCommand_Exception extends Exception {}
