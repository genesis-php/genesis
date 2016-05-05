<?php

namespace Genesis\Tests;


use Genesis;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */

class CliTest extends BaseTest
{

	public function testNonExistingTask()
	{
		// fails in Bootstrap
		$result = $this->execute('nonExistingTask');
		$this->assertEquals(255, $result['code']);
		$line = $result['output'][6];
		$this->assertContains("Task 'nonExistingTask' does not exists", $line);
	}


	public function testWrongBootstrapReturn()
	{
		$result = $this->execute('task', [
			'working-dir' => 'tests/04',
		]);
		$this->assertEquals(255, $result['code']);
		$line = $result['output'][6];
		$this->assertContains("Returned value from bootstrap.php must be instance of 'Genesis\\Container\\Container' or nothing (NULL)", $line);
	}


	public function testShowBootstrapContainer() // test, if returned Container from bootstrap is merged into Container
	{
		$result = $this->execute('showContainerValue myTestBootstrapKey');
		$this->assertEquals(0, $result['code']);
		$line = $result['output'][7];
		$this->assertContains('"val"', $line);
		$result = $this->execute('showServiceClass myService');
		$this->assertEquals(0, $result['code']);
		$line = $result['output'][7];
		$this->assertContains('ArrayObject', $line);
	}


	public function testExistingTask()
	{
		$result = $this->execute('info');
		$this->assertEquals(0, $result['code']);
		$line = $result['output'][6];
		$this->assertContains('Running [info]', $line);
	}


	public function testErrorTask()
	{
		$result = $this->execute('error');
		$this->assertEquals(255, $result['code']);
		$line = $result['output'][6];
		$this->assertContains('Running [error]', $line);
		$line = $result['output'][7];
		$this->assertContains('Exited with ERROR', $line);
	}


	public function testEmpty()
	{
		// executes runDefault()
		$result = $this->execute('');
		$this->assertEquals(0, $result['code']);
		$line = $result['output'][6];
		$this->assertContains('Running default', $line);
	}


	public function testSelfInitTask()
	{
		$dir = __DIR__ . '/self-init';
		$command = new Genesis\Commands\Filesystem\Directory();
		$command->create($dir);

		$result = $this->execute('self-init', [
			'working-dir' => 'tests/self-init',
		]);
		$this->assertEquals(0, $result['code']);
		$line = $result['output'][5];
		$this->assertContains("Build initialized in", $line);
		$line = $result['output'][6];
		$this->assertContains("You can start by typing", $line);

		$command->clean($dir);
		rmdir($dir);
	}


	public function testWrongWorkingDir()
	{
		$result = $this->execute('', [
			'working-dir' => 'tests/workingDirNonExisting',
		]);
		$this->assertEquals(255, $result['code']);
		$line = $result['output'][5];
		$this->assertContains("Working dir 'tests/workingDirNonExisting' does not exists", $line);

		$result = $this->execute('', [
			'working-dir' => '.',
		]);
		$this->assertEquals(255, $result['code']);
		$line = $result['output'][5];
		$this->assertRegexp("#Working dir '.+' is directory with Genesis#", $line);
	}


	public function testBootstrapFound()
	{
		$result = $this->execute('');
		$line = $result['output'][5];
		$this->assertContains('Info: Found bootstrap.php in working directory', $line);
	}


	public function testNonExistingBuild()
	{
		$result = $this->execute('task', [
			'working-dir' => 'tests/02',
		]);
		$this->assertEquals(255, $result['code']);
		$line = $result['output'][6];
		$this->assertContains("Build class 'MyTest\\NonExistingClass' was not found", $line);
	}


	public function testExistingBuild()
	{
		$result = $this->execute('info', [
			'working-dir' => 'tests/01',
		]);
		$this->assertEquals(0, $result['code']);
		$line = $result['output'][6];
		$this->assertContains('Running [info]', $line);
	}


	public function testOptionalConfigBuild()
	{
		$result = $this->execute('showContainerValue optionalConfigKey', [
			'working-dir' => 'tests/01',
			'config' => 'optionalConfig.neon',
		]);
		$this->assertEquals(0, $result['code']);
		$line = $result['output'][7];
		$this->assertContains('"optionalConfigVal"', $line);
	}


	public function testAutowiring()
	{
		$result = $this->execute('showAutowiredClass testService', [
			'working-dir' => 'tests/01',
			'config' => 'config.neon',
		]);
		$this->assertEquals(0, $result['code']);
		$line = $result['output'][7];
		$this->assertContains('ArrayObject', $line);

		$result = $this->execute('showAutowiredClass testService2', [
			'working-dir' => 'tests/01',
			'config' => 'config.neon',
		]);
		$this->assertEquals(0, $result['code']);
		$line = $result['output'][7];
		$this->assertContains('stdClass', $line);
	}


	public function testAutowiringFail()
	{
		$result = $this->execute('showAutowiredClass testService', [
			'working-dir' => 'tests/01',
			'config' => 'configAutowireFail.neon',
		]);
		$this->assertEquals(255, $result['code']);
		$line = $result['output'][7];
		$this->assertContains('Cannot found service \'myService\' to inject into', $line);
	}

}