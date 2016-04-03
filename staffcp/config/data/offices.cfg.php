<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['offices'] = array(
	'table'         =>  DB_PREFIX.'offices',
	'title'         =>  'Представительства (Офисы)',
	'fields'        =>  array(
		'id'        	=>  'index',
		'name'	    	=>  'input',
		'info'	    	=>  array('type'=>'text','style'=>'width:99%;height:75px;'),
		'city_id'			=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'dic_cities',
			'first'	=>	array(
				'0' => '',
			),
		),
		'content'	    	=>  array('type'=>'htmlarea','small'=>true),
		'coords'	    	=>  'googlecoords',
		'contacts'	    	=>  array('type'=>'htmlarea','small'=>true),
		'is_limit_active' =>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'id'			=>  'ID',
			'name'	    	=>  'Название',
			'info'	    	=>  'Описание',
			'city_id'		=> 	'Город',
			'content'	    =>  'Об представительстве',
			'coords'		=>	'Координаты карты Google',
			'contacts'	    =>  'Контакты в шапке',
			'is_limit_active' =>  'Ограничить доступ к поставщикам (включить и выбрать поставщиков к которым доступ открыт)',
		),
		'list' => array(
			'fields' => array('id','name','city_id','coords','is_limit_active',),
			'title'	 => 'Представительства (Офисы)',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','info','city_id','content','coords','contacts','is_limit_active',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','info','city_id','content','coords','contacts','is_limit_active',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);