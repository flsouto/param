<?php

namespace FlSouto;

class Param{

	protected $name;
	protected static $registry = [];
	protected static $setup = null;

	function __construct($name){
		$this->name = $name;
		self::$registry[$name] = $this;
		if(self::$setup){
			call_user_func(self::$setup, $this);
		}
	}

	function name(){
		return $this->name;
	}

	static function get($name){
		if(isset(self::$registry[$name])){
			return self::$registry[$name];
		}
		return new self($name);
	}

	static function setup(callable $callback){
		self::$setup = $callback;
	}

	protected $pipe = null;
	function pipe(){
		if(!$this->pipe){
			$this->pipe = new Pipe();
		}
		return $this->pipe;
	}

	protected $filters = null;
	function filters(){
		if(is_null($this->filters)){
			$this->filters = new ParamFilters($this);
		}
		return $this->filters;
	}

	function fallback($value, $when=[null]){
		$this->pipe()->fallback($value, $when);
		return $this;
	}

	private $context = [];
	function context($data){
		$this->context = $data;
		return $this;
	}

	function process($context = null){
		$context = !is_null($context) ? $context : $this->context;
		$value = isset($context[$this->name]) ? $context[$this->name] : null;
		return $this->pipe()->run($value);
	}

}