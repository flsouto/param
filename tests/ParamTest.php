<?php

use PHPUnit\Framework\TestCase;
use FlSouto\Param;
require_once('vendor/autoload.php');

class ParamTest extends TestCase{

	function testCreation(){
		$param = new Param('test');
		$this->assertEquals('test', $param->name());
	}

	function testRegistry(){
		$param1 = Param::get('country');
		$param2 = Param::get('country');
		$this->assertEquals($param1, $param2);
	}

	function testProcessing(){
		$param = Param::get('user_id');
		$result = $param->process(['user_id'=>5]);
		$this->assertEquals(5, $result->output);
	}

	function testPresetContext(){
		$param = Param::get('language');
		$param->context([
			'var1' => '...',
			'var2' => '...',
			'language' => 'en'
		]);
		$result = $param->process();
		$this->assertEquals('en',$result->output);
	}

	function testPresetContextWithArrayObject(){
		$context = new ArrayObject;
		$param = Param::get('lang_code');
		$param->context($context);
		$context['lang_code'] = 'en';
		$result = $param->process();
		$this->assertEquals('en', $result->output);
	}

	function testValidation(){
		$param = Param::get('user');
		$param->pipe()->add('trim')->add(function($value){
			if(empty($value)){
				echo 'Cannot be empty';
			}
		});
		$result = $param->process(['user'=>'  ']);
		$this->assertEquals('Cannot be empty', $result->error);
	}

	function testFallback(){
		$param = Param::get('lang');
		$param->fallback('en');
		$param->pipe()->add(function($value){
			if(empty($value)){
				echo 'No language selected';
			}
		});
		$result = $param->process([]);
		$this->assertEquals('en', $result->output);
	}

	function testChaining(){
		
		$_REQUEST['lang'] = 'pt';

		Param::get('lang')
			->fallback('en')
			->context($_REQUEST)
			->pipe()
				->add('trim')
				->add(function($value){
					if(empty($value)) echo 'Cannot be empty';
				});

		$result = Param::get('lang')->process();

		$this->assertEquals('pt', $result->output);
	}

	function testSetup(){
		Param::setup(function(Param $param){
			$param->context($_REQUEST)->filters()->trim();
		});

		$_REQUEST = ['name'=>'Fabio ','age'=>' 666 '];

		$name = (new Param('name'))->process()->output;
		$age = (new Param('age'))->process()->output;

		$this->assertEquals("Fabio is 666 years old", "$name is $age years old");
	}

	function testFilters(){
		$filters = Param::get('name')
			->filters()
				->trim()
				->required();

		$this->assertInstanceOf("FlSouto\\ParamFilters", $filters);

	}

}



