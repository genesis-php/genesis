<?php

namespace Genesis\Commands;


use Genesis\Cli;
use Genesis\ErrorException;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
abstract class Command
{

	const SUCCESS = 'success';

	const ERROR = 'error';

	private static $severityColors = [
		self::SUCCESS => 'green',
		self::ERROR => 'red',
	];


	/**
	 * Trigger an error and ends execution (if not catched)
	 * @param $message
	 * @throws ErrorException
	 */
	protected function error($message)
	{
		throw new ErrorException($message);
	}


	protected function log($message, $severity = NULL)
	{
		$color = isset(self::$severityColors[$severity]) ? self::$severityColors[$severity] : NULL;
		echo Cli::getColoredString($message . PHP_EOL, $color);
	}

}