<?php


namespace Genesis\Tests;


use Genesis\Config\Container;
use MyTest\TestBuild;

require_once __DIR__ . '/01/TestBuild.php';


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 *
 * BuildTest
 */
class BuildTest extends BaseTest
{

	public function testDefault()
	{
		// may not throw exception
		ob_start();
		$container = new Container();
		$build = new TestBuild($container);
		$build->runDefault();
		ob_end_clean();
	}


	public function testTask()
	{
		// may not throw exception
		ob_start();
		$container = new Container();
		$build = new TestBuild($container);
		$build->runInfo();
		ob_end_clean();
	}


	/**
	 * @expectedException \ErrorException
	 * @expectedExceptionMessage This is error
	 * @expectedExceptionCode NULL
	 */
	public function testError()
	{
		$container = new Container();
		$build = new TestBuild($container);
		$build->runError();
	}


	public function testArguments()
	{
		ob_start();
		$container = new Container();
		$build = new TestBuild($container, ['myArgument', 'myArgument2']);
		$build->runShowArguments();
		$output = ob_get_clean();
		$this->assertContains('["myArgument","myArgument2"]', $output);
	}

}