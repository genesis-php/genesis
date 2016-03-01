<?php


namespace Genesis\Commands;


use Genesis\Cli;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Exec extends Command
{

	/**
	 * @param $command
	 * @return ExecResult
	 */
	public function execute($command)
	{
		echo Cli::getColoredString("$command\n", 'light_blue');
		exec($command, $output, $return);
		$result = new ExecResult($return, $output);
		echo Cli::getColoredString(implode("\n", $output), 'dark_gray') . "\n\n";
		return $result;
	}

}