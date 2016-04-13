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
		$container->k1 = 'v1';
		$this->assertCount(1, $container->getIterator());
		foreach($container as $k => $v){
			$this->assertEquals('k1', $k);
			$this->assertEquals('v1', $v);
		}
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
		$factory->addContainerToMerge(new Container([
			'myContainerKey' => 'myContainerValue',
		]));
		$container = $factory->create();

		$this->assertEquals($workingDir, $container->workingDirectory);
		$this->assertEquals('MyTest\NonExistingClass2', $container->class);
		$this->assertEquals([], $container->myArray);
		$this->assertEquals('myContainerValue', $container->myContainerKey);
		$this->assertEquals('someValue', $container->config['someVar']);
		$this->assertEquals('i am buggy?', $container->something['config']);
		$this->assertCount(2, $container->mergeMe);
		$this->assertCount(1, $container->doNotMergeMe);
		$this->assertCount(1, $container->doNotMergeMeRecursive);
		$this->assertCount(1, $container->doNotMergeMeRecursive['doNotMergeField']);
		$array = [
			'doNotMergeField' => [
				'anotherKey2' => 'anotherVal',
			],
		];
		$this->assertTrue($array === $container->doNotMergeMeRecursive, 'Array doNotMergeMeRecursive is not the same: ' . print_r($container->doNotMergeMeRecursive, TRUE));
	}

}