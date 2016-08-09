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
		$this->assertEquals('testexec', trim($line, '"')); // on windows there are quotes
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
		$git->setRedirectStderrToStdout(TRUE);
		$this->assertEquals(TRUE, $git->isRedirectStderrToStdout());
		$git->setWorkingDirectory('/my/path');
		$this->assertSame('/my/path', $git->getWorkingDirectory());
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


	public function testGitOperations()
	{
		$dir = __DIR__ . '/genesis-clone';
		ob_start();
		$git = new Commands\Git();
		$git->setRedirectStderrToStdout(TRUE);
		$git->setWorkingDirectory(__DIR__);
		$git->cloneRepo('https://github.com/genesis-php/genesis.git', 'master', $dir);
		$result = $git->execute();
		ob_end_clean();

		$output = $result->getOutput();
		$line = $output[0];
		$this->assertContains('Cloning into', $line);
		$this->assertFileExists($dir); // assert directory exists

		// cleanup
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			// on AppVeyor experiencing permissions issues
			return;
		}
		$directory = new Commands\Filesystem\Directory();
		$directory->clean($dir);
		rmdir($dir);
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
		$result = ob_get_clean();
		$this->assertContains('OK', $result);
	}

	/**
	 * @expectedException \Genesis\ErrorException
	 * @expectedExceptionMessage PHPUnit executable not defined.
	 */
	public function testPhpUnitWithMissingMandatorySetting()
	{
		$workingDir = __DIR__ . '/03';
		$command = new Commands\PhpUnit();
		$command->setWorkingDir($workingDir);
		$this->assertSame($workingDir, $command->getWorkingDir());
		$command->setTarget($workingDir);
		$options = [];
		$command->setOptions($options);
		$this->assertSame($options, $command->getOptions());
		$command->execute();
	}

	public function testNetteTester()
	{
		$workingDir = __DIR__ . '/05';
		ob_start();
		$command = new Commands\NetteTester();
		$command->setWorkingDir($workingDir);
		$this->assertSame($workingDir, $command->getWorkingDir());
		$command->setTarget($workingDir . '/NetteTester.php');
		$options = [
			'executable' => '../../vendor/bin/tester',
			'mode' => 'console',
		];
		$command->setOptions($options);
		$this->assertSame($options, $command->getOptions());
		$command->execute();
		$this->assertContains(
			'OK (1 test',
			ob_get_clean()
		);
	}

	/**
	 * @expectedException \Genesis\ErrorException
	 * @expectedExceptionMessage Tester executable is not defined.
	 */
	public function testNetteTesterWithMissingMandatorySetting()
	{
		$workingDir = __DIR__ . '/05';
		$command = new Commands\NetteTester();
		$command->setWorkingDir($workingDir);
		$this->assertSame($workingDir, $command->getWorkingDir());
		$command->setTarget($workingDir . '/NetteTester.php');
		$options = [];
		$command->setOptions($options);
		$this->assertSame($options, $command->getOptions());
		$command->execute();
		$this->assertContains('OK', ob_get_clean());
	}

	public function testSelfInit()
	{
		$dir = __DIR__ . '/03/output';
		$distDirectory = __DIR__ . '/../src/build-dist';
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
	 * @expectedExceptionMessageRegExp /Directory 'build-dist' in working directory '.+' already exists\./
	 */
	public function testSelfInitError()
	{
		$distDirectory = __DIR__ . '/../src/build-dist';
		$workingDirectory = __DIR__ . '/../src';
		$selfInit = new Commands\SelfInit();
		$selfInit->setDistDirectory($distDirectory);
		$selfInit->setWorkingDirectory($workingDirectory);
		$selfInit->setDirname('build-dist');
		$selfInit->execute();
	}


	/** Assets */

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
		ob_start();
		$command->execute();
		ob_end_clean();
		$res = glob($dir . "/*");
		$this->assertCount(2, $res);
		$this->assertTrue(is_dir($dir . '/testfs1'));
		$this->assertTrue(is_dir($dir . '/testfs2'));
		$this->assertFileExists($dir . '/testfs2/test.js');
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

	public function testFsRelativeSymlinks()
	{
		$dir = __DIR__ . '/03/output';

		ob_start();
		$command = new Commands\Filesystem\Symlink();
		$command->createRelative($dir, 'testfile.json', 'sym-rel-testfile.json');
		ob_end_clean();
		$this->assertTrue(is_link($dir . '/sym-rel-testfile.json'));

		$command = new Commands\Filesystem\Filesystem();
		$command->addSymlinksRelativeToCreate([
			'symlink' => '../'
		], $dir);
		ob_start();
		$command->execute();
		ob_end_clean();
		$this->assertTrue(is_link($dir . '/symlink'));

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { // AppVeyor cannot delete symlinks created by exec()
			return;
		}
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


	/** Tests */

	/**
	 * @expectedException \Genesis\ErrorException
	 * @expectedExceptionMessage Required program "phppp" is not installed
	 * @expectedExceptionCode NULL
	 * @requires OS Linux|Darwin
	 * Do not test on Windows
	 */
	public function testTestProgramsFail()
	{
		$command = new Commands\Test\Programs();
		$command->setRequiredPrograms([
			'phppp' => 'sudo apt-get install php5-cli',
		]);
		$command->execute();
	}

	/**
	 * @requires OS Linux|Darwin
	 * Do not test on Windows
	 */
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