<?php
return array(
	//'配置项'=>'配置值'
	'DEFAULT_MODULE'	=> 'Admin',
	'MODULE_ALLOW_LIST'	=> array('Home','Admin','Api'),
	'APP_SUB_DOMAIN_DEPLOY'   =>    TRUE,
	'APP_SUB_DOMAIN_RULES'    =>    array(
		/*'adm.qoowan.net'  	=> array('/'),*/
		'api.qoowan.net'   => array('Api/')
	)
);