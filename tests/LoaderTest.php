<?php


namespace Genesis\Tests;

use Genesis\Loader;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class LoaderTest extends BaseTest
{

	/**
	 * @expectedException \RuntimeException
	 */
	public function testFail()
	{
		new \Genesis\Config\Containerr();
	}

	public function testSuccess()
	{
		$loader = new Loader();
		$loader->register();
		$container = new \Genesis\Config\Container();
		$this->assertInstanceOf('Genesis\Config\Container', $container);
	}

}