<?php

namespace Genesis\Tests;


use Genesis\Config\Container;
use Genesis\Config\ContainerFactory;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class ContainerTest extends BaseTest
{

	public function testSettersAndGetters()
	{
		$workingDir = __DIR__ . '/02';
		$factory = new ContainerFactory();
		$factory->setWorkingDirectory($workingDir);
		$this->assertEquals($workingDir, $factory->getWorkingDirectory());
		$factory->addConfig('config1.neon');
		$this->assertEquals(['config1.neon'], $factory->getConfigs());
		$this->assertEquals(NULL, $factory->getContainersToMerge());
		$factory->addContainerToMerge(new Container());
		$this->assertCount(1, $factory->getContainersToMerge());
		$this->assertTrue($factory->getContainersToMerge()[0] instanceof Container);
	}


	public function testIterator()
	{
		$container = new Container();
		$container->setParameter('k1', 'v1');
		$this->assertCount(1, $container->getIterator());
		foreach($container as $k => $v){
			$this->assertEquals('k1', $k);
			$this->assertEquals('v1', $v);
		}
	}


	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Direct getting is not supported
	 */
	public function testOverloadedGet()
	{
		$container = new Container();
		$a = $container->k1;
	}


	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Direct setting is not supported
	 */
	public function testOverloadedSet()
	{
		$container = new Container();
		$container->k1 = 'v1';
	}


	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage No config added
	 * @expectedExceptionCode NULL
	 */
	public function testNoConfigFiles()
	{
		$factory = new ContainerFactory();
		$factory->setWorkingDirectory(__DIR__ . '/02');
		$factory->create();
	}


	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage not found
	 * @expectedExceptionCode NULL
	 */
	public function testNonExistingConfig()
	{
		$factory = new ContainerFactory();
		$factory->setWorkingDirectory(__DIR__ . '/02');
		$factory->addConfig(__DIR__ . '/02/nonExistingConfig.neon');
		$factory->create();
	}


	/**
	 * @expectedException \Nette\Neon\Exception
	 */
	public function testInvalidNeon()
	{
		$factory = new ContainerFactory();
		$factory->setWorkingDirectory(__DIR__ . '/02');
		$factory->addConfig(__DIR__ . '/02/invalidConfig.neon');
		$factory->create();
	}


	public function testValues()
	{
		$workingDir = __DIR__ . '/02';
		$factory = new ContainerFactory();
		$factory->setWorkingDirectory($workingDir);
		$factory->addConfig(__DIR__ . '/02/config.neon');
		$factory->addConfig(__DIR__ . '/02/config2.neon');
		$cont = new Container();
		$cont->setParameters([
			'myContainerKey2' => 'myContainerValue2'
		]);
		$cont->setParameter('myContainerKey', 'myContainerValue');
		$factory->addContainerToMerge($cont);
		$container = $factory->create();

		$this->assertEquals($workingDir, $container->getParameter('workingDirectory'));
		$this->assertEquals('MyTest\NonExistingClass2', $container->getClass());
		$this->assertEquals([], $container->getParameter('myArray'));
		$this->assertEquals('myContainerValue', $container->getParameter('myContainerKey'));
		$this->assertEquals('someValue', $container->getParameter('config')['someVar']);
		$this->assertEquals('i am buggy?', $container->getParameter('something')['config']);
		$this->assertCount(2, $container->getParameter('mergeMe'));
		$this->assertCount(1, $container->getParameter('doNotMergeMe'));
		$this->assertCount(1, $container->getParameter('doNotMergeMeRecursive'));
		$this->assertCount(1, $container->getParameter('doNotMergeMeRecursive')['doNotMergeField']);
		$array = [
			'doNotMergeField' => [
				'anotherKey2' => 'anotherVal',
			],
		];
		$this->assertTrue($array === $container->getParameter('doNotMergeMeRecursive'), 'Array doNotMergeMeRecursive is not the same: ' . print_r($container->getParameter('doNotMergeMeRecursive'), TRUE));
		$this->assertEquals('includedVal', $container->getParameter('includedKey'));

		// nested variables
		$this->assertEquals('val', $container->getParameter('nestedValue'));
	}


	public function testServices()
	{
		$workingDir = __DIR__ . '/02';
		$factory = new ContainerFactory();
		$factory->setWorkingDirectory($workingDir);
		$factory->addConfig(__DIR__ . '/02/config.neon');
		$factory->addConfig(__DIR__ . '/02/config2.neon');
		$cont = new Container();
		$cont->addService('myService', new \StdClass);
		$cont->setParameter('myContainerKey', 'myContainerValue');
		$factory->addContainerToMerge($cont);
		$container = $factory->create();

		$this->assertTrue($container->hasService('myservice'));
		$this->assertFalse($container->hasService('myArrayWhichDoesNotExists'));
		$this->assertInstanceOf('DateTime', $container->getService('myservice'));
		$this->assertEquals(date('Y-m-d', strtotime('+ 1 day')), $container->getService('myservice')->format('Y-m-d'));
	}


	/**
	 * @expectedException \Exception
	 */
	public function testGetParameterFail()
	{
		$container = new Container();
		$container->getParameter('unknown');
	}


	/**
	 * @expectedException \Exception
	 */
	public function testGetServiceFail()
	{
		$container = new Container();
		$container->getService('unknown');
	}


	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Class Datetime does not have method setNonExistingSetter()
	 */
	public function testNonExistingSetupMethod()
	{
		$workingDir = __DIR__ . '/02';
		$factory = new ContainerFactory();
		$factory->setWorkingDirectory($workingDir);
		$factory->addConfig(__DIR__ . '/02/config3.neon');
		$factory->create();
	}


	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Since version 2.0 are supported main only these sections
	 */
	public function testOld1xConfig()
	{
		$workingDir = __DIR__ . '/02';
		$factory = new ContainerFactory();
		$factory->setWorkingDirectory($workingDir);
		$factory->addConfig(__DIR__ . '/02/config-old1x.neon');
		$factory->create();
	}

}