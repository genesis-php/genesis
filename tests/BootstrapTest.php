<?php


namespace Genesis\Tests;

use Genesis\Bootstrap;
use Genesis\InputArgs;
use Genesis\TerminateException;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class BootstrapTest extends BaseTest
{

	public function testNonExistingTask()
	{
		$inputArgs = $this->createInputArgs(['nonExistingTask'], ['working-dir' => '01']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(255, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals("Task 'nonExistingTask' does not exists.", $lines[1]);
	}


	public function testWrongBootstrapReturn()
	{
		$inputArgs = $this->createInputArgs(['task'], ['working-dir' => '04']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(255, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals("Returned value from bootstrap.php must be instance of 'Genesis\\Container\\Container' or nothing (NULL).", $lines[1]);
	}


	public function testShowBootstrapContainer() // test, if returned Container from bootstrap is merged into Container
	{
		$inputArgs = $this->createInputArgs(['showContainerValue', 'myTestBootstrapKey'], ['working-dir' => '01']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(0, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals('"val"', $lines[2]);

		$inputArgs = $this->createInputArgs(['showServiceClass', 'myService'], ['working-dir' => '01']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(0, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals('ArrayObject', $lines[2]);
	}


	public function testExistingTask()
	{
		$inputArgs = $this->createInputArgs(['info'], ['working-dir' => '01']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(0, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals('Running [info]', $lines[1]);
	}


	public function testEmpty()
	{
		// executes runDefault()
		$inputArgs = $this->createInputArgs([], ['working-dir' => '01']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(0, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals('Running default', $lines[1]);
	}


	public function testUnexpectedException()
	{
		$inputArgs = $this->createInputArgs(['throwUnexpectedException'], ['working-dir' => '01']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(255, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals('Exited with ERROR:', $lines[2]);
		$this->assertEquals('UnexpectedException message', $lines[3]);
	}


	public function testSelfInitTask()
	{
		$dir = __DIR__ . '/self-init';
		$command = new \Genesis\Commands\Filesystem\Directory();
		$command->create($dir);

		$inputArgs = $this->createInputArgs(['self-init'], ['working-dir' => 'self-init']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(0, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertContains('Build initialized in', $lines[0]);
		$this->assertContains('You can start by typing', $lines[1]);

		$command->clean($dir);
		rmdir($dir);
	}


	public function testWrongWorkingDir()
	{
		$inputArgs = $this->createInputArgs([], ['working-dir' => 'workingDirNonExisting']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(255, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals("Working dir 'workingDirNonExisting' does not exists.", $lines[0]);

		$inputArgs = $this->createInputArgs([], ['working-dir' => '../']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(255, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertRegexp("#Working dir '.+' is directory with Genesis#", $lines[0]);
	}


	public function testBootstrapFound()
	{
		$inputArgs = $this->createInputArgs([], ['working-dir' => '01']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(0, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals('Info: Found bootstrap.php in working directory.', $lines[0]);
	}


	public function testNonExistingBuild()
	{
		$inputArgs = $this->createInputArgs([], ['working-dir' => '02']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(255, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals("Build class 'MyTest\\NonExistingClass' was not found.", $lines[1]);
	}


	public function testExistingBuild()
	{
		$inputArgs = $this->createInputArgs(['info'], ['working-dir' => '01']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(0, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals("Running [info]", $lines[1]);
	}


	public function testOptionalConfigBuild()
	{
		$inputArgs = $this->createInputArgs(['showContainerValue', 'optionalConfigKey'], ['working-dir' => '01', 'config' => 'optionalConfig.neon']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(0, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals('"optionalConfigVal"', $lines[2]);
	}


	public function testAutowiring()
	{
		$inputArgs = $this->createInputArgs(['showAutowiredClass', 'testService'], ['working-dir' => '01', 'config' => 'config.neon']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(0, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals('ArrayObject', $lines[2]);

		$inputArgs = $this->createInputArgs(['showAutowiredClass', 'testService2'], ['working-dir' => '01', 'config' => 'config.neon']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(0, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertEquals('stdClass', $lines[2]);
	}


	public function testAutowiringFail()
	{
		$inputArgs = $this->createInputArgs(['showAutowiredClass', 'testService'], ['working-dir' => '01', 'config' => 'configAutowireFail.neon']);
		$bootstrap = new Bootstrap();
		ob_start();
		try {
			$bootstrap->run($inputArgs);
		}catch(TerminateException $e){
			$this->assertEquals(255, $e->getCode());
		}
		$lines = explode("\n", ob_get_clean());
		$this->assertContains('Cannot found service \'myService\' to inject into', $lines[2]);
	}


	private function createInputArgs(array $args = [], array $options = [])
	{
		$inputArgs = new InputArgs();
		$inputArgs->setArguments($args);
		$inputArgs->setOptions($options + [
			'colors' => 0, // if colored, equality test does not work
		]);
		return $inputArgs;
	}

}