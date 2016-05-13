<?php


namespace Genesis\Commands;


use Genesis\Cli;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Help extends Command
{

	private $sections = [];


	public function addSection($name, $description = NULL)
	{
		$this->sections[$name] = [
			'description' => $description,
			'tasks' => [],
		];
	}


	/**
	 * @return array
	 */
	public function getSections()
	{
		return $this->sections;
	}


	/**
	 * @param $section
	 * @param array $tasks
	 */
	public function setSectionTasks($section, array $tasks)
	{
		if(!isset($this->sections[$section])){
			throw new \InvalidArgumentException("Section '$section' not found.");
		}
		$this->sections[$section]['tasks'] = $tasks;
	}


	public function execute()
	{
		echo Cli::getColoredString(str_repeat('-', 14), 'green') . PHP_EOL;
		echo Cli::getColoredString('HELP', 'green') . PHP_EOL;
		echo Cli::getColoredString(str_repeat('-', 14), 'green') . PHP_EOL . PHP_EOL;
		echo "Available tasks:" . PHP_EOL;
		foreach ($this->sections as $sectionName => $data) {
			echo Cli::getColoredString($sectionName, 'yellow');
			echo "\t\t\t\t";
			echo Cli::getColoredString($data['description'], 'dark_gray');
			echo PHP_EOL;
			foreach ($data['tasks'] as $taskName => $description) {
				echo "  " . Cli::getColoredString($taskName, 'light_blue');
				echo "\t\t\t\t";
				echo Cli::getColoredString($description, 'gray');
				echo PHP_EOL;
			}
			echo PHP_EOL;
		}
	}

}