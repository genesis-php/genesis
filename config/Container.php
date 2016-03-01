<?php


namespace Genesis\Config;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 *
 * @property $workingDirectory
 *
 * Config
 */
class Container
{

	private $config;


	public function __construct(array $config)
	{
		$this->config = $config;
	}


	public function &__get($name)
	{
		if (!array_key_exists($name, $this->config)) {
			throw new \Exception("Config key '$name' does not exists.");
		}
		return $this->config[$name];
	}


	public function __set($name, $value)
	{
		$this->config[$name] = $value;
	}

}