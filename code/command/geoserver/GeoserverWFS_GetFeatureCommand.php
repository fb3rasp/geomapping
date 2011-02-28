<?php

class GeoserverWFS_GetFeatureCommand extends ControllerCommand {

	/**
	 *
	 * @param Layer $layer
	 * @param String $featureID WFS feature-id string (featuretype.id)
	 *
	 * @return SS_HTTPRequest
	 *
	 * @throws GeoserverWFS_GetFeatureCommand_Exception
	 */
	public function getRequest($layer, $featureID) {
		$geoserver_url = $layer->Storage()->URL_WFS;
		$geoserver_url = str_replace("http://","",$geoserver_url);

		$data = new ArrayData(array(
			'Layer' => $layer,
			'FeatureID' => $featureID
		));
		
		$getvars = array(
			"outputformat" => $layer->OutputFormat
		);

		$body = $data->renderWith('GeoserverWFS_GetFeature');
		$request = new SS_HTTPRequest(
			'POST',
			$geoserver_url,
			$getvars,
			null,
			$body
		);
		$request->addHeader('Content-Type', 'application/xml');
		
		return $request;		
	}
	
	/**
	 *
	 * @param SS_HTTPRequest owsRequest
	 * @param StorageGeoserver storage
	 *
	 * @return json-string $json WFS service response
	 *
	 * @throws GeoserverWFS_GetFeatureCommand_Exception
	 */
	protected function sendRequest($owsRequest, $storage) {
		$url = $owsRequest->getURL();
		
		if($owsRequest->getVars()) {
			 $url .= '?' . http_build_query($owsRequest->getVars());
		}
		
		$ch  = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if($owsRequest->isPost()) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $owsRequest->getBody());
		}

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
		$json  = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if($info['http_code'] == '404'){
			throw new GeoserverWFS_GetFeatureCommand_Exception("Bad URL? couldn't find GeoServer");
		}
		if(empty($json)){
			throw new GeoserverWFS_GetFeatureCommand_Exception("Bad request? the response is empty");
		}		
		return $json;
	}
	
	public function execute() {
		$parameters = $this->getParameters();

		$layer = $parameters['Layer'];
		$featureID = $parameters['featureID'];

		$request = $this->getRequest($layer, $featureID);

		$storage = $layer->Storage();
		$result = $this->sendRequest($request,$storage);

		return $result;
	}
		
}

class GeoserverWFS_GetFeatureCommand_Exception extends Exception {}
