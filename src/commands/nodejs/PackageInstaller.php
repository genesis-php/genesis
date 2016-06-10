<?php

namespace Genesis\Commands\NodeJs;

use Genesis\Commands\Command;
use Genesis\Commands\Exec;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class PackageInstaller extends Command
{

	private $directory;

	private $options;


	/**
	 * Gets working directory.
	 * @return string
	 */
	public function getDirectory()
	{
		return $this->directory;
	}


	/**
	 * Sets working directory. System will switch to this directory before running npm.
	 * @param string $directory
	 */
	public function setDirectory($directory)
	{
		$this->directory = $directory;
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
		$cmd = 'cd ' . escapeshellarg($this->directory) . ' && npm install';
		if (isset($this->options['silent']) && $this->options['silent']) {
			$cmd .= ' --silent'; // supress warnings
		}
		$command = new Exec();
		$command->setCommand($cmd);
		$result = $command->execute();
		if ($result->getResult() !== 0) {
			$this->error(sprintf('Installation of node modules for package in dir "%s" failed.', $this->directory));
		}
		$this->log("Npm installed modules successfully.");
		return $result;
	}

}