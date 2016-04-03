<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['cat'] = array(
	'table'         =>  DB_PREFIX.'cat',
	'title'         =>  $translates['admin.cat.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'sort'		  	=> 	'input',
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'parent'		=> 	'readonly2',
		'content'	  	=> 	'htmlarea',
		'title'		  	=> 	'input',
		'kwords'	  	=> 	'input',
		'descr'			=> 	'input',
		'img'		  	=> 	array(
			'type'	=> 'imageResize',
			'base_dir'		=> 'products/',
			'images' => array(
				'normal'	=>	'50x50',
			),
		),
		'filter_id'			=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX . 'filters_views',
			'first'			=> array(
				'0'	=>	'',
			),
		),
		'htmlcode'	  	=> 	'text',
		'url'			=> 	'input',
		'home_catname'	=> 	'input',
		'home_view'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'background_image' => array(
			'type' => 'file', 'base_dir' => 'settings/',
		),
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		'required' => array('name'=>'text'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.cat.fname'],
			'sort'			=>  $translates['admin.cat.sort'],
			'is_active'		=> 	$translates['admin.cat.viewsite'],
			'parent'		=> 	$translates['admin.cat.parent'],
			'content'	  	=> 	$translates['admin.cat.content'],
			'title'			=>  $translates['admin.main.seo_title'],
			'kwords'		=>  $translates['admin.main.seo_kwords'],
			'descr'			=>  $translates['admin.main.seo_descr'],
			'img'			=>	$translates['admin.cat.icon'],
			'filter_id'		=>	$translates['admin.cat.filter'],
			'htmlcode'	  	=> 	'HTML код для плагинов',
			'url'			=> 	'Адрес перехода (url), для принудительного перехода',
			'home_catname'	=> 	'Название для поиск на главной (там где поиск по артикулу)',
			'home_view'		=> 	'Выводить в блоке поиска по артикулу',
			'background_image' => 'Изображение заднего фона для этой категории',
		),
		'list' => array(
			'fields' => array('sort','id','name','is_active','filter_id'),
			'title'         =>  $translates['admin.cat.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','sort','is_active','parent','img','url','filter_id','content','htmlcode','background_image'
				),
				'SEO' => array(
					'title','kwords','descr',
				),
				'Блок поиска' => array(
					'home_catname','home_view',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','sort','is_active','parent','img','url','filter_id','content','htmlcode','background_image'
				),
				'SEO' => array(
					'title','kwords','descr',
				),
				'Блок поиска' => array(
					'home_catname','home_view',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);