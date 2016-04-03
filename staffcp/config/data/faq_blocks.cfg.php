<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['faq_blocks'] = array(
	'table'         =>  DB_PREFIX.'faq_blocks',
	'title'         =>  $translates['admin.faqblocks.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'name'			=> 	'input',
		'code'			=> 	'code',
		'content'		=> 	'htmlarea',
		'isset'		  	=> 	array('type'=>'checkbox','label'=>$translates['admin.main.yesno']),
		'sort'		  	=> 	'input',
		'title'		  	=> 	'input',
		'kwords'	  	=> 	'input',
		'descr'			=> 	'input',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'			=> 	$translates['admin.main.name'],
			'code'			=> 	$translates['admin.main.alias'],
			'content'		=> 	$translates['admin.main.content'],
			'isset'		  	=> 	$translates['admin.main.view'],
			'sort'		  	=> 	$translates['admin.main.sort'],
			'title'			=>  $translates['admin.main.seo_title'],
			'kwords'		=>  $translates['admin.main.seo_kwords'],
			'descr'			=>  $translates['admin.main.seo_descr'],
		),
		'list' => array(
			'fields' => array('name','code','isset','sort'),
			'title'	 => $translates['admin.faqblocks.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','code','content','isset','sort',
				),
				'SEO' => array(
					'title','kwords','descr',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','code','content','isset','sort',
				),
				'SEO' => array(
					'title','kwords','descr',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);