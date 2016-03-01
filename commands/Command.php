<?php

namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
abstract class Command
{

	protected function error($message)
	{
		throw new \ErrorException($message);
	}


	protected function log($message)
	{
		echo $message . PHP_EOL;
	}

}