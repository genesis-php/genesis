<?php


namespace Genesis;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 *
 * InputArgs
 */
class InputArgs
{

	private $arguments = [];

	private $options = [];


	/**
	 * @return array
	 */
	public function getArguments()
	{
		return $this->arguments;
	}


	/**
	 * @param array $arguments
	 */
	public function setArguments(array $arguments)
	{
		$this->arguments = $arguments;
	}


	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * @param $name
	 * @return string
	 */
	public function getOption($name)
	{
		if (!isset($this->options[$name])) {
			return NULL;
		}
		return $this->options[$name];
	}


	/**
	 * @param array $options
	 */
	public function setOptions(array $options)
	{
		$this->options = $options;
	}

}