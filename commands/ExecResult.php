<?php


namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class ExecResult
{

	/** @var int */
	private $result;

	/** @var array|NULL */
	private $output;


	/**
	 * @param int $result
	 * @param array|NULL $output
	 */
	public function __construct($result, array $output = NULL)
	{
		$this->result = $result;
		$this->output = $output;
	}


	/**
	 * Returns exit code
	 * @return int
	 */
	public function getResult()
	{
		return $this->result;
	}


	/**
	 * Returns STDOUT
	 * @return array|NULL
	 */
	public function getOutput()
	{
		return $this->output;
	}

}