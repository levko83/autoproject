<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['testimonials'] = array(
	'table'         =>  DB_PREFIX.'testimonials',
	'title'         =>  'Отзывы и рейтинг',
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'phone'		  	=> 	'input',
		'email'		  	=> 	'input',
		'message'		=> 	'text',
		'dt'			=> 	array('type' => 'date', 'show_time' => true),
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		
		'product_id'	=> 	'input',
		'raiting'	  	=> 	'input',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.main.name'],
			'phone'		  	=> 	'Телефон',
			'email'		  	=> 	'E-mail',
			'message'		=> 	'Сообщение',
			'dt'			=> 	$translates['admin.main.dt'],
			'is_active'		=>  $translates['admin.main.view'],
			
			'product_id'	=> 	'ID товара',
			'raiting'	  	=> 	'Оценка предмета обсуждения',
		),
		'list' => array(
			'fields' => array('id','name','phone','message','is_active','dt','product_id','raiting'),
			'title'         =>  'Отзывы и рейтинг',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','phone','email','message','dt','is_active','product_id','raiting'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','phone','email','message','dt','is_active','product_id','raiting'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);