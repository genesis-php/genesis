<?php


namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Help extends Command
{

	public function execute($tasks)
	{
		echo "Available tasks:" . PHP_EOL;
		foreach ($tasks as $task) {
			echo "- $task" . PHP_EOL;
		}
	}

}