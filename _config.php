<?php

Director::addRules(100, array(
	'Feature' => 'Feature_Controller',
	'Proxy' => 'Proxy_Controller'
));

Proxy_Controller::set_allowed_host(array(
	'localhost:8080','localhost'
));

DataObject::add_extension('MapPage', 'MapPageDecorator');
Object::add_extension('MapPage_Controller', 'MapControllerExtension');


$file_extensions = File::$allowed_extensions;
$file_extensions[] = 'kml';
$file_extensions[] = 'kmz';
File::$allowed_extensions = $file_extensions;

/** 
 * Add to .htaccess file in assets following lines:

<FilesMatch "\.(kmz|kml)$">
	Allow from all
</FilesMatch>

 *
 */