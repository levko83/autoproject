<?php

require_once('../core/classes/application.class.php');
function __adminAutoload($className) {
	Application::loadClass($className);
}
spl_autoload_register('__adminAutoload');

define("setFolderTemplate",false);
$application = new Application();

Application::setRootDir('..');
Application::setApplicationDir('staffcp');
Application::setHttpRoot('/staffcp/');
Application::setDefaultDirs();

Application::addClassDir(Application::getApplicationDir().'/library/classes/types', 'Type', 'type');
Application::addConfigDir(Application::getApplicationDir().'/../application/config');

Application::loadConfig('db');
Application::loadConfig('inc');

/* init project modules */
Register::add('db',new Db());
Register::add('utils',new Utils());
Register::add('translates',Langs::get(1,LANG));

Application::loadConfig('imageservice');
Application::loadConfig('map');
Application::loadConfig('menu');
Application::loadConfig('site');

/* default */
Application::loadConfig('data/user');
Application::loadConfig('data/seo');
Application::loadConfig('data/settings');
Application::loadConfig('data/emails');
Application::loadConfig('data/page');
// Application::loadConfig('data/bannernetwork');
// Application::loadConfig('data/bannernetwork_places');
Application::loadConfig('data/mailing');
Application::loadConfig('data/mailing_emails');

/* load */
Application::loadConfig('data/dic_cities');
Application::loadConfig('data/dic_statuses');
Application::loadConfig('data/dic_sms');

Application::loadConfig('data/cat');
Application::loadConfig('data/products');
Application::loadConfig('data/news');
Application::loadConfig('data/articles');
Application::loadConfig('data/slider');
Application::loadConfig('data/faq');
Application::loadConfig('data/faq_blocks');
Application::loadConfig('data/langs');
Application::loadConfig('data/accounts');
Application::loadConfig('data/accounts_cars');
Application::loadConfig('data/filters_views');
Application::loadConfig('data/filters');
Application::loadConfig('data/filters_values');
Application::loadConfig('data/deliveries');
Application::loadConfig('data/crosses');
Application::loadConfig('data/harvesterclaas');
Application::loadConfig('data/db_parsers');
Application::loadConfig('data/offices');
Application::loadConfig('data/brands_self');
Application::loadConfig('data/autotovar');
Application::loadConfig('data/brands');
Application::loadConfig('data/manufacturers');
Application::loadConfig('data/details');
Application::loadConfig('data/importers');
Application::loadConfig('data/margins');
Application::loadConfig('data/to_cars');
Application::loadConfig('data/to_models');
Application::loadConfig('data/to_types');
Application::loadConfig('data/to');
Application::loadConfig('data/callme');
Application::loadConfig('data/wbs');
Application::loadConfig('data/wbs_correct');
Application::loadConfig('data/settings_merchants_list');
Application::loadConfig('data/testimonials');
Application::loadConfig('data/vin');
Application::loadConfig('data/accounts_discountnames');
Application::loadConfig('data/currencies');
Application::loadConfig('data/pricelists');
Application::loadConfig('data/documents_params');
Application::loadConfig('data/logs');
Application::loadConfig('data/discount_programm');
Application::loadConfig('data/galleries_parts');
Application::loadConfig('data/galleries_images');
Application::loadConfig('data/products_connect_prices');
Application::loadConfig('data/accounts_history_finances');
Application::loadConfig('data/harverster_email__params');
Application::loadConfig('data/harverster_ftp__params');

?>