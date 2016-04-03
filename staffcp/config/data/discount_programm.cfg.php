<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['discount_programm'] = array(
	'table'         =>  DB_PREFIX.'discount_programm',
	'title'         =>  'Дисконтная программа',
	'descr'         =>  '
		<div style="padding:10px;">
			<h1>Описание работы дисконтной системы</h1>
			<ul>
				<li>Активировать программу необходимо <a href="/staffcp/settings/edit/?id=91">тут</a>.</li>
				<li>Первый уровень программы берется из списка в котором "Сумма ОТ" является минимальным числом.</li>
				<li>Проверка на последующий переход на новый уровень осуществялется при авторизации клиента в личном кабинете каждый раз.</li>
				<li>После года первой установки скидки, система обнуляет скидку клиенту на первый уровень.</li>
				<li>Дисконтная программа работает на уровне установки статичной скидки клиенту в этапном порядке (статичной - это дополнительная скидка с учетом всех программ и групп скидок, которую также можно изменить на каждого клиента индивидуально при редактировании профиля).</li>
			</ul>
		</div>
	',
	'fields'        =>  array(
		'id' => 'index',
		'name' => 'input',
		'total_from' => 'input',
		'total_to' => 'input',
		'discount' => 'input',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name' => 'Название этапа',
			'total_from' => 'Сумма ОТ',
			'total_to' => 'Сумма ДО',
			'discount' => 'Скидка/Наценка',
		),
		'list' => array(
			'fields' => array('id','name','total_from','total_to','discount'),
			'title'	 => 'Дисконтная программа',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','total_from','total_to','discount'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','total_from','total_to','discount'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);