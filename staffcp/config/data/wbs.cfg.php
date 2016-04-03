<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['wbs'] = array(
	'table'         =>  DB_PREFIX.'wbs',
	'title'         =>  'Веб-сервис',
	'fields'        =>  array(
		'id'        	=>  'index',
		'name'	    	=>  'input',
		'login'    		=>  'input',
		'pass'    		=>  'password',
		'login2'    	=>  'input',
		'pass2'    		=>  'password',
		'login_uid'    	=>  'input',
		'importer_id'   =>  array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'importers',
		),
		'is_active'   	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'is_groups'   	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'file'   		=>  'wbs',
		'class'    		=>  'input',
		'param_typeview'    =>  array(
			'type'	=>	'listvalue',
			'values'	=>	array(
				0 => 'Выводить все',
				1 => 'Ваводить, где наличие больше 0',
			),
		),
		'descr' => array('type'=>'descr','class'=>'see-data'),
	),
	'generator' => array(
		'disabled' => array('add','delete'),
		'fields' => array(
			'id'			=>  'ID',
			'name'	    	=>  'Название',
			'login'    		=>  'Логин',
			'pass'    		=>  'Пароль',
			'login2'    	=>  'Логин (дополнительный)',
			'pass2'    		=>  'Пароль (дополнительный)',
			'login_uid'    	=>  'Ключ авторизации (не для всех)',
			'importer_id'   =>  'Поставщик',
			'is_active'   	=>  'Активировать',
			'is_groups'   	=>  'Работает через группы (каталоги, предпоиск)',
			'file'   		=>  'Испольняющий файл',
			'class'    		=>  'Класс',
			'param_typeview'	=>	'Параметр вывода',
			'descr' 			=> 'Описание веб-сервиса',
		),
		'list' => array(
			'fields' => array('id','name','importer_id','is_active','file','class','descr'),
			'title'	 => 'Веб-сервис',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','login','pass','login2','pass2','login_uid','importer_id','is_active','param_typeview','descr',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','login','pass','login2','pass2','login_uid','importer_id','is_active','is_groups','file','class','param_typeview','descr',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);