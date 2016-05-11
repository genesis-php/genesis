<?php


namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Help extends Command
{

	private $tasks = [];


	/**
	 * @return array
	 */
	public function getTasks()
	{
		return $this->tasks;
	}


	/**
	 * @param array $tasks
	 */
	public function setTasks($tasks)
	{
		$this->tasks = $tasks;
	}


	public function execute()
	{
		echo "Available tasks:" . PHP_EOL;
		foreach ($this->tasks as $task) {
			echo "- $task" . PHP_EOL;
		}
	}

}