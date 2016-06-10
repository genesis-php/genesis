<?php


namespace Genesis\Commands;


use Genesis\Cli;
use Genesis\InvalidArgumentException;


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
	 * @param $name
	 * @return bool
	 */
	public function hasSection($name)
	{
		return isset($this->sections[$name]);
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
			throw new InvalidArgumentException("Section '$section' not found.");
		}
		$this->sections[$section]['tasks'] = $tasks;
	}


	public function execute()
	{
		// detect max width
		$minColumnWidth = 30;
		foreach ($this->sections as $sectionName => $data) {
			if (strlen($sectionName) > $minColumnWidth) {
				$minColumnWidth = strlen($sectionName) + 5;
			}
			foreach ($data['tasks'] as $taskName => $description) {
				if (strlen($taskName) > $minColumnWidth) {
					$minColumnWidth = strlen($taskName) + 2 + 5;
				}
			}
		}
		// empty section first
		if (isset($this->sections[''])) {
			$val = $this->sections[''];
			unset($this->sections['']);
			$this->sections = ['' => $val] + $this->sections;
		}

		echo Cli::getColoredString(str_repeat('-', 14), 'green') . PHP_EOL;
		echo Cli::getColoredString('HELP', 'green') . PHP_EOL;
		echo Cli::getColoredString(str_repeat('-', 14), 'green') . PHP_EOL . PHP_EOL;
		echo "Available tasks:" . PHP_EOL;
		foreach ($this->sections as $sectionName => $data) {
			echo Cli::getColoredString($sectionName, 'yellow');
			echo str_repeat(" ", $minColumnWidth - strlen($sectionName) + 2); // +2 = two spaces before taskName (below)
			echo Cli::getColoredString($data['description'], 'dark_gray');
			echo PHP_EOL;
			foreach ($data['tasks'] as $taskName => $description) {
				echo "  " . Cli::getColoredString($taskName, 'light_blue');
				echo str_repeat(" ", $minColumnWidth - strlen($taskName));
				echo Cli::getColoredString($description, 'gray');
				echo PHP_EOL;
			}
			echo PHP_EOL;
		}
	}

}