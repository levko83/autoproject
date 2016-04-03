<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['accounts_history_finances'] = array(
	'table'         =>  DB_PREFIX.'accounts_history',
	'title'         =>  'Поступления оплат/Списания со счета',
	'fields'        =>  array(
		'id'            =>  'index',
		'account_id'    =>  array(
				'type'			=> 'category',
				'cross_name'	=> 'name',
				'cross_index'	=> 'id',
				'cross_table'	=> DB_PREFIX.'accounts',
		),
		'sum'		=>  'input',
		'operation'	=>	array(
			'type' => 'listvalue',
			'values' => array(
				'plus' => 'Поступление',
				'minus' => 'Списание',
			),
		),
		'dt'		=>  array('type' => 'date', 'show_time' => true),
		'comment'	=> 'input',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'account_id'    =>  'Клиент',
			'sum'			=>  'Сумма',
			'operation'		=>	'Операция',
			'dt'			=>  'Дата',
			'comment'		=> 'Комментарий',
		),
		'list' => array(
			'fields' => array('id','account_id','sum','operation','dt','comment'),
			'title'         =>  'Поступления оплат/Списания со счета',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'account_id','sum','operation','dt','comment'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'account_id','sum','operation','dt','comment'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);