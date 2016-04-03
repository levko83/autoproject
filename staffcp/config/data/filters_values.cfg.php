<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['filters_values'] = array(
	'table'         =>  DB_PREFIX.'filters_values',
	'title'         =>  $translates['admin.filtersvalues.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'filter_id'			=> 	array(
			'type'			=> 'category2',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX . 'filters',
			
			'cross_join_index'	=> 'view_id',
			
			'join_name'		=> 'name',
			'join_index'	=> 'id',
			'join_table'	=> DB_PREFIX . 'filters_views',
		),
		'name'		  	=> 	'input',
		'sort'			=> 	'input',
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'filter_id'		=>	$translates['admin.filtersvalues.fname'],
			'name'		  	=> 	$translates['admin.main.value'],
			'sort'		  	=> 	$translates['admin.main.sort'],
			'is_active'		=> 	$translates['admin.main.view'],
		),
		'list' => array(
			'fields' => array('id','filter_id','name','sort','is_active'),
			'title'	 => $translates['admin.filtersvalues.name'],
		),
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'filter_id','name','sort','is_active'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'filter_id','name','sort','is_active'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);