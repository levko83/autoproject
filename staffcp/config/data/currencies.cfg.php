<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['currencies'] = array(
	'table'         =>  DB_PREFIX.'currencies',
	'title'         =>  'Валюта',
	'fields'        =>  array(
		'id'            =>  'index',
		'currency'		=> 	'input',
		'view'			=> 	'input',
		'rate'			=> 	'input',
		'round'			=> 	'input',
		'code'			=> 	'input',
		'is_default'	=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'selectName'	=> 	'input',
		'is_main_currency'	=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'is_active'	=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'nf'			=> 	'input',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'currency'		=> 	'Валюта',
			'view'			=> 	'Вид отображения',
			'rate'			=> 	'Курс к основной валюте сайта',
			'round'			=> 	'Округление до',
			'code'			=> 	'Код валюты',
			'is_default'	=> 	'Округление по умолчанию (для корзины)',
			'selectName'	=> 	'Название валюты для выбора из списка',
			'is_main_currency'	=> 	'Основная валюта сайта (выбрать только 1)',
			'is_active'	=> 	'Выводить на сайте',
			'nf'		=> 	'Кол-во знаков после запятой',
		),
		'list' => array(
			'fields' => array('id','currency','view','rate','round','code','is_default','selectName','is_main_currency','is_active','nf'),
			'title'	 =>  'Валюта',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'currency','view','rate','round','code','is_default','selectName','is_main_currency','is_active','nf'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'currency','view','rate','round','code','is_default','selectName','is_main_currency','is_active','nf'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);