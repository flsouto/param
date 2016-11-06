<?php

use PHPUnit\Framework\TestCase;

#mdx:h use
use FlSouto\Param;

#mdx:h autoload
require_once('vendor/autoload.php');

/*
# Param

## Overview

This library allows you to extract, filter and validate individual fields from an associative array. It can be used, as an example, to validate request variables sent from a web browser. You give each parameter a "name", define the "context" and call "process"; the result will be the value extracted from the context or an error message in case of not conforming to one or more validation filters. Many predefined filters and validators are available from a "finger-friendly" API. You can also specify a fallback value to be returned in case of validation error.

## Installation

Install via composer:

```
composer require flsouto/param
```

## Usage

The example bellow creates a parameter and prints out its name

#mdx:creation

#mdx:creation -o

*/
class ParamTest extends TestCase{

	function testCreation(){
		#mdx:creation
		$param = new Param('test');
		#/mdx echo $param->name()
		$this->assertEquals('test', $param->name());
	}

/*
You can instantiate the parameter from the static `get` method which always returns the same instance for that name:

#mdx:registry -php -h:autoload

The output will be:

#mdx:registry -o

*/
	function testRegistry(){
		#mdx:registry
		$param1 = Param::get('country');
		$param2 = Param::get('country');
		#/mdx var_dump($param1===$param2)
		$this->assertEquals($param1, $param2);
	}

/*
### Processing Input

The `process` instance method receives a "context", which must be an associative array, and tries to extract
the parameter value based on the parameter name.

#mdx:processing -php -h:autoload

The output of the above will be:
#mdx:processing -o

*/
	function testProcessing(){
		#mdx:processing
		$param = Param::get('user_id');
		$result = $param->process(['user_id'=>5]);
		#/mdx echo $result->output
		$this->assertEquals(5, $result->output);
	}

/*
#### Predefined Contexts

You can set the context on the parameter instance before calling the process method:

#mdx:presetContext -php -h:autoload

#mdx:presetContext -o
*/

	function testPresetContext(){
		#mdx:presetContext
		$param = Param::get('language');
		$param->context([
			'var1' => '...',
			'var2' => '...',
			'language' => 'en'
		]);
		$result = $param->process();
		#/mdx echo $result->output
		$this->assertEquals('en',$result->output);
	}

/* 
If you want to set the context and be able to modify it later, I recommend you provide an instance of ArrayObject like in the example bellow:

#mdx:contextWithArrayObject -php -h:autoload

#mdx:contextWithArrayObject -o

Furthermore, you can work with the `ParamContext` class which allows you to factory parameters from a predefined context. Read more about it on the "ParamContext" section.

*/

	function testPresetContextWithArrayObject(){
		#mdx:contextWithArrayObject
		$context = new ArrayObject;
		$param = Param::get('lang_code');
		$param->context($context);
		// modify context later
		$context['lang_code'] = 'en';
		$result = $param->process();
		#/mdx echo $result->output
		$this->assertEquals('en', $result->output);
	}

/*
### Validation

The param object uses an instance of the [Pipe](https://github.com/flsouto/pipe) class
which allows you to bind a series of filters and/or validators to the data:

#mdx:validation -php -h:autoload

Output:
#mdx:validation -o

The above snippet adds a filter that trims the parameter value, and also adds a validator
that produces an error if the final value is empty. The printed error message is then captured
inside the $result->error property.

***Notice:*** the Param class actually provides a bunch of common filters and validatiors through the
`ParamFilters` API. More on this subject in the "ParamFilters API" section. 

*/
	function testValidation(){
		#mdx:validation
		$param = Param::get('user');
		$param->pipe()->add('trim')->add(function($value){
			if(empty($value)){
				echo 'Cannot be empty';
			}
		});
		$result = $param->process(['user'=>'  ']);
		#/mdx echo $result->error

		$this->assertEquals('Cannot be empty', $result->error);
	}

/*
### Fallback values

You can specify a fallback value to be returned in the `$result->output` in case validation fails.

#mdx:fallback -php -h:autoload

The output will be:

#mdx:fallback -o

*/

	function testFallback(){
		#mdx:fallback
		$param = Param::get('lang');
		$param->fallback('en');
		$param->pipe()->add(function($value){
			if(empty($value)){
				echo 'No language selected';
			}
		});
		// pass an empty context
		$result = $param->process([]);
		#/mdx echo $result->output
		$this->assertEquals('en', $result->output);
	}
/*
### Setting up defaults

The library provides the static `setup` method which allows you to intercept all param instances upon their creation.
In the example bellow we define the default context for every parameter in our application to be the $_REQUEST superglobal, and we also set all parameters to be trimmed by default:

#mdx:setup -php -h:autoload

The output from the above code will be:
#mdx:setup -o

*/

	function testSetup(){
		#mdx:setup
		// place this in a global bootstrap script
		Param::setup(function(Param $param){
			$param->context($_REQUEST)->filters()->trim();
		});

		// Processing some request
		$_REQUEST = ['name'=>'Fabio ','age'=>' 666 '];

		$name = (new Param('name'))->process()->output;
		$age = (new Param('age'))->process()->output;
		#/mdx echo "$name is $age years old"
		$this->assertEquals("Fabio is 666 years old", "$name is $age years old");
	}

/*
### Method chaining

Last but not least, all setters of the Param class return the instance itself, so it is possible to use method chaining:

#mdx:chaining

*/

	function testChaining(){

		#mdx:chaining
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
		#/mdx

		$this->assertEquals('pt', $result->output);
	}

	function testFilters(){
		$filters = Param::get('name')
			->filters()
				->trim()
				->required();

		$this->assertInstanceOf("FlSouto\\ParamFilters", $filters);

	}

}



