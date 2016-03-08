<?php


namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class PhpUnit extends Command
{

	public function execute($workingDir, $target, array $options)
	{
		if (!isset($options['executable'])) {
			$this->error('PHPUnit executable not defined.');
		}

		$cmd = escapeshellarg($options['executable']) . ' ';
		if (isset($options['configFile'])) {
			$cmd .= '--configuration ';
			$cmd .= escapeshellarg($options['configFile']) . ' ';
		}
		$cmd .= escapeshellarg($target) . ' ';

		$currdir = getcwd();
		@chdir($workingDir);

		$command = new Exec();
		$result = $command->execute($cmd);
		if ($result->getResult() !== 0) {
			$this->error(sprintf('Tests failed with code %d.', $result->getResult()));
		}

		@chdir($currdir);
	}

}