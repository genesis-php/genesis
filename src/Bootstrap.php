<?php


namespace Genesis;


use Genesis\Config\Container;
use Genesis\Config\ContainerFactory;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
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


	/**
	 * @param InputArgs $inputArgs
	 * @throws TerminateException
	 */
	public function run(InputArgs $inputArgs)
	{
		$this->startup($inputArgs);
		$this->selfInit($inputArgs);
		$arguments = $inputArgs->getArguments();
		$container = $this->resolveBootstrap();
		$configFile = $inputArgs->getOption('config') ? $inputArgs->getOption('config') : self::DEFAULT_CONFIG_FILE;
		try {
			$container = $this->createContainer($configFile, $container);
			$build = $this->createBuild($container, $arguments);
		} catch (\Throwable $e) { // fault barrier -> catch all
			$this->handleException($e);
		} catch (\Exception $e) { // fault barrier -> catch all, PHP 5.x compatibility
			$this->handleException($e);
		}
		if (count($arguments) < 1) {
			$this->invokeTask($build, 'default', $arguments);
			$this->terminate(0);
		}

		try {
			$this->invokeTask($build, $arguments[0], $arguments);
		} catch (TerminateException $e) {
			throw $e; // rethrow -> shutdown imminent
		} catch (\Throwable $e) { // fault barrier -> catch all
			$this->handleException($e);
		} catch (\Exception $e) { // fault barrier -> catch all, PHP 5.x compatibility
			$this->handleException($e);
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


	private function selfInit(InputArgs $inputArgs)
	{
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
	}


	/**
	 * @return Container|NULL
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
	 * @param $configFile
	 * @param Container|NULL $bootstrapContainer
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
	private function createBuild(Container $container, array $arguments)
	{
		if ($this->buildFactory === NULL) {
			throw new InvalidStateException("Build factory was not setted.");
		}
		return $this->buildFactory->create($container, $arguments);
	}

	/**
	 * @param Build $build
	 * @param $task
	 * @param array|NULL $arguments
	 */
	private function invokeTask(Build $build, $task, array $arguments)
	{
		$method = 'run' . str_replace('-', '', ucfirst($task));
		if (!method_exists($build, $method)) {
			$this->log("Task '$task' does not exists.", 'red');
			$this->terminate(255);
		}
		$this->log("Running [$task]", 'green');
		$args = array_slice($arguments, 1); // first argument is task name, slice it
		call_user_func_array([$build, $method], $args);
	}


	/**
	 * @param \Exception|\Throwable $e
	 * @throws TerminateException
	 */
	private function handleException($e)
	{
		$this->log("Exited with ERROR:", 'red');
		$this->log($e->getMessage(), 'red');
		echo $e->getTraceAsString() . PHP_EOL;
		$this->terminate(255);
	}


	/**
	 * @param string $message
	 * @param string|null $color
	 * @param string|null $backgroundColor
	 */
	private function log($message, $color = NULL, $backgroundColor = NULL)
	{
		echo Cli::getColoredString($message . PHP_EOL, $color, $backgroundColor);
	}

}