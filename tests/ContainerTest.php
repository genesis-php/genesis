<?php

namespace Genesis\Tests;


use Genesis\Config\ContainerFactory;
use Nette\Neon\Neon;

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
		$container = $factory->create();

		$this->assertEquals($workingDir, $container->workingDirectory);
		$this->assertEquals('MyTest\NonExistingClass2', $container->class);
		$this->assertEquals([], $container->myArray);
	}

}