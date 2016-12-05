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

```php
<?php
use FlSouto\Param;
require_once('vendor/autoload.php');

$param = new Param('test');

echo $param->name();
```

```
test
```


You can instantiate the parameter from the static `get` method which always returns the same instance for that name:

```php
use FlSouto\Param;

$param1 = Param::get('country');
$param2 = Param::get('country');

var_dump($param1===$param2);
```

The output will be:

```
bool(true)

```


### Processing Input

The `process` instance method receives a "context", which must be an associative array, and tries to extract
the parameter value based on the parameter name.

```php
use FlSouto\Param;

$param = Param::get('user_id');
$result = $param->process(['user_id'=>5]);

echo $result->output;
```

The output of the above will be:
```
5
```


#### Predefined Contexts

You can set the context on the parameter instance before calling the process method:

```php
use FlSouto\Param;

$param = Param::get('language');
$param->context([
	'var1' => '...',
	'var2' => '...',
	'language' => 'en'
]);
$result = $param->process();

echo $result->output;
```

```
en
```

If you want to set the context and be able to modify it later, I recommend you provide an instance of ArrayObject like in the example bellow:

```php
use FlSouto\Param;

$context = new ArrayObject;
$param = Param::get('lang_code');
$param->context($context);
// modify context later
$context['lang_code'] = 'en';
$result = $param->process();

echo $result->output;
```

```
en
```

Furthermore, you can work with the `ParamContext` class which allows you to factory parameters from a predefined context. Read more about it on the "ParamContext" section.


### Validation

The param object uses an instance of the [Pipe](https://github.com/flsouto/pipe) class
which allows you to bind a series of filters and/or validators to the data:

```php
use FlSouto\Param;

$param = Param::get('user');
$param->pipe()->add('trim')->add(function($value){
	if(empty($value)){
		echo 'Cannot be empty';
	}
});
$result = $param->process(['user'=>'  ']);

echo $result->error;
```

Output:
```
Cannot be empty
```

The above snippet adds a filter that trims the parameter value, and also adds a validator
that produces an error if the final value is empty. The printed error message is then captured
inside the $result->error property.

***Notice:*** the Param class actually provides a bunch of common filters and validatiors through the
`ParamFilters` API. More on this subject in the "ParamFilters API" section. 


### Fallback values

You can specify a fallback value to be returned in the `$result->output` in case validation fails.

```php
use FlSouto\Param;

$param = Param::get('lang');
$param->fallback('en');
$param->pipe()->add(function($value){
	if(empty($value)){
		echo 'No language selected';
	}
});
// pass an empty context
$result = $param->process([]);

echo $result->output;
```

The output will be:

```
en
```


### Setting up defaults

The library provides the static `setup` method which allows you to intercept all param instances upon their creation.
In the example bellow we define the default context for every parameter in our application to be the $_REQUEST superglobal, and we also set all parameters to be trimmed by default:

```php
use FlSouto\Param;

// place this in a global bootstrap script
Param::setup(function(Param $param){
	$param->context($_REQUEST)->filters()->trim();
});

// Processing some request
$_REQUEST = ['name'=>'Fabio ','age'=>' 666 '];

$name = (new Param('name'))->process()->output;
$age = (new Param('age'))->process()->output;

echo "$name is $age years old";
```

The output from the above code will be:
```
Fabio is 666 years old
```


### Method chaining

Last but not least, all setters of the Param class return the instance itself, so it is possible to use method chaining:

```php
<?php
use FlSouto\Param;
require_once('vendor/autoload.php');

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


```
## ParamContext

The `ParamContext` allows you to create params out of a predefined context.
It's actually a wrapper to the ArrayObject class that keeps track of added parameters:

```php
<?php
require_once('vendor/autoload.php');
use FlSouto\ParamContext;

$context = new ParamContext();
$context->param('user_id');
$context->param('lang_code')->filters()->trim();

$context['user_id'] = 5;
$context['lang_code'] = 'en ';

$result = $context->process();

print_r($result->output);
```

The output will be an associative array with all the parameters extracted:

```
Array
(
    [user_id] => 5
    [lang_code] => en
)

```


You can still process an individual param and get its output:

```php
require_once('vendor/autoload.php');
use FlSouto\ParamContext;

$context = new ParamContext();
$context->param('lang_code')->filters()->trim();

$context['lang_code'] = 'en ';
// process individual param:
$result = $context->param('lang_code')->process();

var_dump($result->output);
```

The output will be:

```
string(2) "en"

```


### Validation

If there are any errors, these will be available as an associative array inside the ´$result->errors´ variable.

```php
require_once('vendor/autoload.php');
use FlSouto\ParamContext;

$context = new ParamContext();
$required = function($value){
	if(empty($value)){
		echo "Cannot be empty";
	}
};

$context->param('user_id')->pipe()->add($required);
$context->param('user_name')->pipe()->add($required);

$result = $context->process();

print_r($result->errors);
```

Output:

```
Array
(
    [user_id] => Cannot be empty
    [user_name] => Cannot be empty
)

```
## ParamFilters

The Param class has an instance method `filters` which returns an instance of `ParamFilters`. 
This class makes the process of filtering and validating data much easier.

The following sections are dedicated to showing the usage of each method in that object.


### trim
Removes espaces from start and end of a string.

```php
<?php
require_once('vendor/autoload.php');
use FlSouto\Param;

Param::get('name')
	->filters()
	->trim();

$result = Param::get('name')->process(['name'=>' maria ']);

var_dump($result->output);
```
```
string(5) "maria"

```



### replace

The replace filter allows you to replace one string with another, similar to php's str_replace function:

```php
use FlSouto\Param;

Param::get('money')
	->context(['money'=>'3,50'])
	->filters()
	->replace(',','.');

$output = Param::get('money')->process()->output;

var_dump($output);
```

```
string(4) "3.50"

```



#### replace using regex

If you wrap the search pattern between two slashes, a regular expression will be assumed.

```php
use FlSouto\Param;

Param::get('file_name')
	->context(['file_name'=>'My  untitled document.pdf'])	
	->filters()
	->replace('/\s+/', '-');

$output = Param::get('file_name')->process()->output;

var_dump($output);
```

```
string(24) "My-untitled-document.pdf"

```


Another example using regex with the "i" modifier:

```php
use FlSouto\Param;

Param::get('style')
	->context(['style'=>'CamelCase'])	
	->filters()
	->replace('/camelcase/i', 'under_scores');

$output = Param::get('style')->process()->output;

var_dump($output);
```

```
string(12) "under_scores"

```


### strip

Matches a string or a regex pattern and remove it from the string:

```php
use FlSouto\Param;

Param::get('name')->filters()->strip('/[^\d]/');

$result = Param::get('name')->process(['name'=>'f4b10']);

var_dump($result->output);
```

```
string(3) "410"

```

### required

Makes sure the value is not empty. If empty, an error message is produced. 

```php
use FlSouto\Param;

Param::get('name')
	->context(['name'=>''])
	->filters()
		->required("Cannot be empty");

$error = Param::get('name')->process()->error;

var_dump($error);
```

```
string(15) "Cannot be empty"

```

All validators have default error messages that can be customized. 
Bellow is an example of redefining the default error message:

```php
use FlSouto\Param;

FlSouto\ParamFilters::$errmsg_required = 'Cannot be empty';

Param::get('name')
	->context(['name'=>''])
	->filters()
		->required();

$error = Param::get('name')->process()->error;

var_dump($error);
```

```
string(15) "Cannot be empty"

```

### ifmatch

Produces an error when the string MATCHES A PATTERN:

```php
use FlSouto\Param;

Param::get('name')
	->filters()
	->ifmatch('/\d/', 'Name cannot contain digits');

$error = Param::get('name')->process(['name'=>'M4ry'])->error;

var_dump($error);
```

```
string(26) "Name cannot contain digits"

```

Another example using regex modifiers:

```php
use FlSouto\Param;

Param::get('phone')
	->filters()
	->ifmatch('/[a-z]/i', 'Phone cannot contain letters');

$error = Param::get('phone')->process(['phone'=>'9829574K'])->error;

var_dump($error);
```

```
string(28) "Phone cannot contain letters"

```

***Notice***: This validator is only applied when the value is not empty:

```php
use FlSouto\Param;

Param::get('phone')
	->filters()
	->ifmatch('/[a-z]/i', 'Phone cannot contain letters');

$error = Param::get('phone')->process(['phone'=>''])->error;

var_dump($error);
```

```
NULL

```

If you want to make sure the value is not empty use the `required` filter before `ifmatch`.


### ifnot

Produces an error when the string DOES NOT MATCH a pattern:

```php
use FlSouto\Param;

Param::get('date')->filters()->ifnot('/^\d{4}-\d{2}-\d{2}$/','Date is expected to be in the format yyyy-mm-dd');
$error = Param::get('date')->process(['date'=>'10/12/1992'])->error;

var_dump($error);
```

```
string(47) "Date is expected to be in the format yyyy-mm-dd"

```

***Notice***: This validator is only applied when the value is not empty:

```php
use FlSouto\Param;

Param::get('login')->filters()->ifnot('\w','Login must contain at least one letter');
$error = Param::get('login')->process([])->error;

var_dump($error);
```

```
NULL

```

### maxlen

Makes sure the length of the string doesn't exceed a maximum:

```php
use FlSouto\Param;

Param::get('description')->filters()->maxlen(30, "Description must be less than %d characters long!");
$error = Param::get('description')
	->process(['description'=>str_repeat('lorem ipsum', 10)])
	->error;


var_dump($error);
```

```
string(49) "Description must be less than 30 characters long!"

```


### minlen

Makes sure the length of a string is at least certain characters long:

```php
use FlSouto\Param;

Param::get('description')->filters()->minlen(10, "Description must be at least %d characters long!");

$error = Param::get('description')->process(['description'=>'Test'])->error;

var_dump($error);
```

```
string(48) "Description must be at least 10 characters long!"

```

***Notice***: This validator is only applied when the value is not empty:

```php
use FlSouto\Param;

Param::get('description')->filters()->minlen(10, "Description must be at least %d characters long!");

$error = Param::get('description')->process(['description'=>''])->error;

var_dump($error);
```

```
NULL

```

### maxval

Makes sure an integer is not more than certain value:

```php
use FlSouto\Param;

Param::get('age')->filters()->maxval(150, "Age cannot be more than %d!");
$error = Param::get('age')->process(['age'=>200])->error;

var_dump($error);
```

```
string(28) "Age cannot be more than 150!"

```

### minval

Makes sure the integer, if provided, is at least larger than X:

```php
use FlSouto\Param;

Param::get('age')->filters()->minval(1, "Age cannot be less than %d!");
$error = Param::get('age')->process(['age'=>0])->error;

var_dump($error);
```

```
string(26) "Age cannot be less than 1!"

```


***Notice***: This validator is only applied when the value is not empty:

```php
use FlSouto\Param;

Param::get('age')->filters()->minval(1, "Age cannot be less than %d!");
$error = Param::get('age')->process(['age'=>null])->error;

var_dump($error);
```

```
NULL

```

### Chaining filters 

Last but not least, you can use method chaning for adding multiple filters.
The filters will be applied to the value in the same order they were added:

```php
<?php
require_once('vendor/autoload.php');
use FlSouto\Param;

Param::get('number')
	->filters()
		->strip('/[^\d]/')
		->required()
		->minlen(5)
		->maxlen(10);

$result = Param::get('number')->process([
	'number' => '203-40-10/80'
]);

var_dump($result->output);
```

In the example above the data will be filtered before the validation constraints and the result will be:

```
string(9) "203401080"

```
