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


	public function run(InputArgs $inputArgs)
	{
		$workingDir = getcwd();
		if ($inputArgs->getOption('colors') !== NULL && !$inputArgs->getOption('colors')) {
			Cli::$enableColors = FALSE;
		}
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

		$arguments = $inputArgs->getArguments();
		if(isset($arguments[0]) && $arguments[0] === 'self-init'){
			$directoryName = isset($arguments[1]) ? $arguments[1] : 'build';
			$selfInit = new Commands\SelfInit();
			$selfInit->setDistDirectory(__DIR__ . '/build-dist');
			$selfInit->setWorkingDirectory($workingDir);
			$selfInit->setDirname($directoryName);
			$selfInit->execute();
			exit(0);
		}

		$bootstrapFile = $this->detectBootstrapFilename($workingDir);
		$container = NULL;
		if (is_file($bootstrapFile)) {
			$this->log("Info: Found bootstrap.php in working directory.", 'dark_gray');
			$container = require_once $bootstrapFile;
			if($container === 1 || $container === TRUE){ // 1 = success, TRUE = already required
				$container = NULL;
			}elseif(!($container instanceof Container)){
				$this->log("Returned value from bootstrap.php must be instance of 'Genesis\\Container\\Container' or nothing (NULL).", 'red');
				exit(255);
			}
		} else {
			$this->log("Info: bootstrap.php was not found in working directory.", 'dark_gray');
		}

		$arguments = $inputArgs->getArguments();
		$configFile = $inputArgs->getOption('config') ? $inputArgs->getOption('config') : self::DEFAULT_CONFIG_FILE;
		try {
			$container = $this->createContainer($workingDir, $configFile, $container);
			$build = $this->createBuild($container, $arguments);
		} catch (\Exception $e) {
			$this->log("Exited with ERROR:", 'red');
			$this->log($e->getMessage(), 'red');
			echo $e->getTraceAsString() . PHP_EOL;
			exit(255);
		}
		if (count($arguments) < 1) {
			$this->log("Running default", 'green');
			$build->runDefault();
			exit(0);
		}

		$method = 'run' . str_replace('-', '', ucfirst($arguments[0]));
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
	protected function createContainer($workingDir, $configFile, Container $bootstrapContainer = NULL)
	{
		$factory = new ContainerFactory();
		$factory->addConfig($workingDir . '/' . $configFile);
		if(is_file($workingDir . '/config.local.neon')){
			$factory->addConfig($workingDir . '/config.local.neon');
		}
		$factory->setWorkingDirectory($workingDir);
		if($bootstrapContainer !== NULL){
			$factory->addContainerToMerge($bootstrapContainer);
		}
		return $factory->create();
	}


	/**
	 * @param Container $container
	 * @return Build
	 */
	protected function createBuild(Container $container, array $arguments = NULL)
	{
		$class = $container->getClass();
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
		$this->autowire($build, $container);
		$build->setup();
		return $build;
	}


	protected function autowire(IBuild $build, Container $container)
	{
		foreach ($this->getAutowiredProperties($build) as $property => $service) {
			if(!$container->hasService($service)){
				throw new \RuntimeException("Cannot found service '$service' to inject into " . get_class($build) . "::$property.");
			}
			$build->$property = $container->getService($service);
		}
	}


	protected function getAutowiredProperties($class)
	{
		$return = [];
		$reflectionClass = new \ReflectionClass($class);
		foreach($reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $property){
			$reflectionProp = new \ReflectionProperty($class, $property->getName());
			$doc = $reflectionProp->getDocComment();
			if(preg_match('#@inject ?([^\s]*)\s#s', $doc, $matches)){
				$return[$property->getName()] = trim($matches[1]) !== '' ? $matches[1] : $property->getName();
			}
		}
		return $return;
	}


	protected function log($message, $color = NULL, $backgroundColor = NULL)
	{
		echo Cli::getColoredString($message . PHP_EOL, $color, $backgroundColor);
	}

}