<?php


namespace Genesis\Commands\Test;


use Genesis\Commands;
use Genesis\Commands\Command;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class NodeJs extends Command
{

	private $nodeVersionCommand = 'node -v';

	private $requiredVersion;


	/**
	 * @return float
	 */
	public function getRequiredVersion()
	{
		return $this->requiredVersion;
	}


	/**
	 * @param float $requiredVersion
	 */
	public function setRequiredVersion($requiredVersion)
	{
		$this->requiredVersion = $requiredVersion;
	}


	public function execute()
	{
		$cmd = $this->nodeVersionCommand;
		$command = new Commands\Exec();
		$command->setCommand($cmd);
		$result = $command->execute();
		if ($result->getResult() !== 0) {
			$this->error(sprintf('Execution of command "%s" failed.', $cmd));
		}

		$version = $result->getOutput()[0];
		if (version_compare($version, $this->requiredVersion) < 0) {
			$this->error(sprintf('Node.JS is not current. Version %s required, but %s is installed.', $this->requiredVersion, $version));
		}

		$this->log(sprintf('Required Node.JS version %s satisfied with installed version %s.', $this->requiredVersion, $version));
	}

}
