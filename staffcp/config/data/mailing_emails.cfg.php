<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['mailing_emails'] = array(
	'table'         =>  DB_PREFIX.'mailing__emails',
	'title'         =>  $translates['admin.mailingemail.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'email'		  	=> 	'input',
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'dt'		  	=> 	array('type'=>'date','show_time'=>true),
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.main.name'],
			'email'			=> 	$translates['admin.email.name'],
			'is_active'		=> 	$translates['admin.banners.factive'],
			'dt'			=> 	$translates['admin.mailingemail.dt'],
		),
		'list' => array(
			'fields' => array('id','name','email','is_active','dt'),
			'title'	 => $translates['admin.mailingemail.name'],			
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','email','is_active','dt'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','email','is_active','dt'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);