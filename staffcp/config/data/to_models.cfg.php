<?php
global $cmsGenerator;
$cmsGenerator['to_models'] = array(
	'table'         =>  DB_PREFIX.'to_models',
	'title'         =>  'Запчасти для ТО: Модели',
	'fields'        =>  array(
		'id'            =>  'index',
		'car_id'	  	=> 	'readonly_to_car',
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
		
		'seo_text'			=> 	'htmlarea',
		'alias'		  		=> 	array('type'=>'alias','field'=>'name','index'=>'id'),
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		'required' => array('name'=>'text'),
		'fields' => array(
			'id'            =>  'ID',
			'car_id'	  	=> 	'Марка',
			'name'		  	=> 	'Название',
			'sort'		  	=> 	'Сортировка',
			'is_active'		=> 	'Выводить',
			'content'	  	=> 	'Описание',
			'title'		  	=> 	'SEO: Заголовок',
			'kwords'	  	=> 	'SEO: Ключевые слова',
			'descr'			=> 	'SEO: Описание',
			'img'		  	=> 	'Изображение',
			
			'seo_text'			=> 	'SEO: Описание страницы',
			'alias'		  		=> 	'Алиас',
		),
		'list' => array(
			'fields' => array('id','name','sort','is_active'),
			'title'         =>  'Запчасти для ТО: Модели',
		), 
		'edit'	=> array(
			'fields' => array(
				'Основые данные' => array(
					'car_id','name','alias','content','img','sort','is_active'
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
					'car_id','name','alias','content','img','sort','is_active'
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