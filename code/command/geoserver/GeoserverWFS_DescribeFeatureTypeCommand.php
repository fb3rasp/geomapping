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
class GeoserverWFS_DescribeFeatureTypeCommand extends ControllerCommand {

	/**
	 * Create a WFS-DescribeFeatureType request.
	 *
	 * @param string $featureType Name of the feature-type (incl. namespace, i.e. tiger:poi).
	 * @return SS_HTTPRequest
	 */ 
	protected function getWfsDescribeFeatureTypeRequest($featureType) {
		// get geoserver wfs server OGC API
		
		if($featureType == null){
			throw new GeoserverWFS_DescribeFeatureTypeCommand_Exception("Internal error: Undefined feature type.");
		} else 
		if($featureType->Layer() == null){
			throw new GeoserverWFS_DescribeFeatureTypeCommand_Exception("Feature Type is not assigned to a layer. Please check the feature type configuration.");
		} else 
		if($featureType->Layer()->Storage() == null){
			throw new GeoserverWFS_DescribeFeatureTypeCommand_Exception("Feature Type is not assigned to a storage. Please check the layer configuration.");
		}  

		$geoserver_url = $featureType->Layer()->Storage()->URL;
		$geoserver_url = str_replace("http://","",$geoserver_url);
		$geoserver_url = str_replace("/wms","/wfs",$geoserver_url);
		$geoserver_url = str_replace("/service/","/",$geoserver_url);
		$geoserver_url = str_replace("/gwc/","/",$geoserver_url);
		$geoserver_url = str_replace("/geowebcache/","/",$geoserver_url);

		if($geoserver_url == ''){
			throw new GeoserverWFS_DescribeFeatureTypeCommand_Exception("Undefined GeoServer WFS URL: please check the Geoserver-Storage configuration.");
		}

		$data = new ArrayData(array(
			'FeatureType' => $featureType
		));

		$body = $data->renderWith('GeoserverWFS_DescribeFeatureType');
		
		$request = new SS_HTTPRequest(
			'POST',
			$geoserver_url,
			null,
			null,
			$body
		);
		$request->addHeader('Content-Type', 'application/xml');
		return $request;
	}
	
	/**
	 * Create a WFS-describe feature type request and sends it off to the 
	 * GeoServer instance. Thismethod returns the xml strucutre, which is 
	 * returned by the GeoServer WFS API.
	 *
	 * @param FeatureType $featureType FeatureType Object
	 *
	 * @return string $xml XML-string (WFS service response)
	 *
	 * @throws GeoserverWFS_DescribeFeatureTypeCommand_Exception
	 */
	protected function describeFeatureType($featureType) {
		//
		// get WFS-DescribeFeatureType request
		$owsRequest = $this->getWfsDescribeFeatureTypeRequest($featureType);
	
		//
		// initiate CURL request
		$url = $owsRequest->getURL();
		
		if($owsRequest->getVars()) $url .= '?' . http_build_query($owsRequest->getVars());
		
		$ch  = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if($owsRequest->isPost()) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $owsRequest->getBody());
		}

		$storage = $featureType->Layer()->Storage();
		if($storage->Username && $storage->Password) {
			curl_setopt($ch, CURLOPT_USERPWD, $storage->Username . ':' . $storage->Password);
		}
		$headers = $owsRequest->getHeaders();
		if($headers) {
			$curlHeaders = array();
			foreach($headers as $header => $value) {
				$curlHeaders[] = "$header: $value";			
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders); 
		}
		$xml  = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		if($info['http_code'] == '404'){
			throw new GeoserverWFS_DescribeFeatureTypeCommand_Exception("Bad URL? couldn't find GeoServer");
		}
		if(empty($xml)){
			throw new GeoserverWFS_DescribeFeatureTypeCommand_Exception("Bad request? the response is empty");
		}		
		return $xml;
	}

	public function execute() {
		
		$parameters = $this->getParameters();
		$featureType = $parameters['FeatureType'];		
		
		$response = $this->describeFeatureType($featureType);
		return $response;
	}	
}

class GeoserverWFS_DescribeFeatureTypeCommand_Exception extends Exception {}