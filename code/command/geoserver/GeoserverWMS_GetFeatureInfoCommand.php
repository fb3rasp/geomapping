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
class GeoserverWMS_GetFeatureInfoCommand extends ControllerCommand {

	static protected $getfeatureinfo_tolerance = 15;
	
	static protected $max_feature_count = 1000;
	
	/**
	 * Generates WMS request.
	 *
	 * @param Array $params (Caution: Unescaped client data)
	 * @return string url and encoded parameter string.
	 */
	protected function getWmsGetFeatureInfoRequest($url, $params) {
		// The controller acts as a proxy for a powerful webservice in a narrow use case, avoid misuse
		// by limiting parameters. We don't need to do complete input validation as this is assumed
		// to be the case in geoserver.
		$requiredParams = array('BBOX', "WIDTH", "HEIGHT", "SRS", "X", "Y", 'LAYERS');
		foreach($requiredParams as $requiredParam) {
			if(!isset($params[$requiredParam])) {
				throw new OLFeature_Controller_Exception('Missing parameter: ' . $requiredParam);
			}
		}
		
		// Filter to allowed parameters
		$allowedParams = $requiredParams;
		$params = ArrayLib::filter_keys($params, $allowedParams);
	
		$params['info_format'] = 'text/plain';
		$params['REQUEST'] = 'GetFeatureInfo';
		$params['SERVICE'] = 'WMS';
		$params['VERSION'] = '1.1.0';
		$params['QUERY_LAYERS'] = $params['LAYERS'];
		
		$params['Buffer'] = self::$getfeatureinfo_tolerance;
		$params['FEATURE_COUNT'] = self::$max_feature_count;

		$url = str_replace('gwc/service/','',$url);
		$request = new SS_HTTPRequest(
			'GET',
			$url,
			$params
		);
		$request->addHeader('Content-Type', 'text/plain');
		return $request;
	}
	
	
	/**
	 * Transforms an SS_HTTPRequest object into a curl request and executes it.
	 * Adds geoserver authentication data if any is set.
	 * 
	 * @param SS_HTTPRequest $owsRequest
	 * @return String|Boolean Returns FALSE when request wasnt successful
	 */
	protected function executeOwsRequest($owsRequest) {
		$url = $owsRequest->getURL();
		if($owsRequest->getVars()) $url .= '?' . http_build_query($owsRequest->getVars());

		$ch  = curl_init($url);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 seconds should be enough for any geoserver request

		if($owsRequest->isPost()) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $owsRequest->getBody());
		}
//		if(self::$geoserver_user && self::$geoserver_password) {
//			curl_setopt($ch, CURLOPT_USERPWD, self::$geoserver_user . ':' . self::$geoserver_password);
//		}
		$headers = $owsRequest->getHeaders();
		if($headers) {
			$curlHeaders = array();
			foreach($headers as $header => $value) {
				$curlHeaders[] = "$header: $value";			
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders); 
		}
		$response = curl_exec($ch);
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if($statusCode != 200) {
			throw new OLFeature_Controller_Exception("Bad response in executeOwsRequest (code: $statusCode): " . $response );
		}
			
		return $response;
	}
	
	public function execute() {
		$parameters = $this->getParameters();
		
		$params = $parameters['HTTP_parameters'];
		$url = $parameters['URL'];
		
		$ogc_request = $this->getWmsGetFeatureInfoRequest($url, $params);
		$response = $this->executeOwsRequest($ogc_request);
		
		return $response;
	}
	
}