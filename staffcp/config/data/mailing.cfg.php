<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['mailing'] = array(
	'table'         =>  DB_PREFIX.'mailing',
	'title'         =>  $translates['admin.mailing.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'content'		=> 	'htmlarea',
		'file'		  	=> 	array(
			'type'	=>	'file',
			'base_dir'	=>	'mailing/'
		),
		'dt'		  	=> 	array('type'=>'date','show_time'=>true),
		'dt_sended'	  	=> 	'hidden',
		'sended'	  	=> 	'hidden',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.main.name'],
			'content'		=> 	$translates['admin.main.content'],
			'file'		  	=> 	$translates['admin.main.file'],
			'dt'		  	=> 	$translates['admin.mailing.dtcreate'],
			'dt_sended'	  	=> 	$translates['admin.mailing.datesending'],
			'sended'	  	=> 	$translates['admin.mailing.sentcount'],
		),
		'list' => array(
			'fields' => array('id','name','dt','dt_sended','sended'),
			'title'	 => $translates['admin.mailing.name'],			
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','content','file','dt','dt_sended','sended'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','content','file','dt','dt_sended','sended'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);