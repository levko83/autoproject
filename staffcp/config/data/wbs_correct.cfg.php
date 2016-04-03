<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['wbs_correct'] = array(
	'table'         =>  DB_PREFIX.'wbs_correct_brands',
	'title'         =>  'Корректировка брендов',
	'fields'        =>  array(
		'id'            =>  'index',
		'incorrect'     =>  'input',
		'correct'       =>  'input',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'incorrect'     =>  'Неправильное название',
			'correct'       =>  'Правильное название',
		),
		'list' => array(
			'fields' => array('id','incorrect','correct',),
			'title'         =>  'Корректировка брендов',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'incorrect','correct',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'incorrect','correct',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);