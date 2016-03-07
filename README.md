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
Initialize build directory in your project:
(path to your vendor directory may differ!)<br>
<code>
../vendor/genesis-php/genesis/genesis <b>self-init</b>
</code>


An 'build' directory will be created, with these files:
- <b>Build.php</b>
	- PHP class with run<b>Xyz</b>() methods
		- every public method starting with 'run' is an "Task"
		- in task you can run any code you want
	- [for example](https://github.com/genesis-php/genesis/blob/master/build-dist/Build.php)
- <b>config.neon</b>
	- config in [NEON](http://ne-on.org) format (very similar to YAML)
	- define build class and some variables, you need
	[for example](https://github.com/genesis-php/genesis/blob/master/build-dist/config.neon)
- <b>bootstrap.php</b> (optional)
	- to load Build.php class
	- [for example](https://github.com/genesis-php/genesis/blob/master/build-dist/bootstrap.php)
- <b>build</b> (optional)
	- shell script which is only shortcut into vendor directory with genesis

[Look at the skeleton, how can build directory looks like.](https://github.com/genesis-php/genesis/tree/master/build-dist)


Run it by (path to your vendor directory may differ!):<br>
<code>
../vendor/genesis-php/genesis/genesis <b>mytask</b>
</code>

OR via shortcut (you may need to edit path to vendor dir in file 'build/build'):<br>
<code>
build/build <b>mytask</b>
</code>



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