<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['harverster_ftp__params'] = array(
	'table'         =>  DB_PREFIX.'harverster_ftp__params',
	'title'         =>  'Настройка FTP подключения',
	'fields'        =>  array(
		'id' => 'index',
		'name' => 'input',
		'host' => 'input',
		'hlogin' => 'input',
		'hpass' => 'password',
		'hsearch' => 'input',
		'importer_id' => array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'importers',
			'first'	=>	array(
				'0' => '',
			),
		),
		'format' => array(
			'type' => 'listvalue',
				'values' => array(
					'csv' => 'csv',
					'txt' => 'txt',
					'xlsx' => 'xlsx',
				)
		),
		'split' => 'input',
		'colum_article' => 'input',
		'colum_brand' => 'input',
		'colum_name' => 'input',
		'colum_box' => 'input',
		'colum_price' => 'input',
		'rar_password' => 'input',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id' => 'ID',
			'name' => 'Название',
			'host' => 'Хост',
			'hlogin' => 'Логин',
			'hpass' => 'Пароль',
			'hsearch' => 'Папка',
			'importer_id' => 'Поставщик',
			'format' => 'Формат',
			'split' => 'Разделитель данных',
			'colum_article' => 'Колонка: Артикул',
			'colum_brand' => 'Колонка: Бренд',
			'colum_name' => 'Колонка: Название',
			'colum_box' => 'Колонка: Колво',
			'colum_price' => 'Колонка: Цена',
			'rar_password' => 'Пароль для архива (если есть и это архив)',
		),
		'list' => array(
			'fields' => array('id','name','host','hlogin','hsearch','importer_id','format','split','colum_article','colum_brand','colum_name','colum_box','colum_price','rar_password'),
			'title'	 => 'Настройка FTP подключения',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','host','hlogin','hpass','hsearch','importer_id','format','split','colum_article','colum_brand','colum_name','colum_box','colum_price','rar_password'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','host','hlogin','hpass','hsearch','importer_id','format','split','colum_article','colum_brand','colum_name','colum_box','colum_price','rar_password'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);