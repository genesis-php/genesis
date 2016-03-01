<?php


namespace Genesis\Commands\Assets;


use Genesis\Commands;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Gulp extends Commands\Command
{

	private $gulpfile = 'gulpfile.js';


	public function setGulpfile($gulpfile)
	{
		$this->gulpfile = $gulpfile;
	}


	public function run($directory, $gulpCommand = NULL)
	{
		$path = $directory . DIRECTORY_SEPARATOR . $this->gulpfile;
		if (!file_exists($path)) {
			$this->error(sprintf("Cannot find gulpfile '%s'.", $path));
		}

		$cmd = 'cd ' . escapeshellarg($directory) . ' && gulp ' . $gulpCommand;
		$command = new Commands\Exec();
		$result = $command->execute($cmd);
		if ($result->getResult() !== 0) {
			$this->error(sprintf("Gulp task '%s' in directory %s failed.", $gulpCommand, $directory));
		}
		return $result;
	}

}