<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['seo'] = array(
	'table'         =>  DB_PREFIX.'seo',
	'title'         =>  'SEO',
	'fields'        =>  array(
		'id_num'    =>  'index',
		'id'		=> 	'input',
		'title'		=> 	'input',
		'kwords'	=> 	'input',
		'descr'		=> 	'input',		
	),
	'generator' => array(
		//'required' => array('id'=>'exist','seo_title'=>'exist'),
		'fields' => array(
			'id_dum' 	=> 'Key',
			'id' 		=> $translates['admin.main.key'],
			'title'		=> $translates['admin.main.seo_title'],
			'kwords'	=> $translates['admin.main.seo_kwords'],
			'descr'		=> $translates['admin.main.seo_descr'],
			
		),
		'list' => array(
			'fields' => array('id', 'title'),
			'title'	 => 'SEO',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'id','title','kwords','descr',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'id','title','kwords','descr',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)
);
?>