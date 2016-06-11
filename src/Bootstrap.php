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

	const DEFAULT_CONFIG_FILE = 'config.neon';

	/** @var BuildFactory */
	private $buildFactory;

	/** @var string */
	private $workingDir;


	/**
	 * @return BuildFactory
	 */
	public function getBuildFactory()
	{
		return $this->buildFactory;
	}


	/**
	 * @param BuildFactory $buildFactory
	 */
	public function setBuildFactory($buildFactory)
	{
		$this->buildFactory = $buildFactory;
	}


	public function run(InputArgs $inputArgs)
	{
		$this->startup($inputArgs);
		$arguments = $inputArgs->getArguments();
		if (isset($arguments[0]) && $arguments[0] === 'self-init') {
			$directoryName = isset($arguments[1]) ? $arguments[1] : 'build';
			$selfInit = new Commands\SelfInit();
			$selfInit->setDistDirectory(__DIR__ . '/build-dist');
			$selfInit->setWorkingDirectory($this->workingDir);
			$selfInit->setDirname($directoryName);
			$selfInit->execute();
			$this->terminate(0);
		}
		$container = $this->resolveBootstrap();
		$configFile = $inputArgs->getOption('config') ? $inputArgs->getOption('config') : self::DEFAULT_CONFIG_FILE;
		try {
			$container = $this->createContainer($configFile, $container);
			$build = $this->createBuild($container, $arguments);
		} catch (Exception $e) {
			$this->log("Exited with ERROR:", 'red');
			$this->log($e->getMessage(), 'red');
			echo $e->getTraceAsString() . PHP_EOL;
			$this->terminate(255);
		}
		if (count($arguments) < 1) {
			$this->log("Running default", 'green');
			$build->runDefault();
			$this->terminate(0);
		}

		$method = 'run' . str_replace('-', '', ucfirst($arguments[0]));
		if (!method_exists($build, $method)) {
			$this->log("Task '$arguments[0]' does not exists.", 'red');
			$this->terminate(255);
		}
		$this->log("Running [$arguments[0]]", 'green');
		try {
			$build->$method();
		} catch (\Exception $e) { // fault barrier -> catch all
			$this->log("Exited with ERROR:", 'red');
			$this->log($e->getMessage(), 'red');
			echo $e->getTraceAsString() . PHP_EOL;
			$this->terminate(255);
		}
		$this->log("Exited with SUCCESS", 'black', 'green');
		echo PHP_EOL;
		$this->terminate(0);
	}


	private function terminate($code)
	{
		throw new TerminateException(NULL, $code);
	}


	private function startup(InputArgs $inputArgs)
	{
		$this->workingDir = getcwd();
		if ($inputArgs->getOption('colors') !== NULL && !$inputArgs->getOption('colors')) {
			Cli::$enableColors = FALSE;
		}
		if ($inputArgs->getOption('working-dir')) {
			$this->workingDir = realpath($inputArgs->getOption('working-dir'));
			if (!$this->workingDir) {
				$this->log(sprintf("Working dir '%s' does not exists.", $inputArgs->getOption('working-dir')), 'red');
				$this->terminate(255);
			}
		}
		if ($this->workingDir === __DIR__) {
			$this->log(sprintf("Working dir '%s' is directory with Genesis. You have to choose directory with build.", $this->workingDir), 'red');
			$this->terminate(255);
		}
	}


	/**
	 * @return Container|null
	 */
	private function resolveBootstrap()
	{
		$bootstrapFile = $this->workingDir . DIRECTORY_SEPARATOR . 'bootstrap.php';
		if (!is_file($bootstrapFile)) {
			$this->log("Info: bootstrap.php was not found in working directory.", 'dark_gray');
			return NULL;
		}
		$this->log("Info: Found bootstrap.php in working directory.", 'dark_gray');
		$container = require_once $bootstrapFile;
		if ($container === 1 || $container === TRUE) { // 1 = success, TRUE = already required
			return NULL;
		} elseif ($container instanceof Container) {
			return $container;
		}
		$this->log("Returned value from bootstrap.php must be instance of 'Genesis\\Container\\Container' or nothing (NULL).", 'red');
		$this->terminate(255);
	}


	/**
	 * @return Container
	 */
	private function createContainer($configFile, Container $bootstrapContainer = NULL)
	{
		$factory = new ContainerFactory();
		$factory->addConfig($this->workingDir . '/' . $configFile);
		if (is_file($this->workingDir . '/config.local.neon')) {
			$factory->addConfig($this->workingDir . '/config.local.neon');
		}
		$factory->setWorkingDirectory($this->workingDir);
		if ($bootstrapContainer !== NULL) {
			$factory->addContainerToMerge($bootstrapContainer);
		}
		return $factory->create();
	}


	/**
	 * @param Container $container
	 * @return Build
	 */
	private function createBuild(Container $container, array $arguments = NULL)
	{
		if ($this->buildFactory === NULL) {
			throw new InvalidStateException("Build factory was not setted.");
		}
		return $this->buildFactory->create($container, $arguments);
	}


	private function log($message, $color = NULL, $backgroundColor = NULL)
	{
		echo Cli::getColoredString($message . PHP_EOL, $color, $backgroundColor);
	}

}