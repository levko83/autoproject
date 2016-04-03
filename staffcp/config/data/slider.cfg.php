<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['slider'] = array(
	'table'         =>  DB_PREFIX.'slider',
	'title'         =>  $translates['admin.slider.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'name'          =>  'input',
		'img'		  	=> 	array(
			/*
			'type'	=> 'imageCrop',
			'base_dir'		=> 'load/',
			'images' => array(
				'normal'			=> '120x80',
			),
			*/
			'type'	=> 'file',
			'base_dir'		=> 'slider/',
		),
		'url'		  	=> 	'input',
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'sort'		  	=> 	'input',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'          =>  'Название',
			'img'		  	=> 	$translates['admin.slider.info'],
			'url'		  	=> 	$translates['admin.slider.url'],
			'is_active'		=> 	$translates['admin.main.view'],
			'sort'			=> 	$translates['admin.main.sort'],
		),
		'list' => array(
			'fields' => array('id','name','img','url','is_active','sort'),
			'title'         =>  $translates['admin.slider.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','img','url','is_active','sort',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','img','url','is_active','sort',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);