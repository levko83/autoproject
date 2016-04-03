<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['faq'] = array(
	'table'         =>  DB_PREFIX.'faq',
	'title'         =>  $translates['admin.faq.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'question'		=> 	'input',
		'answer'		=> 	'htmlarea',
		'isset'		  	=> 	array('type'=>'checkbox','label'=>$translates['admin.main.yesno']),
		'sort'		  	=> 	'input',
		'fk'		  	=> 	array(
			'type'	=>	'multiple',
			'foreign'	=> array(
				'table'	=>	DB_PREFIX.'faq_blocks',
				'id'	=>	'id',
				'name'	=>	'name',
				'foreign_id'	=>	'id',
			),
			'link'	=> array(
				'table'	=>	DB_PREFIX.'faq2block',
				'source_id'	=>	'fk_faq',
				'foreign_id'	=>	'fk_block',
			),
		),
		'account_id'    =>  array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'accounts',
			'first' => array(0=>''),
		),
		'name'		=> 	'input',
		'email'		=> 	'input',
		'phone'		=> 	'input',
		'dt' => 'date',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'question'		=> 	$translates['admin.faq.quest'],
			'answer'		=> 	$translates['admin.faq.answer'],
			'isset'		  	=> 	$translates['admin.faq.agree'],
			'sort'		  	=> 	$translates['admin.main.sort'],
			'fk'		  	=> 	$translates['admin.faq.viewparts'],
			'account_id'    =>  'Пользователь',
			'name' => 'Имя',
			'email' => 'E-mail',
			'phone' => 'Телефон',
			'dt' => 'Дата вопроса',
		),
		'list' => array(
			'fields' => array('question','isset','sort','fk','name','email','phone','dt'),
			'title'	 => $translates['admin.faq.name'],
			
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'question','answer','isset','sort','fk','account_id','name','email','phone','dt',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'question','answer','isset','sort','fk','account_id','name','email','phone','dt',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);