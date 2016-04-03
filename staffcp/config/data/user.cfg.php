<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['user'] = array(
	'table'         =>  DB_PREFIX.'_user',
	'title'         =>  $translates['admin.user.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'login'		  	=> 	'input',
		'password'		=> 	'input',
		'email'			=> 	'input',
		'is_super'		=> 	array(
			'type' => 'listvalue',
			'values'	=>	array(
				'1'	=>	'Администратор',
				'2'	=>	'Менеджер',
				/*'3'	=>	'Топ-менеджер',*/
			),
		),
		'name'			=> 	'input',
		// 'office_id'			=> 	array(
			// 'type'			=> 'category2',
			// 'cross_name'	=> 'name',
			// 'cross_index'	=> 'id',
			// 'cross_table'	=> DB_PREFIX.'offices',
			
			// 'cross_join_index'	=> 'city_id',
			
			// 'join_name'		=> 'name',
			// 'join_index'	=> 'id',
			// 'join_table'	=> DB_PREFIX . 'dic_cities',
			
			// 'first'	=>	array(
				// '0' => '',
			// ),
		// ),
		// 'photo'		  	=> 	array(
			// 'type'		=> 'imageResize',
			// 'base_dir'	=> 'users/',
			// 'images' => array(
				// 'normal'	=>	'130x100',
			// ),
		// ),
		'contacts'		=> 	'htmlarea',
		
		// 'acl_accounts_access' => array(
			// 'type' => 'listvalue',
			// 'values' => array(
				// 0 => 'Полный доступ к базе клиентов',
				// 1 => 'Доступ к базе клиентов в зависимости от офиса к которому привязан менеджер',
			// )
		// ),
		// 'purchase_margin' => 'input',
		// 'user_permission2status' => array(
			// 'type'	=>	'multiple',
			// 'foreign'	=> array(
				// 'table'			=>	DB_PREFIX.'dic_statuses',
				// 'id'			=>	'id',
				// 'foreign_id'	=>	'id',
				// 'name'			=>	'name',
			// ),
			// 'link'	=> array(
				// 'table'			=>	DB_PREFIX.'_user_permission2status',
				// 'source_id'		=>	'user_id',
				// 'foreign_id'	=>	'status_id',
			// ),
		// ),
	),
	'generator' => array(
		'fields' => array(
			'id'            =>  'ID',
			'login'		  	=> 	$translates['admin.user.login'],
			'password'		=> 	$translates['admin.accounts.pass'],
			'email'			=> 	$translates['admin.imps.email'],
			'is_super'		=> 	'Должность (Доступ)',
			'name'			=> 	'ФИО',
			'office_id'		=>	'Представительство (Офис)',
			'photo'		  	=> 	'Фото',
			'contacts'		=> 	'Контакты',
			
			'acl_accounts_access'   =>  'Доступ к базе клиентов',
			'purchase_margin' => 'Показывать закупочные цены с наценок %',
			'user_permission2status' => 'Доступ к статусам',
		),
		'list' => array(
			'fields' => array('id','login','is_super','name'),
			'title'	 => $translates['admin.user.name'],
			
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'login','password','is_super','name','contacts'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'login','password','is_super','name','contacts'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)
);