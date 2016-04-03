<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['crosses'] = array(
	'table'         =>  DB_PREFIX.'details_db__crosses',
	'title'         =>  'База кроссов',
	'fields'        =>  array(
		'ID'        	=>  'index',
		'ARTICLE'    	=>  'input',
		'SEARCH_ARTICLE'    	=>  array('type'=>'extends_input_clear','field'=>'ARTICLE'),
		'BRAND'	    	=>  'input',
		'DESCR'	    	=>  'input',
		'IMG'	    	=>  array(
			'type'	=>	'file',
			'base_dir'	=>	'crosses/',
		),
		'CROSS_BRAND'    	=>  'input',
		'CROSS_ARTICLE'    	=>  array('type' => 'input', 'func' => 'clear'),
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'ID'        	=>  'ID',
			'SEARCH_ARTICLE'    	=>  'Поисковой ключ',
			'ARTICLE'    	=>  'Артикул',
			'BRAND'	    	=>  'Бренд',
			'DESCR'	    	=>  'Описание',
			'IMG'	    	=>  'Изображение',
			'CROSS_BRAND'    	=>  'Кросс Бренд',
			'CROSS_ARTICLE'    	=>  'Кросс Артикул',
		),
		'list' => array(
			'fields' => array('ID','ARTICLE','BRAND','DESCR','CROSS_BRAND','CROSS_ARTICLE'),
			'title'	 => 'База кроссов',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
				'SEARCH_ARTICLE','ARTICLE','BRAND','CROSS_ARTICLE','CROSS_BRAND','DESCR','IMG'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
				'SEARCH_ARTICLE','ARTICLE','BRAND','CROSS_ARTICLE','CROSS_BRAND','DESCR','IMG'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);