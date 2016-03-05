[Genesis](https://github.com/genesis-php/genesis)
===================================
Genesis is lightweight, smart and easy to use CLI tool, for building (mainly) PHP applications.
Usage is similar to Phing, but Genesis is much easier.
For configuration is used an .neon file, which is very similar to YAML.

Installation
------------
Preferred installation is with [Composer](https://doc.nette.org/composer).

<code>
composer require genesis-php/genesis
</code>


Quick example
---------------
Look at the [example](https://github.com/genesis-php/example) which gives you quick introduction.<br>
Don't worry, it will take only minute.


Getting started
---------------
Create directory with build in your project, for example 'build'.

In build directory create:
- <b>Build.php</b>
	- PHP class with run<b>Xyz</b>() methods
		- every public method starting with 'run' is an "Task"
		- in task you can run any code you want
	- [for example](https://github.com/genesis-php/example/blob/master/build-simple/TestBuild.php)
- <b>config.neon</b>
	- config in [NEON](http://ne-on.org) format (very similar to YAML)
	- define build class and some variables, you need
	[for example](https://github.com/genesis-php/example/blob/master/build-simple/config.neon)
- <b>bootstrap.php</b>
	- to load Build.php class
	- [for example](https://github.com/genesis-php/example/blob/master/build-simple/bootstrap.php)

[Look at the example, how can build directory looks like.](https://github.com/genesis-php/example/tree/master/build-simple)


Run it by (path to your vendor directory may differ!):<br>
<code>
../vendor/genesis-php/genesis/genesis <b>mytask</b>
</code>

To run it simplier, you can make an [shortcut](https://github.com/genesis-php/example/blob/master/build/build).


CLI
---------------
You can use any working directory with parameter "--working-dir":

<code>
../vendor/genesis-php/genesis/genesis <b>--working-dir /var/www/myproject</b> mytask
</code>


All arguments are passed to build:

<code>
../vendor/genesis-php/genesis/genesis <b>mytask</b> <b>foo</b>
</code>


Arguments <b>mytask</b> and <b>foo</b> will be available in your build class (property $arguments)


Commands
---------------
Commands are intended to use them in Tasks.
In namespace Genesis\Commands are default commands and of course you can create and use your own commands.



Integration
-------------
An bootstrap.php in build directory can return instance of <code>Genesis\Config\Container</code>
which will be merged into Container created from config.neon