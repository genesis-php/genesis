<?php


namespace Genesis\Tests;


use Genesis\Commands;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 *
 * CommandsTest
 */
class CommandsTest extends BaseTest
{

	public function setUp()
	{
		parent::setUp();
		$dir = __DIR__ . '/03/output';
		if(!is_dir($dir)){
			mkdir($dir);
		}
	}


	/** Common */

	public function testExec()
	{
		ob_start();
		$exec = new Commands\Exec();
		$result = $exec->execute('echo "testexec"');
		$output = $result->getOutput();
		$line = $output[0];
		$this->assertEquals('testexec', $line);
		ob_end_clean();
	}


	public function testGitExecute()
	{
		ob_start();
		$git = new Commands\Git();
		$result = $git->execute('--version');
		$output = $result->getOutput();
		$line = $output[0];
		$this->assertContains('git version ', $line);
		ob_end_clean();
	}


	public function testHelp()
	{
		ob_start();
		$help = new Commands\Help();
		$help->execute([]);
		$this->assertContains('Available tasks:', ob_get_clean());
	}


	public function testNodeJs()
	{
		$workingDir = __DIR__ . '/03';
		$outputDir = __DIR__ . '/03/output';
		$command = new Commands\Filesystem\Directory();
		$command->clean($outputDir);

		$command = new Commands\Filesystem\File();
		$command->copy($workingDir . '/package.json', $outputDir . '/package.json');

		ob_start();
		$command = new Commands\NodeJs();
		$command->installPackages($outputDir, [
			'silent' => TRUE,
		]);
		$this->assertFileExists($outputDir . '/node_modules');
		$this->assertNotContains('Error', ob_get_clean());

		$command = new Commands\Filesystem\Directory();
		$command->clean($outputDir);
	}


	public function testPhpUnit()
	{
		$workingDir = __DIR__ . '/03';
		ob_start();
		$command = new Commands\PHPUnit();
		$command->execute($workingDir, $workingDir, [
			'executable' => 'phpunit',
		]);
		$this->assertNotContains('Error', ob_get_clean());
	}


	public function testSelfInit()
	{
		$dir = __DIR__ . '/03/output';
		$command = new Commands\Filesystem\Directory();
		$command->clean($dir);

		$selfInit = new Commands\SelfInit();
		$selfInit->setDistDirectory(__DIR__ . '/../build-dist');
		ob_start();
		$selfInit->execute($dir, 'build');
		ob_end_clean();
		$res = glob($dir . "/build/*");
		$this->assertCount(4, $res);
	}


	/** Filesystem */

	public function testFs()
	{
		$dir = __DIR__ . '/03/output';
		$command = new Commands\Filesystem\Directory();
		$command->clean($dir);
		$res = glob($dir . "/*");
		$this->assertCount(0, $res);

		$command = new Commands\Filesystem\Directory();
		$command->create($dir . '/test-dir', 777);
		$this->assertFileExists($dir . '/test-dir');

		$iterator = $command->read($dir);
		$this->assertCount(1, $iterator);
		$this->assertEquals('test-dir', $iterator->current()->getFileName());

		$command = new Commands\Filesystem\File();
		$command->create($dir . '/testfile.json', '{}');
		$this->assertFileExists($dir . '/testfile.json');
		$this->assertEquals('{}', file_get_contents($dir . '/testfile.json'));

		$command = new Commands\Filesystem\File();
		$command->copy($dir . '/testfile.json', $dir . '/testfile2.json');
		$this->assertFileExists($dir . '/testfile.json'); // old file still exists
		$this->assertFileExists($dir . '/testfile2.json');
		$this->assertEquals('{}', file_get_contents($dir . '/testfile2.json'));

		$command = new Commands\Filesystem\Symlink();
		$command->create($dir . '/testfile.json', $dir . '/sym-testfile.json');
		$this->assertFileExists($dir . '/sym-testfile.json');
		$this->assertTrue(is_link($dir . '/sym-testfile.json'));

		ob_start();
		$command->createRelative($dir, 'testfile.json', 'sym-rel-testfile.json');
		ob_end_clean();
		$this->assertFileExists($dir . '/sym-rel-testfile.json');
		$this->assertTrue(is_link($dir . '/sym-rel-testfile.json'));

		$command = new Commands\Filesystem\Directory();
		$command->clean($dir);
		$res = glob($dir . "/*");
		$this->assertCount(0, $res);
	}


	/** Filesystem */

	public function testAssets()
	{
		$dir = __DIR__ . '/03';
		$outputDir = __DIR__ . '/03/output';
		$command = new Commands\Filesystem\Directory();
		$command->clean($outputDir);
		$res = glob($outputDir . "/*");
		$this->assertCount(0, $res);

		$command = new Commands\Filesystem\File();
		$command->copy($dir . '/package.json', $outputDir . '/package.json');
		$command->copy($dir . '/gulpfile.js', $outputDir . '/gulpfile.js');
		$command->copy($dir . '/test.less', $outputDir . '/test.less');

		ob_start();
		$command = new Commands\NodeJs();
		$command->installPackages($outputDir, [
			'silent' => TRUE,
		]);
		ob_end_clean();

		ob_start();
		$command = new Commands\Assets\Gulp();
		$command->run($outputDir, 'test');
		$contents = ob_get_clean();
		$this->assertContains("Starting 'test'", $contents);
		$this->assertContains("Running test in Gulp.", $contents);

		ob_start();
		$command = new Commands\Assets\Less();
		$command->compile([
			$outputDir . '/test.less' => $outputDir . '/test.css',
		]);
		ob_end_clean();
		$this->assertFileExists($outputDir . '/test.css');

		$command = new Commands\Filesystem\Directory();
		$command->clean($outputDir);
	}


	/** Tests */

	/**
	 * @expectedException \ErrorException
	 * @expectedExceptionMessage Required program "phppp" is not installed
	 * @expectedExceptionCode NULL
	 */
	public function testTestProgramsFail()
	{
		$command = new Commands\Test\Programs();
		$command->execute([
			'phppp' => 'sudo apt-get install php5-cli',
		]);
	}


	public function testTestPrograms()
	{
		ob_start();
		$command = new Commands\Test\Programs();
		$command->execute([
			'php' => 'sudo apt-get install php5-cli',
		]);
		$this->assertNotContains('Error', ob_get_clean());
	}


	public function testTestPhp()
	{
		ob_start();
		$command = new Commands\Test\Php();
		$command->execute([
			'settings' => [
				'register_globals' => FALSE,
			], 'extensions' => [
				'mysql',
			],
		]);
		$this->assertNotContains('Error', ob_get_clean());
	}


	/**
	 * @expectedException \ErrorException
	 * @expectedExceptionMessage Node.JS is not current. Version v299.10.10 required, but
	 * @expectedExceptionCode NULL
	 */
	public function testTestNodejsFail()
	{
		ob_start();
		try {
			$command = new Commands\Test\NodeJs();
			$command->execute('v299.10.10');
		} catch (\Exception $e) {
			ob_end_clean();
			throw $e;
		}
		$this->assertNotContains('Error', ob_get_clean());
	}


	public function testTestNodejs()
	{
		ob_start();
		$command = new Commands\Test\NodeJs();
		$command->execute('v0.10.10');
		$this->assertNotContains('Error', ob_get_clean());
	}

}