<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['articles'] = array(
	'table'         =>  DB_PREFIX.'articles',
	'title'         =>  'Статьи',
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'code'			=> 	'code',
		'brief'			=> 	'htmlarea',
		'content'		=> 	'htmlarea',
		'sort'			=> 	'input',
		'title'		  	=> 	'input',
		'kwords'	  	=> 	'input',
		'descr'			=> 	'input',
		'dt'			=> 	'date',
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'img'		  	=> 	array(
			'type'	=> 'imageResize',
			'base_dir'		=> 'load/',
			'images' => array(
				'normal'			=> '120x80',
			),
		),
		'is_index'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.main.name'],
			'code'		  	=> 	$translates['admin.main.alias'],
			'brief'			=> 	$translates['admin.main.brief'],
			'content'		=> 	$translates['admin.main.content'],
			'sort'		  	=> 	$translates['admin.main.sort'],
			'title'			=>  $translates['admin.main.seo_title'],
			'kwords'		=>  $translates['admin.main.seo_kwords'],
			'descr'			=>  $translates['admin.main.seo_descr'],
			'dt'			=> 	$translates['admin.main.dt'],
			'is_active'		=>  $translates['admin.main.view'],
			'img'			=>  $translates['admin.banners.img'],
			'is_index'		=>  'Выводить на главную страницу',
		),
		'list' => array(
			'fields' => array('id','name','is_active','dt','is_index'),
			'title'         =>  'Статьи',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','code','brief','content','dt','is_active','is_index','img'
				),
				'SEO' => array(
					'title','kwords','descr',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','code','brief','content','dt','is_active','is_index','img'
				),
				'SEO' => array(
					'title','kwords','descr',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);