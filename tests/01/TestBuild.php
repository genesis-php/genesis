<?php

namespace MyTest;


use Genesis;
use Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 *
 * BuildTest
 */
class TestBuild extends Genesis\Build
{

	/**
	 * @var \ArrayObject
	 * @inject myService
	 */
	public $testService;

	/**
	 * @var \StdClass
	 * @inject myService2
	 */
	public $testService2;

	/**
	 * @var \ArrayObject
	 * @inject
	 */
	public $myService; // injected without definition


	/**
	 * My task description
	 * @return void
	 * @section mySection
	 */
	public function runInfo()
	{
		$this->logSection('This is Section with info.');
		$this->log('This is TestBuild.');
	}


	public function runShowArguments()
	{
		$this->log(json_encode($this->arguments));
	}


	public function runShowContainerValue()
	{
		$key = $this->arguments[1];
		$this->log(json_encode($this->container->getParameter($key)));
	}


	public function runShowServiceClass()
	{
		$key = $this->arguments[1];
		$this->log(get_class($this->container->getService($key)));
	}


	public function runShowAutowiredClass()
	{
		$key = $this->arguments[1];
		$this->log(get_class($this->$key));
	}


	public function runError()
	{
		$this->error("This is error.");
	}


	public function runThrowUnexpectedException()
	{
		throw new \RuntimeException("UnexpectedException message");
	}

}