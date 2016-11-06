<?php

use PHPUnit\Framework\TestCase;

#mdx:h autoload
require_once('vendor/autoload.php');

#mdx:h useContext
use FlSouto\ParamContext;

#mdx:h useParam
use FlSouto\Param;

/*
## ParamContext

The `ParamContext` allows you to create params out of a predefined context.
It's actually a wrapper to the ArrayObject class that keeps track of added parameters:

#mdx:dataProcessing -h:useParam

The output will be an associative array with all the parameters extracted:

#mdx:dataProcessing -o

*/
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
		#mdx:dataProcessing
		$context = new ParamContext();
		$context->param('user_id');
		$context->param('lang_code')->filters()->trim();

		$context['user_id'] = 5;
		$context['lang_code'] = 'en ';

		$result = $context->process();
		#/mdx print_r($result->output)
		$this->assertEquals(['user_id'=>5,'lang_code'=>'en'],$result->output);

	}

/* 
You can still process an individual param and get its output:

#mdx:individual -php -h:useParam

The output will be:

#mdx:individual -o

*/

	function testIndividualParamProcessing(){
		#mdx:individual
		$context = new ParamContext();
		$context->param('lang_code')->filters()->trim();

		$context['lang_code'] = 'en ';
		// process individual param:
		$result = $context->param('lang_code')->process();
		#/mdx var_dump($result->output)

		$this->assertEquals('en',$result->output);

	}
/*
### Validation

If there are any errors, these will be available as an associative array inside the ´$result->errors´ variable.

#mdx:validation -php -h:useParam

Output:

#mdx:validation -o

*/
	function testValidationErrors(){
		#mdx:validation
		$context = new ParamContext();
		$required = function($value){
			if(empty($value)){
				echo "Cannot be empty";
			}
		};

		$context->param('user_id')->pipe()->add($required);
		$context->param('user_name')->pipe()->add($required);

		$result = $context->process();
		#/mdx print_r($result->errors)		
		$this->assertEquals(['user_id'=>"Cannot be empty",'user_name'=>"Cannot be empty"], $result->errors);
		
	}


}




