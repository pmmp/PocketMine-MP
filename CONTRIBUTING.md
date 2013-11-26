![](http://www.pocketmine.net/favicon.png)

# PocketMine-MP Contribution Guidelines

Before contributing to PocketMine-MP, please read this.


## I've a question
* For questions, please refer to the _#pocketmine_ or _#mcpedevs_ IRC channel on Freenode. There is a [WebIRC](http://webchat.freenode.net?channels=pockdetmine,mcpedevs&uio=d4) if you want.
* You can ask directly to _[@PocketMine](https://twitter.com/PocketMine)_ in Twitter, but don't expect an inmediate reply.

## I want to create an issue
* First, use the [Issue Search](https://github.com/PocketMine/PocketMine-MP/search?ref=cmdform&type=Issues) to check if anyone has reported it.
* Is your issue related to a Plugin? If so, please contact their original author instead of reporting it here.
 * And no, we won't update a Plugin because you need it.
* When reporting, give as much info as you can, and if the Issue is a crash, give the Crash Dump.
* Issues should be written in English.

## I want to contribute code
* Use the [Pull Request](https://github.com/PocketMine/PocketMine-MP/pull/new) system, your request will be checked and discussed.
* __Create a single branch for that pull request__
* If you want to be part of PocketMine-MP, we will ask you to.
* Code using the syntax as in PocketMine-MP. See below for an example.
* The code must be clear and written in English, comments included.


__Thanks for contributing to PocketMine-MP!__




#### Code syntax

It is mainly [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) with a few exceptions.
* Opening braces MUST go on the same line.
* `else if` MUST be written as `elseif`. _(It is in PSR-2, but using a SHOULD)_
* Control structure keywords or opening braces MUST NOT have one space after them.
* Code MUST use tabs for indenting.
* Long arrays MAY be split across multiple lines, where each subsequent line is indented once. 
* Files MUST use only the `<?php` tag.
* Files MUST NOT have an ending `?>` tag.
* Code MUST NOT use namespaces. _(This restriction will be lifted on the Alpha_1.4 code)_
* Strings SHOULD use the double quote `"` except when the single quote is required.
* Arrays SHOULD be declared using `array()`, not the `[]` shortcut.
* Argument lists MAY NOT be split across multiple lines, except long arrays.

```php
<?php 

class ExampleClass{
	const EXAMPLE_CLASS_CONSTANT = 1;
	public $examplePublicVariable = "defaultValue";
	private $examplePrivateVariable;
	
	public function __construct($firstArgument, &$secondArgument = null){
		if($firstArgument === "exampleValue"){ //Remember to use === instead == when possible
			//do things
		}elseif($firstArgument === "otherValue"){
			$secondArgument = function(){
				return array(
					0 => "value1",
					1 => "value2",
					2 => "value3",
					3 => "value4",
					4 => "value5",
					5 => "value6",
				);
			}
		}
	}

}
```