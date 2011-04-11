<?php

Director::addRules(100, array(
	'Feature' => 'Feature_Controller',
	'Proxy' => 'Proxy_Controller'
));

Proxy_Controller::set_allowed_host(array(
	'localhost:8080'
));
