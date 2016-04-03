<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['galleries_images'] = array(
	'table'         =>  DB_PREFIX.'galleries_images',
	'title'         =>  'Галерея - Изображения',
	'fields'        =>  array(
		'id'            =>  'index',
		'fk_gallery' 		=> array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'ordered'		=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'galleries_parts',
			'first' => array(
				0 => '',
			)
		),
		'name'		  	=> 	'input',
		'img'		  	=> 	array(
			'type'	=> 'imageResizeWatermark',
			'base_dir'		=> 'gallery/',
			'images' => array(
				'normal'	=>	'130x100',
			),
		),
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'fk_gallery'	=>	'Раздел галереи',
			'name'		  	=> 	'Название ихображения',
			'img'			=>	'Изображение',
		),
		'list' => array(
			'fields' => array('id','fk_gallery','name','img'),
			'title'	 => 'Галерея - Разделы',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'fk_gallery','name','img'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'fk_gallery','name','img'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);