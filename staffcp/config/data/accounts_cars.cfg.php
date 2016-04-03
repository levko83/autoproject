<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['accounts_cars'] = array(
	'table'         =>  DB_PREFIX.'accounts_cars',
	'title'         =>  $translates['admin.accounts.name'],
	'fields'        =>  array(
		'id'        	=>  'index',
		'account_id'    =>  array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'accounts',
		),
		'car_id'    		=>  'input',
		'car_model_id'	    =>  'input',
		'car_type_id'    	=>  'input',
		
		'car_name'    		=>  'input',
		'car_year'    		=>  'input',
		'car_kpp'    		=>  'input',
		'car_rul'    		=>  'input',
		'car_cond'    		=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'car_abs'    		=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'car_quattro'    	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'car_body'    		=>  'input',
		'car_vin'    		=>  'input',
		'car_info'    		=>  'text',
		'car_photo'		  	=> 	array(
			'type'			=> 'imageResize',
			'base_dir'		=> 'data/',
			'images' => array(
				'normal'	=>	'130x100',
			),
		),
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'id'				=>  'ID',
			'account_id'    	=>  'Клиент',
			'car_id'    		=>  'ID TECDOC Марка',
			'car_model_id'	    =>  'ID TECDOC Модель',
			'car_type_id'    	=>  'ID TECDOC Модификация',
			
			'car_name'    		=>  'Марка / Модель / Модификация / Год / Объем / Мощность / Силы',
			'car_year'    		=>  'Точный год месяц выпуска',
			'car_kpp'    		=>  'КПП',
			'car_rul'    		=>  'Усилитель руля',
			'car_cond'    		=>  'Кондиционер',
			'car_abs'    		=>  'ABS',
			'car_quattro'    	=>  'Полный привод',
			'car_body'    		=>  'Кузов',
			'car_vin'    		=>  'VIN',
			'car_info'    		=>  'Дополнительно',
			'car_photo'   		=>  'Фото автомобиля',
		),
		'list' => array(
			'fields' => array('id','account_id','car_name','car_year','car_body','car_vin'),
			'title'	 => $translates['admin.accounts.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'account_id','car_name','car_year','car_kpp','car_rul','car_cond','car_abs','car_quattro','car_body','car_vin','car_info','car_photo'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'account_id','car_name','car_year','car_kpp','car_rul','car_cond','car_abs','car_quattro','car_body','car_vin','car_info','car_photo'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);