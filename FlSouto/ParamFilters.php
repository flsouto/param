<?php

namespace FlSouto;

class ParamFilters{

	static $errmsg_required = 'This field is required';
	static $errmsg_maxlen = 'This field cannot have more than %d characters.';
	static $errmsg_minlen = 'This field cannot have less than %d characters.';
	static $errmsg_maxval = 'This field cannot contain a value grater than %d.';
	static $errmsg_minval = 'This field cannot contain a value lower than %d.';
	static $errmsg_ifmatch = 'The value supplied to this field is invalid.';
	static $errmsg_ifnot = 'The value supplied to this field is invalid.';

	protected $param;
	function __construct(Param $param){
		$this->param = $param;
	}

	function trim(){
		$this->param->pipe()->add('trim');
		return $this;
	}

	function strip($pattern){
		$this->replace($pattern,"");
		return $this;
	}

	function replace($pattern, $replacement){
		if(substr($pattern,0,1)!='/'){
			$pattern = "/$pattern/";
		}
		$this->param->pipe()->add(function($value) use($pattern, $replacement){
			return preg_replace($pattern, $replacement, $value);
		});
		return $this;
	}

	protected function isEmpty($value){
		return is_null($value) || $value=='';
	}

	function required($errmsg=''){
		$errmsg = $errmsg ?: self::$errmsg_required;
		$this->param->pipe()->add(function($value) use($errmsg){
			if($this->isEmpty($value)){
				echo $errmsg;
			}
		});
		return $this;
	}

	function ifmatch($pattern, $errmsg=''){
		$errmsg = $errmsg ?: self::$errmsg_ifmatch;
		if(substr($pattern,0,1)!='/'){
			$pattern = "/$pattern/";
		}
		$this->param->pipe()->add(function($value) use($pattern, $errmsg){
			if($this->isEmpty($value)){
				return;
			}
			if(preg_match($pattern,$value)){
				echo $errmsg;
			}
		});
		return $this;
	}

	function ifnot($pattern, $errmsg=''){
		$errmsg = $errmsg ?: self::$errmsg_ifnot;
		if(substr($pattern,0,1)!='/'){
			$pattern = "/$pattern/";
		}
		$this->param->pipe()->add(function($value) use($pattern, $errmsg){
			if($this->isEmpty($value)){
				return;
			}
			if(!preg_match($pattern,$value)){
				echo $errmsg;
			}
		});
		return $this;
	}

	function maxlen($maxlen, $errmsg=''){
		$errmsg = $errmsg ?: self::$errmsg_maxlen;
		$this->param->pipe()->add(function($value) use($maxlen, $errmsg){
			if($this->isEmpty($value)){
				return;
			}
			if(function_exists('mb_strlen')){
				$length = mb_strlen($value);
			} else {
				$length = strlen($value);
			}
			if($length > $maxlen){
				printf($errmsg,$maxlen);
			}
		});
		return $this;
	}

	function minlen($minlen, $errmsg=''){
		$errmsg = $errmsg ?: self::$errmsg_minlen;
		$this->param->pipe()->add(function($value) use($minlen, $errmsg){
			if($this->isEmpty($value)){
				return;
			}
			if(function_exists('mb_strlen')){
				$length = mb_strlen($value);
			} else {
				$length = strlen($value);
			}
			if($length < $minlen){
				printf($errmsg,$minlen);
			}
		});
		return $this;
	}

	function maxval($maxval, $errmsg=''){
		$errmsg = $errmsg ?: self::$errmsg_maxval;
		$this->param->pipe()->add(function($value) use($maxval,$errmsg){
			if($this->isEmpty($value)){
				return;
			}
			if($value > $maxval){
				printf($errmsg,$maxval);
			}
		});
		return $this;
	}

	function minval($minval, $errmsg=''){
		$errmsg = $errmsg ?: self::$errmsg_minval;
		$this->param->pipe()->add(function($value) use($minval,$errmsg){
			if($this->isEmpty($value)){
				return;
			}
			if($value < $minval){
				printf($errmsg,$minval);
			}
		});
		return $this;
	}

}