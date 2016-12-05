<?php

use PHPUnit\Framework\TestCase;
#mdx:h autoload
require_once('vendor/autoload.php');

#mdx:h useParam
use FlSouto\Param;
use FlSouto\ParamFilters;

/*
## ParamFilters

The Param class has an instance method `filters` which returns an instance of `ParamFilters`. 
This class makes the process of filtering and validating data much easier.

The following sections are dedicated to showing the usage of each method in that object.

*/

class ParamFiltersTest extends TestCase{

/*
### trim
Removes espaces from start and end of a string.

#mdx:trim
#mdx:trim -o

*/

	function testTrim(){
		#mdx:trim		
		Param::get('name')
			->filters()
			->trim();

		$result = Param::get('name')->process(['name'=>' maria ']);
		#/mdx var_dump($result->output)
		$this->assertEquals('maria', $result->output);

	}

/*

### replace

The replace filter allows you to replace one string with another, similar to php's str_replace function:

#mdx:replace_no_regex -php -h:autoload

#mdx:replace_no_regex -o

*/

	function testReplaceWihtoutRegex(){
		#mdx:replace_no_regex
		Param::get('money')
			->context(['money'=>'3,50'])
			->filters()
			->replace(',','.');

		$output = Param::get('money')->process()->output;
		#/mdx var_dump($output)
		$this->assertEquals('3.50', $output);

	}

/*

#### replace using regex

If you wrap the search pattern between two slashes, a regular expression will be assumed.

#mdx:replace -php -h:autoload

#mdx:replace -o

*/
	function testReplaceWithRegex(){
		#mdx:replace
		Param::get('file_name')
			->context(['file_name'=>'My  untitled document.pdf'])	
			->filters()
			->replace('/\s+/', '-');

		$output = Param::get('file_name')->process()->output;
		#/mdx var_dump($output)
		$this->assertEquals('My-untitled-document.pdf',$output);
	}

/*
Another example using regex with the "i" modifier:

#mdx:replaceWithModifiers -php -h:autoload

#mdx:replaceWithModifiers -o

*/

	function testReplaceWithModifiers(){
		#mdx:replaceWithModifiers
		Param::get('style')
			->context(['style'=>'CamelCase'])	
			->filters()
			->replace('/camelcase/i', 'under_scores');

		$output = Param::get('style')->process()->output;
		#/mdx var_dump($output)

		$this->assertEquals('under_scores',$output);
	}

/*
### strip

Matches a string or a regex pattern and remove it from the string:

#mdx:strip -php -h:autoload

#mdx:strip -o
*/

	function testStrip(){
		#mdx:strip
		Param::get('name')->filters()->strip('/[^\d]/');

		$result = Param::get('name')->process(['name'=>'f4b10']);
		#/mdx var_dump($result->output)
		$this->assertEquals('410', $result->output);
	}

	function testStripWithoutRegex(){
		$param = (new Param('name'))->filters()->strip('*');
		$result = Param::get('name')->process(['name'=>'f4b*10']);
		$this->assertEquals('f4b10', $result->output);
	}

/*
### required

Makes sure the value is not empty. If empty, an error message is produced. 

#mdx:required -php -h:autoload

#mdx:required -o
*/
	function testRequired(){
		#mdx:required
		Param::get('name')
			->context(['name'=>''])
			->filters()
				->required("Cannot be empty");

		$error = Param::get('name')->process()->error;
		#/mdx var_dump($error)
		$this->assertEquals("Cannot be empty", $error);
	}
/*
All validators have default error messages that can be customized. 
Bellow is an example of redefining the default error message:

#mdx:required2 -php -h:autoload

#mdx:required2 -o
*/
	function testRequiredDefaultMessage(){
		#mdx:required2
		FlSouto\ParamFilters::$errmsg_required = 'Cannot be empty';

		Param::get('name')
			->context(['name'=>''])
			->filters()
				->required();

		$error = Param::get('name')->process()->error;
		#/mdx var_dump($error)
		$this->assertEquals("Cannot be empty", $error);
	}
/*
### ifmatch

Produces an error when the string MATCHES A PATTERN:

#mdx:ifmatch -php -h:autoload

#mdx:ifmatch -o
*/
	function testIfmatch(){
		#mdx:ifmatch
		Param::get('name')
			->filters()
			->ifmatch('/\d/', 'Name cannot contain digits');

		$error = Param::get('name')->process(['name'=>'M4ry'])->error;
		#/mdx var_dump($error)
		$this->assertEquals("Name cannot contain digits", $error);

	}

	function testIfmatchWithoutRegex(){
		Param::get('email_address')
			->filters()
			->ifmatch('?', 'Email cannot contain interrogation marks!');

		$error = Param::get('email_address')->process(['email_address'=>'myemail?@domain.com'])->error;
		$this->assertContains("marks!", $error);

	}

/*
Another example using regex modifiers:

#mdx:ifmatch2 -php -h:autoload

#mdx:ifmatch2 -o
*/
	function testIfmatchWithModifiers(){
		#mdx:ifmatch2
		Param::get('phone')
			->filters()
			->ifmatch('/[a-z]/i', 'Phone cannot contain letters');

		$error = Param::get('phone')->process(['phone'=>'9829574K'])->error;
		#/mdx var_dump($error)
		$this->assertEquals("Phone cannot contain letters", $error);

	}
/*
***Notice***: This validator is only applied when the value is not empty:

#mdx:ifmatch3 -php -h:autoload

#mdx:ifmatch3 -o

If you want to make sure the value is not empty use the `required` filter before `ifmatch`.

*/
	function testIfmatchRunsOnlyIfNotEmpty(){
		#mdx:ifmatch3
		Param::get('phone')
			->filters()
			->ifmatch('/[a-z]/i', 'Phone cannot contain letters');

		$error = Param::get('phone')->process(['phone'=>''])->error;
		#/mdx var_dump($error)

		$this->assertNull($error);

	}
/*
### ifnot

Produces an error when the string DOES NOT MATCH a pattern:

#mdx:ifnot -php -h:autoload

#mdx:ifnot -o
*/
	function testIfnot(){
		#mdx:ifnot
		Param::get('date')->filters()->ifnot('/^\d{4}-\d{2}-\d{2}$/','Date is expected to be in the format yyyy-mm-dd');
		$error = Param::get('date')->process(['date'=>'10/12/1992'])->error;
		#/mdx var_dump($error)

		$this->assertContains('yyyy', $error);

	}
/*
***Notice***: This validator is only applied when the value is not empty:

#mdx:ifnot2 -php -h:autoload

#mdx:ifnot2 -o
*/

	function testIfnotWithoutRegex(){
		
		(new Param('email'))->filters()->ifnot('@', 'Email address should contain a @');

		$error = Param::get('email')->process(['email'=>'myemail.domain.com'])->error;

		$this->assertContains('should', $error);

	}

	function testIfnotRunsOnlyIfNotEmpty(){
		#mdx:ifnot2
		Param::get('login')->filters()->ifnot('\w','Login must contain at least one letter');
		$error = Param::get('login')->process([])->error;
		#/mdx var_dump($error)

		$this->assertNull($error);
		
	}
/*
### maxlen

Makes sure the length of the string doesn't exceed a maximum:

#mdx:maxlen -php -h:autoload

#mdx:maxlen -o
*/
	function testMaxlen(){
		#mdx:maxlen		
		Param::get('description')->filters()->maxlen(30, "Description must be less than %d characters long!");
		$error = Param::get('description')
			->process(['description'=>str_repeat('lorem ipsum', 10)])
			->error;

		#/mdx var_dump($error)

		$this->assertContains("less than 30", $error);
	}
/*

### minlen

Makes sure the length of a string is at least certain characters long:

#mdx:minlen -php -h:autoload

#mdx:minlen -o
*/
	function testMinlen(){
		#mdx:minlen
		Param::get('description')->filters()->minlen(10, "Description must be at least %d characters long!");

		$error = Param::get('description')->process(['description'=>'Test'])->error;
		#/mdx var_dump($error)

		$this->assertContains('at least 10', $error);

	}
/*
***Notice***: This validator is only applied when the value is not empty:

#mdx:minlen2 -php -h:autoload

#mdx:minlen2 -o
*/
	function testMinlenRunOnlyIfNotEmpty(){
		#mdx:minlen2
		Param::get('description')->filters()->minlen(10, "Description must be at least %d characters long!");

		$error = Param::get('description')->process(['description'=>''])->error;
		#/mdx var_dump($error)

		$this->assertNull($error);

	}
/*
### maxval

Makes sure an integer is not more than certain value:

#mdx:maxval -php -h:autoload

#mdx:maxval -o
*/
	function testMaxval(){
		#mdx:maxval
		Param::get('age')->filters()->maxval(150, "Age cannot be more than %d!");
		$error = Param::get('age')->process(['age'=>200])->error;
		#/mdx var_dump($error)

		$this->assertContains("more than 150", $error);
	}
/*
### minval

Makes sure the integer, if provided, is at least larger than X:

#mdx:minval -php -h:autoload

#mdx:minval -o
*/
	function testMinval(){
		#mdx:minval
		Param::get('age')->filters()->minval(1, "Age cannot be less than %d!");
		$error = Param::get('age')->process(['age'=>0])->error;
		#/mdx var_dump($error)

		$this->assertContains("less than 1", $error);

	}
/*

***Notice***: This validator is only applied when the value is not empty:

#mdx:minval2 -php -h:autoload

#mdx:minval2 -o
*/
	function testMinvalRunsOnlyIfNotEmpty(){
		#mdx:minval2
		Param::get('age')->filters()->minval(1, "Age cannot be less than %d!");
		$error = Param::get('age')->process(['age'=>null])->error;
		#/mdx var_dump($error)

		$this->assertNull($error);

	}
/*
### Chaining filters 

Last but not least, you can use method chaning for adding multiple filters.
The filters will be applied to the value in the same order they were added:

#mdx:chaining

In the example above the data will be filtered before the validation constraints and the result will be:

#mdx:chaining -o

*/
	function testChaining(){
		#mdx:chaining
		Param::get('number')
			->filters()
				->strip('/[^\d]/')
				->required()
				->minlen(5)
				->maxlen(10);

		$result = Param::get('number')->process([
			'number' => '203-40-10/80'
		]);
		#/mdx var_dump($result->output)
		$this->assertEquals('203401080',$result->output);

	}

}