<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['filters'] = array(
	'table'         =>  DB_PREFIX.'filters',
	'title'         =>  $translates['admin.filters.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'view_id'			=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX . 'filters_views',
			'first'			=> array(
				'0'	=>	'',
			),
		),
		'name'		  	=> 	'input',
		'sort'			=> 	'input',
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'sortby'		=> array(
			'type' => 'listvalue',
			'values' => array(
				'default' => 'алфавиту',
				'cast' => 'значениям в порядке возрастания',
			),
		),
		'is_product_view' => array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'view_id'		=>	$translates['admin.filters.view'],
			'name'		  	=> 	$translates['admin.main.name'],
			'sort'		  	=> 	$translates['admin.main.sort'],
			'is_active'		=> 	$translates['admin.main.view'],
			'sortby'		=>  'Сортировать значения по',
			'is_product_view' => 'Выводить в описании товара',
		),
		'list' => array(
			'fields' => array('sort','id','view_id','name','is_active','is_product_view'),
			'title'	 => $translates['admin.filters.name'],
		),
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'view_id','name','sort','is_active','is_product_view','sortby',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'view_id','name','sort','is_active','is_product_view','sortby',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);