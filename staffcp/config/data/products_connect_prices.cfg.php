<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['products_connect_prices'] = array(
	'table'         =>  DB_PREFIX.'products_connect_prices',
	'title'         =>  'Кеш удаленных цен',
	'fields'        =>  array(
		'id'            =>  'index',
		'product_id'	=> 	'goods',
		'importer_id'	=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'importers',
			'first'	=>	array(
				'0' => '',
			),
		),
		'price'			=> 	'input',
		'quant'			=> 	'input',
		'dt_update'		=> 	array('type'=>'date','show_time'=>true),
		'result_price'	=> 	'input',
	),
	'generator' => array(
		'fields' => array(
			'id'            =>  'ID',
			'product_id'	=> 	'Продукт',
			'importer_id'	=> 	'Поставщик',
			'price'			=> 	'Цена',
			'quant'			=> 	'Количество',
			'dt_update'		=> 	'Дата обновления',
			'result_price'	=> 	'Конечная цена для сортировки',
		),
		'list' => array(
			'fields' => array('id','product_id','importer_id','price','quant','dt_update','result_price'),
			'title'         =>  'Кеш удаленных цен',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'product_id','importer_id','price','quant','dt_update','result_price'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'product_id','importer_id','price','quant','dt_update','result_price'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);