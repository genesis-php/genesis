<?php


namespace Genesis\Commands\Test;


use Genesis\Commands\Command;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Programs extends Command
{

	private $requiredPrograms;


	/**
	 * @return array
	 */
	public function getRequiredPrograms()
	{
		return $this->requiredPrograms;
	}


	/**
	 * @param array $requiredPrograms
	 */
	public function setRequiredPrograms($requiredPrograms)
	{
		$this->requiredPrograms = $requiredPrograms;
	}


	public function execute()
	{
		$errors = array();
		foreach ($this->requiredPrograms as $program => $howToInstallCommand) {
			exec('command -v ' . escapeshellarg($program) . ' >/dev/null 2>&1', $output, $return);
			if ($return !== 0) {
				$errors[] = 'Required program "' . $program . '" is not installed.';
				if ($howToInstallCommand) {
					$errors[] = 'You can fix this by running: ' . $howToInstallCommand;
				}
			}
		}

		if (!empty($errors)) {
			$this->error(implode(PHP_EOL, $errors));
		}

		$this->log('Required programs are installed.');
	}

}
