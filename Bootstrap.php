<?php


namespace Genesis;


use Genesis\Config\Container;
use Genesis\Config\ContainerFactory;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 *
 * Bootstrap
 */
class Bootstrap
{

	public function run(InputArgs $inputArgs)
	{
		$workingDir = getcwd();
		if ($inputArgs->getOption('working-dir')) {
			$workingDir = realpath($inputArgs->getOption('working-dir'));
			if (!$workingDir) {
				$this->log(sprintf("Working dir '%s' does not exists.", $inputArgs->getOption('working-dir')), 'red');
				exit(255);
			}
		}
		if ($workingDir === __DIR__) {
			$this->log(sprintf("Working dir '%s' is directory with Genesis. You have to choose directory with build.", $workingDir), 'red');
			exit(255);
		}

		$bootstrapFile = $this->detectBootstrapFilename($workingDir);
		if (is_file($bootstrapFile)) {
			$this->log("Info: Found bootstrap.php in working directory.", 'dark_gray');
			require_once $bootstrapFile;
		} else {
			$this->log("Info: bootstrap.php was not found in working directory", 'dark_gray');
		}

		$arguments = $inputArgs->getArguments();
		$container = $this->createContainer($workingDir);
		$build = $this->createBuild($container, $arguments);
		if (count($arguments) < 1) {
			$this->log("Running default", 'green');
			$build->runDefault();
			exit(0);
		}

		$method = 'run' . ucfirst($arguments[0]);
		if (!method_exists($build, $method)) {
			$this->log("Task '$arguments[0]' does not exists.", 'red');
			exit(255);
		}
		$this->log("Running [$arguments[0]]", 'green');
		try {
			$build->$method();
		} catch (\Exception $e) {
			$this->log("Exited with ERROR:", 'red');
			$this->log($e->getMessage(), 'red');
			echo $e->getTraceAsString() . PHP_EOL;
			exit(255);
		}
		$this->log("Exited with SUCCESS", 'black', 'green');
		echo PHP_EOL;
		exit(0);
	}


	protected function detectBootstrapFilename($workingDir)
	{
		return $workingDir . DIRECTORY_SEPARATOR . 'bootstrap.php';
	}


	/**
	 * @return Container
	 */
	protected function createContainer($workingDir)
	{
		$factory = new ContainerFactory();
		$factory->addConfig($workingDir . '/config.neon');
		$factory->setWorkingDirectory($workingDir);
		return $factory->create();
	}


	/**
	 * @param Container $container
	 * @return Build
	 */
	protected function createBuild(Container $container, array $arguments = NULL)
	{
		$class = $container->class;
		if (!class_exists($class, TRUE)) {
			$this->log(sprintf(
				"Build class '%s' was not found." . PHP_EOL .
				"Are you in correct working directory?" . PHP_EOL .
				"Did you forget add bootstrap.php with class loading into working directory?" . PHP_EOL .
				"Did you forget to load class %s?", $class, $class), 'red');
			exit(255);
		}
		$build = new $class($container, $arguments);
		if (!($build instanceof IBuild)) {
			throw new \RuntimeException("Instance of build does not implements interface IBuild.");
		}
		return $build;
	}


	protected function log($message, $color = NULL, $backgroundColor = NULL)
	{
		echo Cli::getColoredString($message . PHP_EOL, $color, $backgroundColor);
	}

}