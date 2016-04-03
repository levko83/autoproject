<?php

$GLOBALS['http_lnk'] = "toppkwteile.de";
$GLOBALS['sitename'] = "toppkwteile";

$translates = Register::get('translates');

$GLOBALS['menu_manager'] = array(
	$translates['admin.menu.listaccounts'] 				=> '/staffcp/accounts/',
	($translates['admin.menu.listimps'].' (склады)')	=> '/staffcp/importers/',
	$translates['admin.menu.listdetails'] 				=> '/staffcp/details/',
);

$GLOBALS['menu'] = array(
	'Заказы'							 				=> '/staffcp/index/crm_list_orders/',
	'Отчеты'							 				=> '/staffcp/index/reports/',
	($translates['admin.menu.listimps'].' (склады)')	=> '/staffcp/importers/',
	'Валюта' 											=> '/staffcp/currencies/',
	'База кроссов' 										=> '/staffcp/crosses/',
	'Веб-сервисы' 										=> '/staffcp/wbs/',
	$translates['admin.main.priceparsers'] => array(
		'Обработка по файлово' => '/staffcp/harvesterclaas/',
		'Обработка с E-mail почты' => '/staffcp/harvester_email/',
		'Обработка с FTP' => '/staffcp/harvester_ftp/',
		'Обработка по коэфф. (оригинал)' => '/staffcp/db_parsers/originals/',
		'Банк цен (прайсы)' => '/staffcp/details/',
	),
	$translates['admin.menu.accounts'] =>  array(
		$translates['admin.menu.listaccounts']		=> '/staffcp/accounts/',
		'Список автомобилей клиентов' 				=> '/staffcp/accounts_cars/',
		'Группы клиентов, Группы скидок' 			=> '/staffcp/accounts_discountnames/',
		'Собственный веб-сервис' 					=> '/staffcp/webservice/',
		'Прайсы для клиентов' 						=> '/staffcp/pricelists/',
	),
	'Финансы' =>  array(
		'Поступления/Списания'	=> '/staffcp/accounts_history_finances/',
		'Финансовые операции клиента'	=> '/staffcp/accounts/finances/',
	),
	'Дисконтная программа' =>  array(
		'Список скидок' 	=> '/staffcp/discount_programm/',
	),
	'Запросы по VIN' =>  array(
		'Список запросов' 	=> '/staffcp/vin/',
	),
	'Офисы' =>  array(
		'Офисы' => '/staffcp/offices/',
		'Добавить офис' => array('/staffcp/offices/add/','add'),
	),
	$translates['admin.deliveries.name'] =>  array(
		$translates['admin.main.list_deliveries'] 	=> '/staffcp/deliveries/',
		$translates['admin.main.add_deliveries'] 	=> array('/staffcp/deliveries/add/','add'),
	),
	
	$translates['admin.menu.shop'] => array(
		$translates['admin.menu.filterview'] 	=> '/staffcp/filters_views/',
		$translates['admin.menu.filtercharac'] 	=> '/staffcp/filters/',
		$translates['admin.menu.filtervalues'] 	=> '/staffcp/filters_values/',
		$translates['admin.menu.catalog'] 		=> '/staffcp/cat/',
		'Все товары' 							=> '/staffcp/products/',
		'Импорт товаров' 						=> '/staffcp/shopimport/',
		'Яндекс YML' 							=> '/staffcp/yml/',
		'Генератор мета-данных' 				=> '/staffcp/cat_info_generator/',
		'Кеш удаленных цен' 					=> '/staffcp/products_connect_prices/',
	),
	'Отзывы и рейтинг' =>  array(
		'Список отзывов' => '/staffcp/testimonials/',
		'Добавить отзыв' => array('/staffcp/testimonials/add/','add'),
	),
	'Справочник' =>  array(
		$translates['admin.menu.listbrands'] 	=> '/staffcp/brands/',
		$translates['admin.menu.listcars'] 		=> '/staffcp/manufacturers/',
		$translates['admin.main.liststatuses'] 	=> '/staffcp/dic_statuses/',
		$translates['admin.title.listcities'] 	=> '/staffcp/dic_cities/',
	),
);

if (INSTALL_TO){
	$GLOBALS['menu']['Запчасти для ТО']['Запчасти для ТО']='/staffcp/to_cars/';
	$GLOBALS['menu']['Запчасти для ТО']['Генератор мета-данных']='/staffcp/to_info_generator/';
}

if (INSTALL_AVTOTOVAR_API)
	$GLOBALS['menu']['Автотовар (Web-Сервис)']['Автотовар (Web-Сервис)']='/staffcp/autotovar/';

$GLOBALS['menu'] += array(
	$translates['admin.menu.pages'] =>  array(
		$translates['admin.menu.listpages'] => '/staffcp/page/',
		'Галерея - Разделы' => '/staffcp/galleries_parts/',
		'Галерея - Изображения' => '/staffcp/galleries_images/',
	),
	$translates['admin.news.name']=>  array(
		$translates['admin.menu.listnews'] => '/staffcp/news/',
		'Новости автоматически по Rss' => '/staffcp/settings/edit/?id=84',
		'Активировать Rss' => '/staffcp/settings/edit/?id=85',
	),
	'Статьи' =>  array(
		'Список статей' => '/staffcp/articles/',
		'Добавить статью' => array('/staffcp/articles/add/','add'),
	),
	$translates['admin.menu.faq'] =>  array(
		$translates['admin.menu.faqparts'] => '/staffcp/faq_blocks/',
		$translates['admin.menu.faqanswers'] => '/staffcp/faq/',
	),
	$translates['admin.menu.slider'] =>  array(
		$translates['admin.menu.sliderimgs'] => '/staffcp/slider/',
		$translates['admin.menu.addslide'] => array('/staffcp/slider/add/','add'),
	),
);

$GLOBALS['menu']['Платежные системы']['Описание систем'] = '/staffcp/settings_merchants_list/';

$GLOBALS['menu']['Платежные системы']['Беларусь']['Хутки грош'] = '/staffcp/merchant_hutkigrosh/';
$GLOBALS['menu']['Платежные системы']['Беларусь']['iPay'] = '/staffcp/merchant_ipay/';
$GLOBALS['menu']['Платежные системы']['Беларусь']['WEBPAY'] = '/staffcp/merchant_webpay/';

$GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['Яндекс.Деньги.Счет'] = '/staffcp/merchant_yandexpc/';
$GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['Яндекс.Деньги.Карта'] = '/staffcp/merchant_yandexac/';
$GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['QIWI'] = '/staffcp/merchant_qiwi/';
$GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['ASSIST'] = '/staffcp/merchant_assist/';
$GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['ROBOKASSA'] = '/staffcp/merchant_robokassa/';
$GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['Альфа Банк'] = '/staffcp/merchant_alfa/';

$GLOBALS['menu']['Платежные системы']['Украина']['Деньги.Online'] = '/staffcp/merchant_dengionline/';
$GLOBALS['menu']['Платежные системы']['Украина']['MONEXY'] = '/staffcp/merchant_monexy/';


$GLOBALS['menu']['Платежные системы']['PayPal'] = '/staffcp/merchant_paypal/';
$GLOBALS['menu']['Баннерная сеть']['Настройки'] = '/staffcp/bannernetwork/';
$GLOBALS['menu']['Рассылка']['Рассылка email сообщений'] = '/staffcp/mailing/';
$GLOBALS['menu']['Sitemap XML']['Сгенерировать'] = '/extensions/sitemap.php';

$GLOBALS['menu']['Настройки'] = array(
	'Общие настройки' 				=> '/staffcp/settings/',
	'SEO главных разделов' 			=> '/staffcp/seo/',
	'Настройка документов' 			=> '/staffcp/documents_params/',
	'Настройка 1С' 					=> '/staffcp/int1c/',
	'Настроить обратный звонок' 	=> '/staffcp/callme/',
	'Настройка SMS уведомлений'		=> '/staffcp/sms_alert/',
	'Шаблоны SMS уведомлений'		=> '/staffcp/dic_sms/',
	'Шаблоны E-mail уведомлений' => '/staffcp/emails/',
	'Перевод сайта на другой язык'	=> '/staffcp/langs/'
);

