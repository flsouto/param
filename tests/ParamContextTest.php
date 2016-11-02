<?php

use PHPUnit\Framework\TestCase;
require_once('vendor/autoload.php');

use FlSouto\ParamContext;
use FlSouto\Param;

class ParamContextTest extends TestCase{

	
	function testCreation(){
		$context = new ParamContext([
			'user_id' => 5
		]);
		$user_id = $context['user_id'];
		$this->assertEquals(5, $user_id);
	}

	function testParamRegistry(){

		$context = new ParamContext();
		$param = $context->param('lang_code');

		$this->assertEquals('lang_code', $param->name());

	}

	function testParamReturnsSameInstance(){
		$context = new ParamContext();
		$param = Param::get('lang_code');
		$context->param($param);

		$this->assertEquals($context->param('lang_code'), $param);
	}

	function testDataProcessing(){

		$context = new ParamContext();
		$context->param('user_id');
		$context->param('lang_code')->filters()->trim();

		$context['user_id'] = 5;
		$context['lang_code'] = 'en ';

		$result = $context->process();

		$this->assertEquals(['user_id'=>5,'lang_code'=>'en'],$result->output);

	}

	function testIndividualParamProcessing(){
		
		$context = new ParamContext();
		$context->param('lang_code')->filters()->trim();

		$context['lang_code'] = 'en ';

		$result = $context->param('lang_code')->process();

		$this->assertEquals('en',$result->output);

	}

	function testValidationErrors(){

		$context = new ParamContext();
		$required = function($value){
			if(empty($value)){
				echo "Cannot be empty";
			}
		};

		$context->param('user_id')->pipe()->add($required);
		$context->param('user_name')->pipe()->add($required);

		$result = $context->process();

		$this->assertEquals(['user_id'=>"Cannot be empty",'user_name'=>"Cannot be empty"], $result->errors);
		
	}


}




