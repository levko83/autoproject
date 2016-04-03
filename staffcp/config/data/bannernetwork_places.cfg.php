<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['bannernetwork_places'] = array(
	'table'         =>  DB_PREFIX.'bannernetwork__places',
	'title'         =>  $translates['admin.bannerszone.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'width'		  	=> 	'input',
		'height'	  	=> 	'input',
	),
	'generator' => array(
		'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.bannerszone.fname'],
			'width'		  	=> 	$translates['admin.bannerszone.fwidth'],
			'height'	  	=> 	$translates['admin.bannerszone.fheight'],
		),
		'list' => array(
			'fields' => array('id','name','width','height'),
			'title'	 => $translates['admin.bannerszone.name'],			
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','width','height'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.save'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','width','height'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);