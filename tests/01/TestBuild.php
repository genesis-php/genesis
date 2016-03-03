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
		$this->log(json_encode($this->container->$key));
	}


	public function runError()
	{
		$this->error("This is error.");
	}

}