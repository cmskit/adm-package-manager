# Templates

To make your scripts more readable it is recommended to separate functionality from presentation 
and to manage HTML code in separate Files, often called Templates. 
There are a bunch of Template-Engines whitch will do the translation of templates to native code out of the box. 
Unfortunately they do this on the fly (with some magic "caching" under the hood) whitch is way too slow. 
In addition, it is difficult to get generated snippets (instead of a whole HTML page) from a template.

To overcome this our template parser has to be called manually (eg. after changing something at the template). 
Scripts using the generated native code dosen't need to have a glue that the template even exists ;-)
The parser dosen't generate one translated "Page". 
Instead it searches for "HTML-blocks" whitch are translated to Functions returning only the portion of code found in this block 
(of course you can define the &lt;html> element itself as a block to get the whole page).

### Here's how it works:

The parser will search for Files ending to ".xhtml" and creates a list of all templates found. 
After selecting a template-path for parsing it will create a folder containing the translated native code witch you can include in your scripts.


Place a File ending with ".xhtml" anywhere you like. *The parent folder must be writable for the parser.* 

	<div class="TemplateContainer" rel="php" id="unique_container_name"></div>

1. The parser will look for HTML elements with the class "TemplateContainer", 
2. checks the attribute "data-type" and use the attribute "id" to generate a unique function name (prefixed with "render_").
3. Everything inside the container is parsed as template snippet.

Example: assume this is a file "wizardtpl.xhtml" containing this DIV (there are no tags in this example)

	<div class="TemplateContainer" data-type="php" id="menu">
	  <ul>
	    <li>one</li>
	    <li>two</li>
	    <li>three</li>
	  </ul>
	</div>

This will be translated to a file "wizardtpl/tpl.php" containing a php-class

	class wizardtpl
	{
	    public $arr = array();
	    public function render_menu ($_V)
	    {
		if(is_array($_V) && (array_keys($_V) !== range(0, count($_V) - 1))) {
		    foreach($_V as $k=>$v){$$k = $v;}
		}
		 $_P='
		  <ul><li>one</li>
		    <li>two</li>
		    <li>three</li>
		  </ul>';

		return  $_P;
	    }
	}




To call it simply 

* include the php
* create an object ( eg. *$tpl = new wizardtpl();* )
* get/output the HTML ( eg. *echo $tpl->render_menu($myArray);* )

## Handover data

To fill templates with variable data you can call the render_*xyz*-function with a variable

1. containing a string or number
2. a numeric array (eg. array('a', 'b', 'f'))
3. a associative array (whtch may contain arrays of course)

* In case 1 and 2 you have to use the variable name "$_V" within your template.
* In case 3 the main keys of your associative array will be mapped to variables, 
so array("bla"=>"blubb") will result in a variable "$bla" wth the value "blubb".  
*Please note that the key-names "$_V" and "$_P" should not be used in this case!*

## Tags

Of course it is possible to create special tags whitch are replaced by data sent tot he function (see the variable "$val" above).

The template parser uses slightly adapted portions of the [RainTPL](http://www.raintpl.com) parser to compile template code to native code.

Here are the tags you can use in your template *(and their use per language)*.


* **{$variable}** (php,js)
* **{$variable=abc}** (php)
* **{#class_variable#}** (php)
* **{if="condition"}{/if}** (php,js)
* **{loop="array"}{/loop}** (php)
* **{function="myFunc($value1,$value2,...)"}** (php,js)
* **{* comment *}** (php,js)
* **{noparse}{/noparse}** (php,js)
* **{$variable|modifiers}** (php,js)


### Variables

{$Variable_name}

Variables are the dynamic contents of the templates, you can assign them with assign() method. Variables are case_sensitive.

	My age is {$age}  // 30


#### Set variables
You can do simple operations such as + - * / % with numbers, string and other variables and also use = for assign a value to class scope (for later use)

	{$year=30}

My age is {$age+$year}  // 60

#### Modifiers

You can use functions with variables

	My name is {$name|substr:0,3}  // Rai

Use {$GLOBALS} to access all globals variable as _SESSION, _GET, _POST, _SERVER.

#### "Global" Variables

Access values previously stored in class scope

	{#parent_variable#}

## Conditions

	{if="condition"}{/if}

It's the equivalent of php/js if. You can use variable and php into the condition.

	{if condition="$age < 20"}
	    less than 20 years
	{elseif condition="$age < 30"}
	     less than 30 years
	{else}
	     30 or more
	{/if}

In certain situation it could be easier to use the ?: operator

	{$is_logged? 'Hello $name':'Not logged'}

## Loops

	{loop="array"}{/loop}

This tag allows you to loop an array, it's useful to print a list of articles, news or other repeating elements.

	{loop="user"}
	 <li>{$value.user_id} - {$value.name} - {$value.phone}</li>
	{/loop}


Between {loop} and {/loop} you can use special variables:

* {$key}, key of the array element.
* {$value}, value of the array.
* {$counter}, loop counter, it starts from 0. 

If you want starting it from 1 use {$counter+1}. 
If you do {$counter%2+1} each cycle will return 1 and 2.

 
## Function calls

	{function="myFunc"}

Call regular php functions and print the result.

	{function="pagination($selected_page)"}

You also can call methods inside the class like this. 

	{function="self::render_foo($array)"}

(Please note: $this->render_foo() does not pass the parser!)

## Comments

All between {* and *} is deleted in the compiled file. Use this tag to comment your templates.

	{* 'sup dude? I'm a comment *}

Of course you can also place your comments outside of the container ;-)

## Prevent parsing

	{noparse}{/noparse}

All the code between {noparse} {/noparse} won't be parsed

	Hey {noparse}how are you {$name}{/noparse}?

Output:
	Hey how are you {$name}?



