<?php

$GLOBALS['http_lnk'] = "toppkwteile.de";
$GLOBALS['sitename'] = "toppkwteile";

$translates = Register::get('translates');

$GLOBALS['menu'] = array(
	"Пользователи" => array(
		'Список пользователей' => '/staffcp/accounts/',
		'Рассылка email сообщений' => '/staffcp/mailing/',
	),
	"Продукты" => array(
		'Поиск' => '/staffcp/products/',
		'Поставщики' => '/staffcp/importers/',
		'Производители' => '/staffcp/brands/',
		'Категории' => '/staffcp/cat/',
		'Список марок авто' => '/staffcp/manufacturers/',
		'Добавить продукт с TecDoc на сайты' => '#',
	),
	"Заказ товара" => array(
		'Заказы' => '/staffcp/index/crm_list_orders/',
		'Отчеты' => '/staffcp/index/reports/',
	),
	"Финансы" =>  array(
		'Поступления/Списания'	=> '/staffcp/accounts_history_finances/',
		'Финансовые операции клиента'	=> '/staffcp/accounts/finances/',
	),
	"Настройки" => array(
		'Список доставок' 				=> '/staffcp/deliveries/',
		// 'Список скидок' 				=> '/staffcp/discount_programm/',
		'Общие настройки' 				=> '/staffcp/settings/',
		// 'SEO главных разделов' 			=> '/staffcp/seo/',
		'Настройка документов' 			=> '/staffcp/documents_params/',
		'Шаблоны E-mail уведомлений'    => '/staffcp/emails/',
		'Перевод сайта на другой язык'	=> '/staffcp/langs/',
		'Слайдер'	=> '/staffcp/slider/',
		'Список страниц'	=> '/staffcp/page/',
		'Sitemap XML'	=> '/extensions/sitemap.php',
	),
	// "Платежные системы" => array(
		// 'PayPal' => '/staffcp/merchant_paypal/',
	// ), 
	
	
	
);
	$GLOBALS['menu']['Платежные системы']['Описание систем'] = '/staffcp/settings_merchants_list/';
	$GLOBALS['menu']['Платежные системы']['PayPal'] = '/staffcp/merchant_paypal/';
	$GLOBALS['menu']['Платежные системы']['Sofort'] = '/staffcp/merchant_sofort/';
	// $GLOBALS['menu']['Платежные системы']['VISA / MasterCard'] = '#';
	// $GLOBALS['menu']['Платежные системы']['Nachnahme'] = '#';
	$GLOBALS['menu']['Платежные системы']['Rechnung'] = '/staffcp/merchant_rechnung/';
// $GLOBALS['menu']['Платежные системы']['Беларусь']['Хутки грош'] = '/staffcp/merchant_hutkigrosh/';
// $GLOBALS['menu']['Платежные системы']['Беларусь']['iPay'] = '/staffcp/merchant_ipay/';
// $GLOBALS['menu']['Платежные системы']['Беларусь']['WEBPAY'] = '/staffcp/merchant_webpay/';

// $GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['Яндекс.Деньги.Счет'] = '/staffcp/merchant_yandexpc/';
// $GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['Яндекс.Деньги.Карта'] = '/staffcp/merchant_yandexac/';
// $GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['QIWI'] = '/staffcp/merchant_qiwi/';
// $GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['ASSIST'] = '/staffcp/merchant_assist/';
// $GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['ROBOKASSA'] = '/staffcp/merchant_robokassa/';
// $GLOBALS['menu']['Платежные системы']['Россия (Щедрая душа)']['Альфа Банк'] = '/staffcp/merchant_alfa/';

// $GLOBALS['menu']['Платежные системы']['Украина']['Деньги.Online'] = '/staffcp/merchant_dengionline/';
// $GLOBALS['menu']['Платежные системы']['Украина']['MONEXY'] = '/staffcp/merchant_monexy/';
	