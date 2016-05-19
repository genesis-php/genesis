<?php


namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class PhpUnit extends Command
{

	private $workingDir;

	private $target;

	private $options;


	/**
	 * @return mixed
	 */
	public function getWorkingDir()
	{
		return $this->workingDir;
	}


	/**
	 * @param mixed $workingDir
	 */
	public function setWorkingDir($workingDir)
	{
		$this->workingDir = $workingDir;
	}


	/**
	 * @return mixed
	 */
	public function getTarget()
	{
		return $this->target;
	}


	/**
	 * @param mixed $target
	 */
	public function setTarget($target)
	{
		$this->target = $target;
	}


	/**
	 * @return array|NULL
	 */
	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * @param array|NULL $options
	 */
	public function setOptions(array $options = NULL)
	{
		$this->options = $options;
	}


	public function execute()
	{
		if (!isset($this->options['executable'])) {
			$this->error('PHPUnit executable not defined.');
		}

		$cmd = escapeshellarg($this->options['executable']) . ' ';
		if (isset($this->options['xdebugExtensionFile'])) {
			if (!is_file($this->options['xdebugExtensionFile'])) { // PHP is quite when extension file does not exists
				$this->error("Xdebug extension file '{$this->options['xdebugExtensionFile']}' does not exists.");
			}
			$cmd .= '-d zend_extension=' . escapeshellarg($this->options['xdebugExtensionFile']) . ' ';
		}
		if (isset($this->options['configFile'])) {
			$cmd .= '--configuration ';
			$cmd .= escapeshellarg($this->options['configFile']) . ' ';
		}
		$cmd .= escapeshellarg($this->target) . ' ';

		$currdir = getcwd();
		$result = @chdir($this->workingDir);
		if (!$result) {
			$this->error("Cannot change working directory to '$this->workingDir'.");
		}

		$command = new Exec();
		$command->setCommand($cmd);
		$result = $command->execute();
		if ($result->getResult() !== 0) {
			$this->error(sprintf('Tests failed with code %d.', $result->getResult()));
		}

		$result = @chdir($currdir);
		if (!$result) {
			$this->error("Cannot change working directory back to '$currdir'.");
		}
	}

}