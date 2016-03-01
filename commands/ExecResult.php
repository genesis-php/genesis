<?php


namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class ExecResult
{

	/** @var string */
	private $result;

	/** @var string */
	private $output;


	/**
	 * @param string $result
	 * @param string $output
	 */
	public function __construct($result, $output = NULL)
	{
		$this->result = $result;
		$this->output = $output;
	}


	/**
	 * @return string
	 */
	public function getResult()
	{
		return $this->result;
	}


	/**
	 * @return string
	 */
	public function getOutput()
	{
		return $this->output;
	}

}