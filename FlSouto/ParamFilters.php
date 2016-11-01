<?php

namespace FlSouto;

class ParamFilters{

	static $errmsg_required = '';
	static $errmsg_maxlen = '';
	static $errmsg_minlen = '';
	static $errmsg_maxval = '';
	static $errmsg_minval = '';
	static $errmsg_ifmatch = '';
	static $errmsg_ifnot = '';

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

	function required($errmsg=''){
		$errmsg = $errmsg ?: self::$errmsg_required;
		$this->param->pipe()->add(function($value) use($message){
			if(empty($value)){
				echo $message;
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
			if(!preg_match($pattern,$value)){
				echo $errmsg;
			}
		});
		return $this;
	}

	function maxlen($maxlen, $errmsg=''){
		$errmsg = $errmsg ?: self::$errmsg_maxlen;
		$this->param->pipe()->add(function($value) use($maxlen, $errmsg){
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
			if($value > $maxval){
				printf($errmsg,$maxval);
			}
		});
		return $this;
	}

	function minval($minval, $errmsg=''){
		$errmsg = $errmsg ?: self::$errmsg_minval;
		$this->param->pipe()->add(function($value) use($minval,$errmsg){
			if($value < $minval){
				printf($errmsg,$minval);
			}
		});
		return $this;
	}

}