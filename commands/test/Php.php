<?php


namespace Genesis\Commands\Test;


use Genesis\Commands\Command;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Php extends Command
{

	/** @var string[] */
	private $errors;


	public function execute(array $settings)
	{
		if (isset($settings['settings'])) {
			$this->testSettings($settings['settings']);
		}
		if (isset($settings['extensions'])) {
			$this->testExtensions($settings['extensions']);
		}

		if (!empty($this->errors)) {
			$this->error(implode(PHP_EOL, $this->errors));
		}

		$this->log('PHP settings correct.');
	}


	private function testSettings(array $settings)
	{
		foreach ($settings as $option => $requiredValue) {
			$setValue = ini_get($option);
			if ($requiredValue === '0') {
				if ($setValue !== '' && $setValue && $setValue != $requiredValue) { // intentionally !=
					$this->errors[] = 'PHP option ' . $option . ' is not set to required value "' . $requiredValue . '".';
				}
			} else {
				if ($setValue != $requiredValue) { // intentionally
					$this->errors[] = 'PHP option ' . $option . ' is not set to required value "' . $requiredValue . '".';
				}
			}
		}
	}


	private function testExtensions(array $extensions)
	{
		foreach ($extensions as $extension) {
			if (!extension_loaded($extension)) {
				$this->errors[] = 'Required PHP extension "' . $extension . '" is not loaded.';
			}
		}
	}

}
