<?php


namespace Genesis\Tests;


use Genesis\Commands;
use Genesis\ErrorException;

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
		$exec->setCommand('echo "testexec"');
		$result = $exec->execute();
		$output = $result->getOutput();
		$line = $output[0];
		$this->assertEquals('testexec', $line);
		ob_end_clean();
	}

	/**
	 * @expectedException \Genesis\InvalidArgumentException
	 * @expectedExceptionMessage Git executable cannot be empty.
	 */
	public function testGitExecutableFail()
	{
		$git = new Commands\Git();
		$git->setGitExecutable(NULL);
		$this->assertSame(NULL, $git->getGitExecutable());
	}


	public function testGit()
	{
		$git = new Commands\Git();
		$git->setGitExecutable('git');
		$this->assertSame('git', $git->getGitExecutable());
		$git->setCommand('clone abc');
		$this->assertSame('clone abc', $git->getCommand());
	}


	public function testGitExecute()
	{
		ob_start();
		$git = new Commands\Git();
		$git->setCommand('--version');
		$result = $git->execute();
		$output = $result->getOutput();
		$line = $output[0];
		$this->assertContains('git version ', $line);
		ob_end_clean();
	}


	public function testHelp()
	{
		ob_start();
		$help = new Commands\Help();
		$help->addSection('mySection');
		$help->addSection('mySection2');
		$help->setSectionTasks('mySection', ['myTask' => 'myTaskDesc']);
		$help->execute();
		$contents = ob_get_clean();
		$this->assertContains('Available tasks:', $contents);
		$this->assertContains('mySection', $contents);
		$this->assertContains('mySection2', $contents);
		$this->assertContains('myTask', $contents);
		$this->assertContains('myTaskDesc', $contents);
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
		$command = new Commands\NodeJs\PackageInstaller();
		$command->setDirectory($outputDir);
		$command->setOptions([
			'silent' => TRUE,
		]);
		$command->execute();
		$this->assertFileExists($outputDir . '/node_modules');
		$this->assertNotContains('Error', ob_get_clean());

		$command = new Commands\Filesystem\Directory();
		$command->clean($outputDir);
	}


	public function testPhpUnit()
	{
		$workingDir = __DIR__ . '/03';
		ob_start();
		$command = new Commands\PhpUnit();
		$command->setWorkingDir($workingDir);
		$this->assertSame($workingDir, $command->getWorkingDir());
		$command->setTarget($workingDir);
		$options = [
			'executable' => '../../vendor/bin/phpunit',
		];
		$command->setOptions($options);
		$this->assertSame($options, $command->getOptions());
		$command->execute();
		$this->assertNotContains('Error', ob_get_clean());
	}


	public function testSelfInit()
	{
		$dir = __DIR__ . '/03/output';
		$distDirectory = __DIR__ . '/../build-dist';
		$command = new Commands\Filesystem\Directory();
		$command->clean($dir);

		$selfInit = new Commands\SelfInit();
		$selfInit->setDistDirectory($distDirectory);
		$this->assertSame($distDirectory, $selfInit->getDistDirectory());
		$selfInit->setWorkingDirectory($dir);
		$this->assertSame($dir, $selfInit->getWorkingDirectory());
		$selfInit->setDirname('myBuildDir');
		$this->assertSame('myBuildDir', $selfInit->getDirname());
		ob_start();
		$selfInit->execute();
		ob_end_clean();
		$res = glob($dir . "/myBuildDir/*");
		$this->assertCount(4, $res);
	}


	/**
	 * @expectedException \Genesis\ErrorException
	 * @expectedExceptionMessageRegExp /Directory 'build-dist' in working directory '[A-Za-z0-9\/\._-]+' already exists\./
	 */
	public function testSelfInitError()
	{
		$distDirectory = __DIR__ . '/../build-dist';
		$workingDirectory = __DIR__ . '/../';
		$selfInit = new Commands\SelfInit();
		$selfInit->setDistDirectory($distDirectory);
		$selfInit->setWorkingDirectory($workingDirectory);
		$selfInit->setDirname('build-dist');
		$selfInit->execute();
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

		$command = new Commands\Filesystem\File();
		$command->delete($dir . '/testfile.json');
		$this->assertFileNotExists($dir . '/testfile.json');

		$command = new Commands\Filesystem\Directory();
		$command->clean($dir);
		$res = glob($dir . "/*");
		$this->assertCount(0, $res);

		// filesystem class
		$command = new Commands\Filesystem\Filesystem();
		$command->addDirectoriesToCreate([
			$dir . '/testfs1' => 777,
		]);
		$command->addDirectoriesToCreate([
			$dir . '/testfs2' => 777,
		]);
		$command->addFilesToCopy([
			$dir . '/testfs2/test.js' => $dir . '/../gulpfile.js',
		]);
		$command->addSymlinksRelativeToCreate([
			'symlink' => '../'
		], $dir);
		ob_start();
		$command->execute();
		ob_end_clean();
		$res = glob($dir . "/*");
		$this->assertCount(3, $res);
		$this->assertTrue(is_dir($dir . '/testfs1'));
		$this->assertTrue(is_dir($dir . '/testfs2'));
		$this->assertFileExists($dir . '/testfs2/test.js');
		$this->assertTrue(is_link($dir . '/symlink'));
		$command = new Commands\Filesystem\Filesystem();
		$command->addDirectoriesToClean([
			$dir,
		]);
		ob_start();
		$command->execute();
		ob_end_clean();
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
		$command = new Commands\NodeJs\PackageInstaller();
		$command->setDirectory($outputDir);
		$command->setOptions([
			'silent' => TRUE,
		]);
		$command->execute();
		ob_end_clean();

		ob_start();
		$command = new Commands\Assets\Gulp();
		$command->setGulpfile('gulpfile.js');
		$this->assertSame('gulpfile.js', $command->getGulpfile());
		$command->setDirectory($outputDir);
		$this->assertSame($outputDir, $command->getDirectory());
		$command->execute('test');
		$contents = ob_get_clean();
		$this->assertContains("Starting 'test'", $contents);
		$this->assertContains("Running test in Gulp.", $contents);

		ob_start();
		$command = new Commands\Assets\Less();
		$command->setExecutable('lessc');
		$this->assertSame('lessc', $command->getExecutable());
		$files = [
			$outputDir . '/test.less' => $outputDir . '/test.css',
		];
		$command->setFiles($files);
		$this->assertSame($files, $command->getFiles());
		$command->setOptions([]);
		$this->assertSame([], $command->getOptions());
		$command->execute();
		ob_end_clean();
		$this->assertFileExists($outputDir . '/test.css');

		$command = new Commands\Filesystem\Directory();
		$command->clean($outputDir);
	}


	/** Tests */

	/**
	 * @expectedException \Genesis\ErrorException
	 * @expectedExceptionMessage Required program "phppp" is not installed
	 * @expectedExceptionCode NULL
	 */
	public function testTestProgramsFail()
	{
		$command = new Commands\Test\Programs();
		$command->setRequiredPrograms([
			'phppp' => 'sudo apt-get install php5-cli',
		]);
		$command->execute();
	}


	public function testTestPrograms()
	{
		ob_start();
		$command = new Commands\Test\Programs();
		$command->setRequiredPrograms([
			'php' => 'sudo apt-get install php5-cli',
		]);
		$command->execute();
		$this->assertNotContains('Error', ob_get_clean());
	}


	public function testTestPhp()
	{
		ob_start();
		$command = new Commands\Test\Php();
		$command->setSettings([
			'settings' => [
				'register_globals' => FALSE,
			], 'extensions' => [
				'PDO',
			],
		]);
		$command->execute();
		$this->assertNotContains('Error', ob_get_clean());
	}


	/**
	 * @expectedException \Genesis\ErrorException
	 * @expectedExceptionMessage Node.JS is not current. Version v299.10.10 required, but
	 * @expectedExceptionCode NULL
	 */
	public function testTestNodejsFail()
	{
		ob_start();
		try {
			$command = new Commands\Test\NodeJs();
			$command->setRequiredVersion('v299.10.10');
			$command->execute();
		} catch (ErrorException $e) {
			ob_end_clean();
			throw $e;
		}
		$this->assertNotContains('Error', ob_get_clean());
	}


	public function testTestNodejs()
	{
		ob_start();
		$command = new Commands\Test\NodeJs();
		$command->setRequiredVersion('v0.10.10');
		$command->execute();
		$this->assertNotContains('Error', ob_get_clean());
	}

}