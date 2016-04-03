<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['documents_params'] = array(
	'table'         =>  DB_PREFIX.'documents_params',
	'title'         =>  'Значения документов',
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'code'			=> 	'code',
		'value'			=> 	'input',
		'document'		=> 	array(
			'type' => 'listvalue',
			'values' => array(
				'Товарный чек' => 'Товарный чек',
				'Акт-приема передачи' => 'Акт-приема передачи',
				'Бланк заказа' => 'Бланк заказа',
				'Квитанция банка' => 'Квитанция банка',
				'Счет на оплату' => 'Счет на оплату',
				'Счет-фактура' => 'Счет-фактура',
			)
		)
	),
	'generator' => array(
		'disabled' => array('add','delete'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	'Название поля',
			'code'			=> 	'Код',
			'value'			=> 	'Значение',
			'document'		=> 	'Тип документа'
		),
		'list' => array(
			'fields' => array('id','code','name','value'),
			'title'	 => 'Значения документов',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','value'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'code','name','value','document',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);