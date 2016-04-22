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

// $LANGUAGES = array('ru'=>array('ID'=>16,'TEX_TEXT'=>'Русский'),'en'=>array('ID'=>4,'TEX_TEXT'=>'English'),'de'=>array('ID'=>1,'TEX_TEXT'=>'German'),'fr'=>array('ID'=>6,'TEX_TEXT'=>'French'),'it'=>array('ID'=>7,'TEX_TEXT'=>'Italian'),'gr'=>array('ID'=>20,'TEX_TEXT'=>'Greek'),'no'=>array('ID'=>12,'TEX_TEXT'=>'Norwegian'),'da'=>array('ID'=>10,'TEX_TEXT'=>'Danish'),'es'=>array('ID'=>8,'TEX_TEXT'=>'Spanish'));



// $LANGUAGES = array('en'=>array('ID'=>4,'TEX_TEXT'=>'English'),'de'=>array('ID'=>1,'TEX_TEXT'=>'German'),'fr'=>array('ID'=>6,'TEX_TEXT'=>'French'),'it'=>array('ID'=>7,'TEX_TEXT'=>'Italian'),'gr'=>array('ID'=>20,'TEX_TEXT'=>'Greek'),'no'=>array('ID'=>12,'TEX_TEXT'=>'Norwegian'),'da'=>array('ID'=>10,'TEX_TEXT'=>'Danish'),'es'=>array('ID'=>8,'TEX_TEXT'=>'Spanish'));
$LANGUAGES = array('en'=>array('ID'=>4,'TEX_TEXT'=>'English'),'de'=>array('ID'=>1,'TEX_TEXT'=>'German'),'fr'=>array('ID'=>6,'TEX_TEXT'=>'French'),'it'=>array('ID'=>7,'TEX_TEXT'=>'Italian'),'ru'=>array('ID'=>16,'TEX_TEXT'=>'Russian'));
// $LANGUAGES = array('de'=>array('ID'=>1,'TEX_TEXT'=>'German'));
Register::add('langs', $LANGUAGES);

