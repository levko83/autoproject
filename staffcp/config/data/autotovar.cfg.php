<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['autotovar'] = array(
	'table'         =>  DB_PREFIX.'autotovar',
	'title'         =>  'Автотовар',
	'fields'        =>  array(
		'id'        	=>  'index',
		'name'    		=>  'input',
		'margin'    	=>  'input',
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'id'			=>  'ID',
			'name'    		=>  'Группа',
			'margin'    	=>  'Наценка, %',
		),
		'list' => array(
			'fields' => array('id','name','margin',),
			'title'	 => 'Автотовар',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','margin',
					),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','margin',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);