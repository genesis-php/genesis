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
	 * @return mixed
	 */
	public function getCommand()
	{
		return $this->command;
	}


	/**
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
		echo Cli::getColoredString("$this->command\n", 'light_blue');
		exec($this->command, $output, $return);
		$result = new ExecResult($return, $output);
		echo Cli::getColoredString(implode("\n", $output), 'dark_gray') . "\n\n";
		return $result;
	}

}