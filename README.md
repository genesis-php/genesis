[Genesis](https://github.com/genesis-php/genesis)
===================================
[![Latest Stable Version](https://poser.pugx.org/genesis-php/genesis/v/stable)](https://github.com/genesis-php/genesis/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/genesis-php/genesis/blob/master/license.md)

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

<h3>Common</h3>
- [Exec](https://github.com/genesis-php/genesis/blob/master/commands/Exec.php)
	- executes shell command
	- returns an [ExecResult](https://github.com/genesis-php/genesis/blob/master/commands/ExecResult.php) including return code and output
- [Git](https://github.com/genesis-php/genesis/blob/master/commands/Git.php)
	- executes Git command & can clone any repository
	- returns an [ExecResult](https://github.com/genesis-php/genesis/blob/master/commands/ExecResult.php) including return code and output
- [Help](https://github.com/genesis-php/genesis/blob/master/commands/Help.php)
	- prints list of available tasks to output
	- list is given in simple array
- [NodeJs](https://github.com/genesis-php/genesis/blob/master/commands/NodeJs.php)
	- can install packages (npm install) in any directory
- [PhpUnit](https://github.com/genesis-php/genesis/blob/master/commands/PhpUnit.php)
	- can run PHPUnit tests in any directory & provides some setup

<h3>Assets</h3>
- [Assets\Gulp](https://github.com/genesis-php/genesis/blob/master/commands/assets/Gulp.php)
	- Running gulp, usually to build frontend
	- You can setup gulpfile location
- [Assets\Less](https://github.com/genesis-php/genesis/blob/master/commands/assets/Less.php)
	- if you want only compile LESS files on build, you can use this command
	- using NodeJs lessc tool

<h3>Filesystem</h3>
- [Filesystem\Directory](https://github.com/genesis-php/genesis/blob/master/commands/filesystem/Directory.php)
	- provides set of methods for manipulating with directories
	- creating, cleaning (purging), reading
- [Filesystem\File](https://github.com/genesis-php/genesis/blob/master/commands/filesystem/File.php)
	- provides set of methods for manipulating with files
	- creating, copying
- [Filesystem\Symlink](https://github.com/genesis-php/genesis/blob/master/commands/filesystem/Symlink.php)
	- creating symlinks

<h3>Test</h3>
This set of commands may be useful, if you want to check system some system requirements,
usually at the beginning of the build.
- [Test\NodeJs](https://github.com/genesis-php/genesis/blob/master/commands/test/NodeJs.php)
	- tests NodeJs version
- [Test\Php](https://github.com/genesis-php/genesis/blob/master/commands/test/Php.php)
	- tests PHP ini settings
	- tests if desired extension present
- [Test\NodeJs](https://github.com/genesis-php/genesis/blob/master/commands/test/Programs.php)
	- tests if desired programs is installed (on UNIX)


Integration
-------------
An bootstrap.php in build directory can return instance of <code>Genesis\Config\Container</code>
which will be merged into Container created from config.neon