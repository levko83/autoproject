<?php

class ModelExtension {
	
	protected $model = null;
	
	public function __construct($model)
	{
		$this->setModel($model);
	}
	
	public function setModel($model)
	{
		$this->model = $model;
	}
	
	public function getModel()
	{
		return $this->model;
	}
}