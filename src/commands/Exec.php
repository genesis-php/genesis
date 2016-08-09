<?php


namespace Genesis\Commands;


use Genesis\Cli;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Exec extends Command
{

	private $command;


	/**
	 * Returns setted shell command
	 * @return mixed
	 */
	public function getCommand()
	{
		return $this->command;
	}


	/**
	 * Sets shell command (to be executed later)
	 * @param mixed $command
	 */
	public function setCommand($command)
	{
		$this->command = $command;
	}


	/**
	 * @return ExecResult
	 */
	public function execute()
	{
		echo Cli::getColoredString($this->command . PHP_EOL, 'light_blue');
		exec($this->command, $output, $return);
		$result = new ExecResult($return, $output);
		echo Cli::getColoredString(implode(PHP_EOL, $output), 'dark_gray') . "\n\n";
		return $result;
	}

}