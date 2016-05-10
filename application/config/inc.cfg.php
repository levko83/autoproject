<?php

define("VERSION","2.0.55");
define('SITE_NAME','AUTORESURS');
define('ADMIN_EMAIL','info@autoresurs.de');
define('CONTACT_EMAIL','info@autoresurs.de');
define('OUTPUT_LANGUAGE','de');
define('USE_SET_NAMES',1);
define('LANG','de');
define('debug_MYSQL',0);
define('INSTALL_BODY_MODULE','1,2,3');
define('IS_MOBILE_VIEW',0);
define('MULTILANGS',1); 
define('HTTP_ROOT',((isset($_SERVER['HTTPS']) && isset($_SERVER['HTTPS']) == 'on')?('https://'.$_SERVER['HTTP_HOST']):('http://'.$_SERVER['HTTP_HOST'])));	
$LANGUAGES = array('en'=>array('ID'=>4,'TEX_TEXT'=>'English'),'de'=>array('ID'=>1,'TEX_TEXT'=>'German'),'fr'=>array('ID'=>6,'TEX_TEXT'=>'French'),'it'=>array('ID'=>7,'TEX_TEXT'=>'Italian'),'ru'=>array('ID'=>16,'TEX_TEXT'=>'Russian'));
Register::add('langs', $LANGUAGES);

