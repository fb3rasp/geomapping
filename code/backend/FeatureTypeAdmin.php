<?php
/**
 * @package mapping
 * @subpackage backend
 */

/**
 * Feature Type - Model-Admin class.
 *
 * @package mapping
 * @subpackage backend
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class FeatureTypeAdmin extends ModelAdmin {

	static $menu_title = "Feature Types";
	
	static $url_segment = "featuretypes";

	static $managed_models = array(
		"FeatureType",
	);
	
	static $record_controller_class = "FeatureTypeAdmin_RecordController";

	static $allowed_actions = array(
	);
	
	/**
	 * Initialize the model admin interface. Sets up embedded jquery libraries and requisite plugins.
	 * 
	 * @todo remove reliance on urlParams
	 */
	public function init() {
		parent::init();
		$presenter = singleton(MapControllerExtension::get_map_presenter_class());	
		Requirements::javascript($presenter->getModulePath().'/javascript/backend/FeatureType.js');
	}	
}

/**
 * Feature Type - Record Controller class.
 *
 * @package mapping
 * @subpackage backend
 * @author Rainer Spittel (rainer at silverstripe dot com)
 */
class FeatureTypeAdmin_RecordController extends ModelAdmin_RecordController {

	static $allowed_actions = array('edit', 'view', 'EditForm', 'ViewForm', 'doimportlabels');
	
	protected $import_message = '';

	/**
	 * Edit action - shows a form for editing this record
	 */
	function doImportLabels($data, $form, $request) {

		$featureType = $this->currentRecord;
		$featureTypeID =  $featureType->ID;

		$data = array(
			'FeatureType' => $featureType
		);
		
		// get command and execute command
		try {
			$cmd = $this->getCommand('ImportFeatureTypeLabels', $data);
			$result = $cmd->execute();

			$message = sprintf("Feature type structure '%s' has been imported sucessfully.",$featureType);
			$form->sessionMessage( $message, 'good');
		} 
		catch(Exception $e) {
			$message = sprintf("FeatureType import failed. Please try again. <br/>Error Message: '%s'", $e->getMessage());
			$form->sessionMessage( $message, 'bad');
		}
		
		// Behaviour switched on ajax.
		if(Director::is_ajax()) {
			return $this->edit($request);
		} else {
			Director::redirectBack();
		}
	}
}
