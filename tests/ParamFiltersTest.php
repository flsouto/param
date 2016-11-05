<?php

use PHPUnit\Framework\TestCase;
require_once('vendor/autoload.php');

use FlSouto\Param;
use FlSouto\ParamFilters;

class ParamFiltersTest extends TestCase{


	function testTrim(){
		
		Param::get('name')
			->filters()
			->trim();

		$result = Param::get('name')->process(['name'=>' maria ']);

		$this->assertEquals('maria', $result->output);

	}

	function testReplace(){
		Param::get('file_name')
			->context(['file_name'=>'My  untitled document.pdf'])	
			->filters()
			->replace('\s+', '-');

		$output = Param::get('file_name')->process()->output;

		$this->assertEquals('My-untitled-document.pdf',$output);
	}

	function testReplaceWithModifiers(){
		Param::get('style')
			->context(['style'=>'CamelCase'])	
			->filters()
			->replace('/camelcase/i', 'under_scores');

		$output = Param::get('style')->process()->output;

		$this->assertEquals('under_scores',$output);
	}

	function testStrip(){
		Param::get('name')->filters()->strip('[^\d]');

		$result = Param::get('name')->process(['name'=>'f4b10']);

		$this->assertEquals('410', $result->output);
	}

	function testRequired(){
		Param::get('name')
			->context(['name'=>''])
			->filters()
				->required("Cannot be empty");

		$error = Param::get('name')->process()->error;
		$this->assertEquals("Cannot be empty", $error);
	}

	function testRequiredDefaultMessage(){

		ParamFilters::$errmsg_required = 'Cannot be empty';

		Param::get('name')
			->context(['name'=>''])
			->filters()
				->required();

		$error = Param::get('name')->process()->error;
		$this->assertEquals("Cannot be empty", $error);
	}

	function testIfmatch(){

		Param::get('name')
			->filters()
			->ifmatch('\d', 'Name cannot contain digits');

		$error = Param::get('name')->process(['name'=>'M4ry'])->error;

		$this->assertEquals("Name cannot contain digits", $error);

	}

	function testIfmatchWithModifiers(){

		Param::get('phone')
			->filters()
			->ifmatch('/[a-z]/i', 'Phone cannot contain letters');

		$error = Param::get('phone')->process(['phone'=>'9829574K'])->error;

		$this->assertEquals("Phone cannot contain letters", $error);

	}

	function testIfmatchRunsOnlyIfNotEmpty(){

		Param::get('phone')
			->filters()
			->ifmatch('/[a-z]/i', 'Phone cannot contain letters');

		$error = Param::get('phone')->process(['phone'=>''])->error;

		$this->assertNull($error);

	}

	function testIfnot(){

		Param::get('date')->filters()->ifnot('/^\d{4}-\d{2}-\d{2}$/','Date is expected to be in the format yyyy-mm-dd');
		$error = Param::get('date')->process(['date'=>'10/12/1992'])->error;

		$this->assertContains('yyyy', $error);

	}

	function testIfnotRunsOnlyIfNotEmpty(){

		Param::get('login')->filters()->ifnot('\w','Login must contain at least one letter');
		$error = Param::get('login')->process([])->error;

		$this->assertNull($error);
		
	}

	function testMaxlen(){
		
		Param::get('description')->filters()->maxlen(30, "Description must be less than %d characters long!");
		$error = Param::get('description')
			->process(['description'=>str_repeat('lorem ipsum', 10)])
			->error;

		$this->assertContains("less than 30", $error);
	}

	function testMinlen(){

		Param::get('description')->filters()->minlen(10, "Description must be at least %d characters long!");

		$error = Param::get('description')->process(['description'=>'Test'])->error;

		$this->assertContains('at least 10', $error);

	}

	function testMinlenRunOnlyIfNotEmpty(){

		Param::get('description')->filters()->minlen(10, "Description must be at least %d characters long!");

		$error = Param::get('description')->process(['description'=>''])->error;

		$this->assertNull($error);

	}

	function testMaxval(){

		Param::get('age')->filters()->maxval(150, "Age cannot be more than %d!");
		$error = Param::get('age')->process(['age'=>200])->error;

		$this->assertContains("more than 150", $error);
	}

	function testMinval(){

		Param::get('age')->filters()->minval(1, "Age cannot be less than %d!");
		$error = Param::get('age')->process(['age'=>0])->error;

		$this->assertContains("less than 1", $error);

	}

	function testMinvalRunsOnlyIfNotEmpty(){

		Param::get('age')->filters()->minval(1, "Age cannot be less than %d!");
		$error = Param::get('age')->process(['age'=>null])->error;

		$this->assertNull($error);

	}

}