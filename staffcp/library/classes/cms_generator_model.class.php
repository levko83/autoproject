<?php

class CmsGeneratorModel extends Orm {
	
	protected $generatorConfig;
	protected $table;
	
	public function __construct($generatorConfig)
	{
		$this->generatorConfig = $generatorConfig;
		$this->table = $generatorConfig->getTable();
		parent::__construct($this->table);
	}
	
	public function insert($data) {
		$data = $this->generatorConfig->getSaveData($data);
		return parent::insert($data);
	}
	
	public function update($data, $cond='1=1') {
		$data = $this->generatorConfig->getSaveData($data);
		parent::update($data, $cond);
	}	
}