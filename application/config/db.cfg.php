<?php

$_SERVER['SERVER_NAME'] = 'autoresurs.de';

define("SOAP","http://api.pkwlkwteile.de/server.wsdl");
define('IMGPATH','http://static.pkwlkwteile.de/');

#DB CONFIG ~~~~~~~~~~~~~~~~~~~~~~~~~~~
define('DB_PREFIX','w_');
// define('DB_HOST', 'localhost');
define('DB_HOST', '178.63.86.9');
define('DB_NAME', 'admin_autoresursde');
define('DB_USER', 'admin_autoresurs');
define('DB_PASSWORD', 'nI82GlX4mV');
define('DB_INIT','SET NAMES `utf8`');
#DB END ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
define('PRICE_PATH','extensions/price_cron');

define('NOTICE',0);
define('INSTALL_TO',1);
define('INSTALL_TO_PRICES_VIEW',0);

?>
