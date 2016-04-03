<?php
global $cmsGenerator;
$cmsGenerator['to'] = array(
	'table'         =>  DB_PREFIX.'to',
	'title'         =>  'Запчасти для ТО: Каталог',
	'fields'        =>  array(
		'id'            =>  'index',
		'type_id'	  	=> 	'readonly_to_type',
		'descr'		  	=> 	'input',
		'box'		  	=> 	'input',
		'comment'	 	=> 	'input',
		'article'	  	=> 	'readonly',
		'search'	 	=> 	'input',
		'brand_id'			=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'BRA_BRAND',
			'cross_index'	=> 'BRA_ID',
			'cross_table'	=> DB_PREFIX.'brands',
			'first'	=>	array(
				'0' => 'нет',
			),
			'ordered' => 'BRA_BRAND'
		),
		'seo_title'		  	=> 	'input',
		'seo_kwords'	  	=> 	'input',
		'seo_descr'			=> 	'input',
		'alias'		  		=> 	array('type'=>'alias','field'=>array('descr','article','id'),'index'=>'id'),
		'seo_text'			=> 	'htmlarea',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		'required' => array('name'=>'text'),
		'fields' => array(
			'id'            =>  'ID',
			'type_id'	  	=> 	'Тип',
			'descr'		  	=> 	'Описание детали',
			'box'		  	=> 	'Кол-во',
			'comment'	 	=> 	'Комментарии',
			'article'	  	=> 	'Цены',
			'search'	 	=> 	'Артикул поиска',
			'brand_id'		=>	'Бренд',
			'seo_title'		  	=> 	'SEO: Заголовок',
			'seo_kwords'	  	=> 	'SEO: Ключевые слова',
			'seo_descr'			=> 	'SEO: Описание',
			'alias'		  		=> 	'Алиас',
			'seo_text'			=> 	'SEO: Описание страницы',
		),
		'list' => array(
			'fields' => array('id','descr','box','comment','article','search','brand_id'),
			'title' =>  'Запчасти для ТО: Каталог',
		), 
		'edit'	=> array(
			'fields' => array(
				'Основые данные' => array(
					'type_id','descr','alias','box','comment','article','search','brand_id'
				),
				'SEO' => array(
					'seo_title','seo_kwords','seo_descr','seo_text'
				),
			),
			'title'	=> 'Редактирование',
			'submit'=> 'Редактировать',
		),
		'add'	=> array(
			'fields' => array(
				'Основые данные' => array(
					'type_id','descr','alias','box','comment','article','search','brand_id'
				),
				'SEO' => array(
					'seo_title','seo_kwords','seo_descr','seo_text'
				),
			),
			'title'	=> 'Добавление',
			'submit'=> 'Добавить',
		),
	)	
);