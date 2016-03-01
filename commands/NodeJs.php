<?php

namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class NodeJs extends Command
{

	public function installPackages($directory, array $options = NULL)
	{
		$cmd = 'cd ' . escapeshellarg($directory) . ' && npm install';
		if (isset($options['silent']) && $options['silent']) {
			$cmd .= ' --silent'; // supress warnings
		}
		$command = new Exec();
		$result = $command->execute($cmd);
		if ($result->getResult() !== 0) {
			$this->error(sprintf('Installation of node modules for package in dir "%s" failed.', $directory));
		}
		$this->log("Npm installed modules successfully.");
		return $result;
	}

}