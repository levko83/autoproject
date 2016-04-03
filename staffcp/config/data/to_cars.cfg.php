<?php
global $cmsGenerator;
$cmsGenerator['to_cars'] = array(
	'table'         =>  DB_PREFIX.'to_cars',
	'title'         =>  'Запчасти для ТО: Марки',
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'sort'		  	=> 	'input',
		'is_active'		=> 	array('type' => 'checkbox','label' => 'Да/Нет'),
		'content'	  	=> 	'htmlarea',
		'title'		  	=> 	'input',
		'kwords'	  	=> 	'input',
		'descr'			=> 	'input',
		'img'		  	=> 	array(
			'type'	=> 'imageResize',
			'base_dir'		=> 'logos/',
			'images' => array(
				'node'		=> '50x50',
				'small'		=> '100x100',
				'normal'	=> '150x150',
				'big'		=> '200x200',
			),
		),
		
		'seo_text'			=> 	'htmlarea',
		'alias'		  		=> 	array('type'=>'alias','field'=>'name','index'=>'id'),
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		'required' => array('name'=>'text'),
		'fields' => array(
			'id'            =>  'ID',
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
			'title'         =>  'Запчасти для ТО: Марки',
		), 
		'edit'	=> array(
			'fields' => array(
				'Основые данные' => array(
					'name','alias','img','sort','is_active'
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
					'name','alias','img','sort','is_active'
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