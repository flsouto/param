<?php

namespace FlSouto;

class ParamContext extends \ArrayObject{

	protected $params = [];

	function param($param){
		if($param instanceof Param){
			// ...
		} else if(is_string($param)) {
			if(isset($this->params[$param])){
				return $this->params[$param];
			}
			$param = new Param($param);
		} else {
			throw new \InvalidArgumentException("Parameter must be string or an instance of Param.");
		}
		$param->context($this);
		$this->params[$param->name()] = $param;
		return $param;
	}

	function process(){
		$output = [];
		$errors = [];
		foreach($this->params as $param){
			$result = $param->process($this);
			$output[$param->name()] = $result->output;
			if($error = $result->error){
				$errors[$param->name()] = $result->error;
			}
		}
		$result = new ParamContextResult();
		$result->output = $output;
		$result->errors = $errors;
		return $result;
	}

}

class ParamContextResult{

	var $output = [];
	var $errors = [];

}