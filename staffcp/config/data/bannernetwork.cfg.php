<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['bannernetwork'] = array(
	'table'         =>  DB_PREFIX.'bannernetwork',
	'title'         =>  $translates['admin.banners.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'comment'		=> 	array('type'=>'text','style'=>'height:100px;width:100%;'),
		'file'		  	=> 	array(
			'type'	=>	'file',
			'base_dir'	=>	'banners/'
		),
		'file_type'	  	=> 	array(
			'type'	=>	'listvalue',
			'values'	=>	array(
				'img'	=>	$translates['admin.banners.img'],
				'swf'	=>	$translates['admin.banners.flash'],
				'txt'	=>	$translates['admin.banners.text'],
			)
		),
		'url'		  	=> 	'input',
		'view_count'  	=> 	'input',
		'view_count_minus'  	=> 	'input',
		'view_from'	  	=> 	array('type'=>'date','show_time'=>true),
		'view_to'	  	=> 	array('type'=>'date','show_time'=>true),
		'type_view'	  	=> 	array(
			'type'	=>	'listvalue',
			'values'	=>	array(
				'1'	=>	$translates['admin.banners.byviews'],
				'2'	=>	$translates['admin.banners.bydate'],
				'3'	=>	$translates['admin.banners.nolimit'],
			)
		),
		'zone'		  	=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'bannernetwork__places',
		),
		'dt'		  	=> 	array('type'=>'date','show_time'=>true),
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'view_by_url'	=>	'input',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.banners.fname'],
			'comment'		=> 	$translates['admin.banners.fcomment'],
			'file'		  	=> 	$translates['admin.banners.ffile'],
			'file_type'	  	=> 	$translates['admin.banners.ftype'],
			'url'		  	=> 	$translates['admin.banners.faddress'].' перехода',
			'view_count'  	=> 	$translates['admin.banners.fcountviews'],
			'view_count_minus'  	=> 	$translates['admin.banners.fleave'],
			'view_from'	  	=> 	$translates['admin.banners.ffrom'],
			'view_to'	  	=> 	$translates['admin.banners.fbefore'],
			'type_view'	  	=> 	$translates['admin.banners.ftypeview'],
			'zone'		  	=> 	$translates['admin.banners.fplace'],
			'dt'		  	=> 	$translates['admin.banners.fdatecreate'],
			'is_active'		=> 	$translates['admin.banners.factive'],
			'view_by_url'	=>	'<b>Выводить на странице (Адрес страницы)</b>',
		),
		'list' => array(
			'fields' => array('id','name','comment','url','type_view','zone','dt','is_active'),
			'title'	 => $translates['admin.banners.name'],		
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','comment','file','file_type','url','type_view','zone','view_by_url','dt','is_active'
				),
				$translates['admin.banners.viewbyviews'] => array(
					'view_count','view_count_minus'
				),
				$translates['admin.banners.viewbyperiod'] => array(
					'view_from','view_to',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','comment','file','file_type','url','type_view','zone','view_by_url','dt','is_active'
				),
				$translates['admin.banners.viewbyviews'] => array(
					'view_count','view_count_minus'
				),
				$translates['admin.banners.viewbyperiod'] => array(
					'view_from','view_to',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);