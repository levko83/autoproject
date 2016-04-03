<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['brands_self'] = array(
	'table'         =>  DB_PREFIX.'brands',
	'title'         =>  'Обучение брендов',
	'fields'        =>  array(
		'id'        	=>  'index',
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'id'			=>  'ID',
		),
		'list' => array(
			'fields' => array('id'),
			'title'	 => 'Обучение брендов',
		), 
		'edit'	=> array(
			'fields' => array(
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);