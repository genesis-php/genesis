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


	public function execute($requiredVersion)
	{
		$cmd = $this->nodeVersionCommand;
		$command = new Commands\Exec();
		$result = $command->execute($cmd);
		if ($result->getResult() !== 0) {
			$this->error(sprintf('Execution of command "%s" failed.', $command));
		}

		$version = $result->getOutput()[0];
		if (version_compare($version, $requiredVersion) < 0) {
			$this->error(sprintf('Node.JS is not current. Version %s required, but %s is installed.', $requiredVersion, $version));
		}

		$this->log(sprintf('Required Node.JS version %s satisfied with installed version %s.', $requiredVersion, $version));
	}

}
