<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['margins'] = array(
	'table'         =>  DB_PREFIX.'margins',
	'title'         =>  'Группы наценок',
	'fields'        =>  array(
		'id'        	=>  'index',
		'name'    		=>  'input',
		'extra'    		=>  'input',
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'id'			=>  'ID',
			'name'    		=>  'Название группы',
			'extra'    		=>  'Наценка по умолчанию, %',
		),
		'list' => array(
			'fields' => array('id','name','extra'),
			'title'	 => 'Группы наценок',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','extra',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','extra',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);