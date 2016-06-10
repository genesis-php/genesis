<?php


namespace Genesis;


use Genesis\Config\Container;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class BuildFactory
{

	/**
	 * @param Container $container
	 * @return Build
	 */
	public function create(Container $container, array $arguments = NULL)
	{
		$class = $container->getClass();
		if (!class_exists($class, TRUE)) {
			$this->log(sprintf(
				"Build class '%s' was not found." . PHP_EOL .
				"Are you in correct working directory?" . PHP_EOL .
				"Did you forget add bootstrap.php with class loading into working directory?" . PHP_EOL .
				"Did you forget to load class %s?", $class, $class), 'red');
			throw new TerminateException(NULL, 255);
		}
		$build = new $class($container, $arguments);
		if (!($build instanceof IBuild)) {
			throw new Exception("Instance of build does not implements interface IBuild.");
		}
		$this->autowire($build, $container);
		$build->setup();
		return $build;
	}


	protected function autowire(IBuild $build, Container $container)
	{
		foreach ($this->getAutowiredProperties($build) as $property => $service) {
			if(!$container->hasService($service)){
				throw new Exception("Cannot found service '$service' to inject into " . get_class($build) . "::$property.");
			}
			$build->$property = $container->getService($service);
		}
	}


	protected function getAutowiredProperties($class)
	{
		$return = [];
		$reflectionClass = new \ReflectionClass($class);
		foreach ($reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
			$reflectionProp = new \ReflectionProperty($class, $property->getName());
			$doc = $reflectionProp->getDocComment();
			if (preg_match('#@inject ?([^\s]*)\s#s', $doc, $matches)) {
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