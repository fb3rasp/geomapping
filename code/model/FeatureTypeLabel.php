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
class FeatureTypeLabel extends DataObject {
	
	static $db = array(
			'Visible'          => 'Boolean',
			'RemoteColumnName' => 'Varchar(1024)',
			'Label'            => 'Varchar(1024)',
	//		'DataType'         => 'Varchar(50)',
			'IsGeometry'       => 'Boolean',
			'Retrieve'		   => 'Boolean',
			'Sort'			   => 'Int'
	);

	static $has_one = array(
			'FeatureType' => 'FeatureType'
	);

	static $summary_fields = array(
		'Visible',
		'Label',
		'RemoteColumnName',
		'Retrieve',
		'IsGeometry',
		'Sort'
	);	
	
	static $defaults = array(
		'IsGeometry' => 0,
		'Retrieve' => 1,
		'Sort' => 1
	);
}