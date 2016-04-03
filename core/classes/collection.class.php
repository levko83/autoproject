<?php

class Collection extends ArrayObject {
	
	protected $model = null;
	
	public function setModel($model)
	{
		$this->model = $model;
	}
	
	public function getModel()
	{
		return $this->model;
	}
	
	public function __call($methodName, $arguments)
	{
		if (!empty($this->model))
		{
			if (($result = $this->model->callExtension($methodName, $this)) !== false) {
				return $result;
			} else {
				throw new Exception("Unknow method ".$methodName);
			}
		} else {
			throw new Exception("Unknow method ".$methodName);
		}
	}
}