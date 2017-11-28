<?php
return array(
	//'配置项'=>'配置值'
	'DEFAULT_MODULE'	=> 'Admin',
	'MODULE_ALLOW_LIST'	=> array('Home','Admin','Api','Wap'),
	'APP_SUB_DOMAIN_DEPLOY'   =>    TRUE,
	'APP_SUB_DOMAIN_RULES'    =>    array(
		/*'adm.qoowan.net'  	=> array('/'),*/
		'm.qooce.cn'   => array('Wap/'),
		'wap.qooce.cn'   => array('Wap/'),
		'm.jiancai.com'   => array('Wap/')//test domain
	)
);