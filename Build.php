<?php


namespace Genesis;


use Genesis\Commands;
use Genesis\Config;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Build implements IBuild
{

	protected $container;

	protected $arguments;


	public function __construct(Config\Container $container, array $arguments = NULL)
	{
		$this->container = $container;
		$this->arguments = $arguments;
	}


	public function setup()
	{
	}


	public function runDefault()
	{
		$tasks = $this->detectAvailableTasks();
		$helpCommand = new Commands\Help;
		$helpCommand->addSection('');
		$helpCommand->setSectionTasks('', $tasks);
		$helpCommand->execute();
	}


	protected function detectAvailableTasks()
	{
		$tasks = [];
		$classReflection = new \ReflectionClass($this);
		foreach ($classReflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			if (preg_match('#^run(.*)#', $method->getName(), $match)) {
				$tasks[lcfirst($match[1])] = NULL;
			}
		}
		return $tasks;
	}


	protected function error($message)
	{
		throw new \ErrorException($message);
	}


	protected function logSection($message)
	{
		echo Cli::getColoredString("=> " . $message, 'green') . PHP_EOL;
	}


	protected function log($message)
	{
		echo $message . PHP_EOL;
	}

}