<?php

namespace Genesis\Tests;


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

}