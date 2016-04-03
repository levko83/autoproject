<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['importers'] = array(
	'layout'         =>  'screen',
	'table'         =>  DB_PREFIX.'importers',
	'title'         =>  $translates['admin.imps.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'code'		  	=> 	'input',
		'name'		  	=> 	'input',
		'name_price'  	=> 	'input',
		'discount'	  	=> 	'input',
		'delivery'	  	=> 	'input',
		'email'		  	=> 	'input',
		'color'    		=>  'color',
		'margin_id'		=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'margins',
			'first'			=> array(
				'0'	=>	'',
			),
			'ordered' => 'name',
		),
		'info'		  	=> 	array('type'=>'htmlarea','small'=>true),
		'PARSER_IS_ACTIVE'			=>	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'PARSER_FIELD_BRAND'	  	=> 	'input',
		'PARSER_FIELD_ARTICLE'	  	=> 	'input',
		'PARSER_FIELD_PRICE'	  	=> 	'input',
		'PARSER_FIELD_DESCR'	  	=> 	'input',
		'PARSER_FIELD_BOX'	  		=> 	'input',
		'PARSER_FIELD_DELIVERY'	  	=> 	'input',
		'PARSER_FIELD_CROSS'	  	=> 	'input',
		'PARSER_FIELD_WEIGHT'	  	=> 	'input',
		'PARSER_FIELD_IMG_URL'	  	=> 	'input',
		'PARSER_FIELD_MIN'		  	=> 	'input',
		
		'ONLY_FOR_SHOP'				=>	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'price_date_update'		  	=> 	'date',
		'sort'	  					=> 	'input',
		'currency_id'		=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'currency',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'currencies',
			'first'			=> array(
				'0'	=>	'',
			),
		),
		'only_preorder'		=>	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'country'		  	=> 	'input',
		'disable_unsigned_accounts'			=>	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'imp_offices_params' => 'imp_offices_params',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'code'		  	=> 	$translates['admin.imps.code'],
			'name'		  	=> 	'<b>'.$translates['admin.main.name'].'</b>',
			'name_price'  	=> 	$translates['admin.imps.nameprice'],
			'discount'	  	=> 	'<b>'.$translates['admin.imps.discount'].'</b>',
			'delivery'	  	=> 	$translates['admin.imps.delivery'],
			'email'		  	=> 	$translates['admin.imps.email'],
			'color'    		=>  $translates['admin.main.color.rgb'],
			'margin_id'		=> 	'<b>Группа наценки</b>',
			'info'		  	=> 	'Описание в выдаче артикулов о поставщика под иконкой I',
			
			'PARSER_IS_ACTIVE'			=>	'Использовать настройку по умолчанию',
			'PARSER_FIELD_BRAND'	  	=> 	'Колонка Бренда',
			'PARSER_FIELD_ARTICLE'	  	=> 	'Колонка Артикула (номера)',
			'PARSER_FIELD_PRICE'	  	=> 	'Колонка Цены',
			'PARSER_FIELD_DESCR'	  	=> 	'Колонка Описания (Наименования)',
			'PARSER_FIELD_BOX'	  		=> 	'Колонка Количество',
			'PARSER_FIELD_DELIVERY'	  	=> 	'Колонка О доставке',
			'PARSER_FIELD_CROSS'	  	=> 	'Колонка Кроссы',
			'PARSER_FIELD_WEIGHT'	  	=> 	'Колонка Вес',
			'PARSER_FIELD_IMG_URL'	  	=> 	'Колонка Адрес изображения',
			'PARSER_FIELD_MIN'		  	=> 	'Колонка Кратность',
			
			'ONLY_FOR_SHOP'				=>	'Использовать только для раздела магазин',
			'price_date_update'		  	=> 	'Дата обновления прайслиста (через раздел обработка прайсов)',
			'sort'	  					=> 	'Сортировка (порядок отображения цен)',
			'currency_id' 				=> 'Валюта',
			'only_preorder'				=>	'Продажа под заказ',
			'country'		  			=> 	'Страна происхождения',
			'disable_unsigned_accounts'	=>	'Не показывать для НЕзарегистрированных пользователей',
			'imp_offices_params' => 'Сроки поставки для этого поставщика в офисы',
		),
		'list' => array(
			'fields' => array('id','code','name','currency_id','name_price','delivery','discount','margin_id','color','price_date_update','sort','only_preorder','disable_unsigned_accounts'),
			'title'         =>  $translates['admin.imps.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'code','name','name_price','currency_id','delivery','email','discount','margin_id','color','ONLY_FOR_SHOP','info','price_date_update','sort','only_preorder','country','disable_unsigned_accounts',
				),
				'Обработка прайса (Настройка колонок)' => array(
					'PARSER_IS_ACTIVE',
					'PARSER_FIELD_BRAND',
					'PARSER_FIELD_ARTICLE',
					'PARSER_FIELD_PRICE',
					'PARSER_FIELD_DESCR',
					'PARSER_FIELD_BOX',
					'PARSER_FIELD_DELIVERY',
					'PARSER_FIELD_WEIGHT',
					'PARSER_FIELD_IMG_URL',
					'PARSER_FIELD_MIN'
				),
				'Параметры для офиса' => array(
					'imp_offices_params'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'code','name','name_price','currency_id','delivery','email','discount','margin_id','color','ONLY_FOR_SHOP','info','price_date_update','sort','only_preorder','country','disable_unsigned_accounts',
				),
				'Обработка прайса (Настройка колонок)' => array(
					'PARSER_IS_ACTIVE',
					'PARSER_FIELD_BRAND',
					'PARSER_FIELD_ARTICLE',
					'PARSER_FIELD_PRICE',
					'PARSER_FIELD_DESCR',
					'PARSER_FIELD_BOX',
					'PARSER_FIELD_DELIVERY',
					'PARSER_FIELD_WEIGHT',
					'PARSER_FIELD_IMG_URL',
					'PARSER_FIELD_MIN'
				),
				'Параметры для офиса' => array(
					'imp_offices_params'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);