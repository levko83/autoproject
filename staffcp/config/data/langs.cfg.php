<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['langs'] = array(
	'table'         =>  DB_PREFIX.'langs',
	'title'         =>  $translates['admin.langs.name'],
	'fields'        =>  array(
		'id'        	=>  'index',
		'code'    		=>  'input',#'readonly',
		'side'    		=>  array(
			'type'	=>	'listvalue',
			'values' => array(
				'0' => $translates['admin.langs.frontend'],
				'1'	=>	$translates['admin.langs.backend'],
			),
		),
		'ru'    		=>  'input',
		'en'	=>  'input',
		'de'	=>  'input',
		'fr'	=>  'input',
		'it'	=>  'input',
		'gr'	=>  'input',
		'no'	=>  'input',
		'da'	=>  'input',
		'es'	=>  'input',
		
	),
	'generator' => array(
		'disabled' => array('delete'),
		'fields' => array(
			'id'			=>  'ID',
			'code'    		=>  $translates['admin.main.code'],
			'side'    		=>  $translates['admin.main.zone'],
			'ru'    		=>  'Русский',
			'en'			=>  'Английский', 
			'de'			=>  'Gereman',
			'fr'			=>  'France',
			'it'			=>  'Italian',
			'gr'			=>  'Greek',
			'no'			=>  'Norwegian',
			'da'			=>  'Danish',
			'es'			=>  'Spanish',
		),
		'list' => array(
			'fields' => array('id','code','ru','en','de','fr','it','gr','no','da','es'),
			'title'	 => $translates['admin.langs.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'side','code','ru','en','de','fr','it','gr','no','da','es'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array( 
				$translates['admin.main.dataname'] => array(
					'side','code','ru','en','de','fr','it','gr','no','da','es'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);