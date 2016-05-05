<?php


namespace Genesis\Config;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Container implements \IteratorAggregate
{

	/** @var string */
	private $class;

	/** @var array */
	private $parameters = [];

	private $services = [];


	public function getClass()
	{
		return $this->class;
	}


	public function setClass($class)
	{
		$this->class = $class;
	}


	public function getParameter($name)
	{
		if (!array_key_exists($name, $this->parameters)) {
			throw new \Exception("Config key '$name' does not exists.");
		}
		return $this->parameters[$name];
	}


	public function getParameters()
	{
		return $this->parameters;
	}


	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}


	public function setParameters($parameters)
	{
		$this->parameters = $parameters;
	}


	public function getServices()
	{
		return $this->services;
	}


	public function getService($name)
	{
		if (!isset($this->services[$name])) {
			throw new \Exception("Service '$name' does not exists.");
		}
		return $this->services[$name];
	}


	public function hasService($name)
	{
		return isset($this->services[$name]);
	}


	public function addService($name, $service)
	{
		$this->services[$name] = $service;
	}


	public function getIterator()
	{
		return new \ArrayIterator($this->parameters);
	}


	public function &__get($name)
	{
		throw new \RuntimeException("Direct getting is not supported. Use setParameter() instead.");
	}


	public function __set($name, $value)
	{
		throw new \RuntimeException("Direct setting is not supported. Use setParameter() instead.");
	}

}