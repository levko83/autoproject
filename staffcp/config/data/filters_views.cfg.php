<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['filters_views'] = array(
	'table'         =>  DB_PREFIX.'filters_views',
	'title'         =>  $translates['admin.filtersviews.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.main.name'],
		),
		'list' => array(
			'fields' => array('id','name',),
			'title'	 => $translates['admin.filtersviews.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);