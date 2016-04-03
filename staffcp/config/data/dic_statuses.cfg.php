<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['dic_statuses'] = array(
	'table'         =>  DB_PREFIX.'dic_statuses',
	'title'         =>  $translates['admin.main.statuses'],
	'fields'        =>  array(
		'id'        	=>  'index',
		'name'	    	=>  'input',
		'color'    		=>  'color',
		'type'    		=>  array(
			'type'	=>	'listvalue',
			'values'	=>	array(
				'0' => $translates['admin.st.simple'],
				'1' => $translates['admin.st.archive'],
				'2' => $translates['admin.st.forimp'],
				'3' => $translates['admin.st.deny'],
			)
		),
		'letter'    	=>  'htmlarea',
		'sort'	    	=>  'input',
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'id'			=>  'ID',
			'name'	    	=>  $translates['admin.accounts.fullname'],
			'color'    		=>  $translates['admin.main.color.rgb'],
			'type'    		=>  $translates['admin.settings.type'],
			'letter'    	=>  'E-mail уведомление',
			'sort'	    	=>  'Сортировка',
		),
		'list' => array(
			'fields' => array('id','name','color','type','sort'),
			'title'	 => $translates['admin.main.statuses'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','color','type','sort'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','color','type','sort'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);