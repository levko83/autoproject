<?php
global $cmsGenerator;
/*

alter table w_page add name_en text not null default '';
alter table w_page add content_en text not null default '';
alter table w_page add title_en text not null default '';

alter table w_page add name_fr text not null default '';
alter table w_page add content_fr text not null default '';
alter table w_page add title_fr text not null default '';

alter table w_page add name_it text not null default '';
alter table w_page add content_it text not null default '';
alter table w_page add title_it text not null default '';

alter table w_page add name_gr text not null default '';
alter table w_page add content_gr text not null default '';
alter table w_page add title_gr text not null default '';

alter table w_page add name_no text not null default '';
alter table w_page add content_no text not null default '';
alter table w_page add title_no text not null default '';

alter table w_page add name_da text not null default '';
alter table w_page add content_da text not null default '';
alter table w_page add title_da text not null default '';

alter table w_page add name_es text not null default '';
alter table w_page add content_es text not null default '';
alter table w_page add title_es text not null default '';

*/


$translates = Register::get('translates');

$cmsGenerator['page'] = array(
	'table'         =>  DB_PREFIX.'page',
	'title'         =>  $translates['admin.page.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'name_en'		  	=> 	'input',
		'name_fr'		  	=> 	'input',
		'name_it'		  	=> 	'input',
		'name_gr'		  	=> 	'input',
		'name_no'		  	=> 	'input',
		'name_da'		  	=> 	'input',
		'name_es'		  	=> 	'input',
		'name_ru'		  	=> 	'input',
		'code'			=> 	'code',
		'content'		=> 	'htmlarea',
		'content_en'		=> 	'htmlarea',
		'content_fr'		=> 	'htmlarea',
		'content_it'		=> 	'htmlarea',
		'content_gr'		=> 	'htmlarea',
		'content_no'		=> 	'htmlarea',
		'content_da'		=> 	'htmlarea',
		'content_es'		=> 	'htmlarea',
		'content_ru'		=> 	'htmlarea',
		'sort'			=> 	'input',
		'title'		  	=> 	'input',
		'title_en'		  	=> 	'input',
		'title_fr'		  	=> 	'input',
		'title_it'		  	=> 	'input',
		'title_gr'		  	=> 	'input',
		'title_no'		  	=> 	'input',
		'title_da'		  	=> 	'input',
		'title_es'		  	=> 	'input',
		'title_ru'		  	=> 	'input',
		'kwords'	  	=> 	'input',
		'descr'			=> 	'input',
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno'])
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.main.name'],
			'name_en'		  	=> 	$translates['admin.main.name'],
			'name_fr'		  	=> 	$translates['admin.main.name'],
			'name_it'		  	=> 	$translates['admin.main.name'],
			'name_gr'		  	=> 	$translates['admin.main.name'],
			'name_no'		  	=> 	$translates['admin.main.name'],
			'name_da'		  	=> 	$translates['admin.main.name'],
			'name_es'		  	=> 	$translates['admin.main.name'],
			'name_ru'		  	=> 	$translates['admin.main.name'],
			'code'		  	=> 	$translates['admin.main.alias'],
			'content'		=> 	$translates['admin.main.content'],
			'content_en'		=> 	$translates['admin.main.content'],
			'content_fr'		=> 	$translates['admin.main.content'],
			'content_it'		=> 	$translates['admin.main.content'],
			'content_gr'		=> 	$translates['admin.main.content'],
			'content_no'		=> 	$translates['admin.main.content'],
			'content_da'		=> 	$translates['admin.main.content'],
			'content_es'		=> 	$translates['admin.main.content'],
			'content_ru'		=> 	$translates['admin.main.content'],
			'sort'		  	=> 	$translates['admin.main.sort'],
			'title'			=>  $translates['admin.main.seo_title'],
			'title_en'			=>  $translates['admin.main.seo_title'],
			'title_fr'			=>  $translates['admin.main.seo_title'],
			'title_it'			=>  $translates['admin.main.seo_title'],
			'title_gr'			=>  $translates['admin.main.seo_title'],
			'title_no'			=>  $translates['admin.main.seo_title'],
			'title_da'			=>  $translates['admin.main.seo_title'],
			'title_es'			=>  $translates['admin.main.seo_title'],
			'title_ru'			=>  $translates['admin.main.seo_title'],
			'kwords'		=>  $translates['admin.main.seo_kwords'],
			'descr'			=>  $translates['admin.main.seo_descr'],
			'is_active'		=> 	$translates['admin.main.view']
		),
		'list' => array(
			'fields' => array('name','code','sort','is_active'),
			'title'	 => $translates['admin.page.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'code','sort','is_active'
				),
				'German' => array(
					'name','title','content'
				), 
				'Russian' => array(
					'name_ru','title_ru','content_ru'
				), 
				'English' => array(
					'name_en','title_en','content_en'
				), 
				'French' => array(
					'name_fr','title_fr','content_fr'
				), 
				'Italian' => array(
					'name_it','title_it','content_it'
				), 
				'Greek' => array(
					'name_gr','title_gr','content_gr'
				), 
				'Norwegian' => array(
					'name_no','title_no','content_no'
				), 
				'Danish' => array(
					'name_da','title_da','content_da'
				), 
				'Spanish' => array(
					'name_es','title_es','content_es'
				), 
				
				'SEO' => array(
					'kwords','descr',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'code','sort','is_active'
				),
				'German' => array(
					'name','title','content'
				), 
				'Russian' => array(
					'name_ru','title_ru','content_ru'
				), 
				'English' => array(
					'name_en','title_en','content_en'
				), 
				'French' => array(
					'name_fr','title_fr','content_fr'
				), 
				'Italian' => array(
					'name_it','title_it','content_it'
				), 
				'Greek' => array(
					'name_gr','title_gr','content_gr'
				), 
				'Norwegian' => array(
					'name_no','title_no','content_no'
				), 
				'Danish' => array(
					'name_da','title_da','content_da'
				), 
				'Spanish' => array(
					'name_es','title_es','content_es'
				), 
				
				'SEO' => array(
					'kwords','descr',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);