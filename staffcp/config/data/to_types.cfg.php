<?php
global $cmsGenerator;
$cmsGenerator['to_types'] = array(
	'table'         =>  DB_PREFIX.'to_types',
	'title'         =>  'Запчасти для ТО: Типы',
	'fields'        =>  array(
		'id'            =>  'index',
		'model_id'	  	=> 	'readonly_to_model',
		'name'		  	=> 	'input',
		'sort'		  	=> 	'input',
		'is_active'		=> 	array('type' => 'checkbox','label' => 'Да/Нет'),
		'content'	  	=> 	'htmlarea',
		'title'		  	=> 	'input',
		'kwords'	  	=> 	'input',
		'descr'			=> 	'input',
		'img'		  	=> 	array(
			'type'=>'file',
			'base_dir'=>'TO/',
		),
		
		'mod'			=> 	'input',
		'engine'		=> 	'input',
		'engine_model'	=> 	'input',
		
		'engine_obj'	=> 	'input',
		'engine_horse'	=> 	'input',
		'type_year'		=> 	'input',
		
		'seo_text'			=> 	'htmlarea',
		
		'tecdoc_url'	=> 	array('type'=>'input','func'=>'tecdoc_url_car'),
		'tecdoc_id'		=> 	array('type'=>'input','func'=>'tecdoc_url_car'),
		'alias'		  		=> 	array('type'=>'alias','field'=>array('name','engine_model','type_year'),'index'=>'id'),
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		'required' => array('name'=>'text'),
		'fields' => array(
			'id'            =>  'ID',
			'model_id'	  	=> 	'Модель',
			'name'		  	=> 	'Название',
			'sort'		  	=> 	'Сортировка',
			'is_active'		=> 	'Выводить',
			'content'	  	=> 	'Описание',
			'title'		  	=> 	'SEO: Заголовок',
			'kwords'	  	=> 	'SEO: Ключевые слова',
			'descr'			=> 	'SEO: Описание',
			'img'		  	=> 	'Изображение',
		
			'mod'			=> 	'Модификация',
			'engine'		=> 	'Тип двиг.',
			'engine_model'	=> 	'Модель двиг.',
			
			'engine_obj'	=> 	'Объем двиг. л',
			'engine_horse'	=> 	'Мощность, л.с.	',
			'type_year'		=> 	'Даты выпуска',
			
			'seo_text'			=> 	'SEO: Описание страницы',
		
			'tecdoc_url'	=> 	'Адрес страницы TECDOC',
			'tecdoc_id'		=> 	'TECDOC ID',
			'alias'		  		=> 	'Алиас',
		),
		'list' => array(
			'fields' => array('id','mod','engine','engine_model','engine_obj','engine_horse','type_year','sort','is_active','tecdoc_id'),
			'title' =>  'Запчасти для ТО: Типы',
		), 
		'edit'	=> array(
			'fields' => array(
				'Основые данные' => array(
					'model_id','name','alias','sort','is_active',
					'mod','engine','engine_model','engine_obj','engine_horse','type_year',
					'tecdoc_id'
				),
				'SEO' => array(
					'title','kwords','descr','seo_text'
				),
			),
			'title'	=> 'Редактирование',
			'submit'=> 'Редактировать',
		),
		'add'	=> array(
			'fields' => array(
				'Основые данные' => array(
					'model_id','name','alias','sort','is_active',
					'mod','engine','engine_model','engine_obj','engine_horse','type_year',
					'tecdoc_id'
				),
				'SEO' => array(
					'title','kwords','descr','seo_text'
				),
			),
			'title'	=> 'Добавление',
			'submit'=> 'Добавить',
		),
	)	
);