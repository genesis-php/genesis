<?php


namespace Genesis\Commands\Assets;


use Genesis\Commands;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Gulp extends Commands\Command
{

	private $gulpfile = 'gulpfile.js';

	private $directory;


	/**
	 * @return string
	 */
	public function getGulpfile()
	{
		return $this->gulpfile;
	}


	/**
	 * @param string $gulpfile
	 */
	public function setGulpfile($gulpfile)
	{
		$this->gulpfile = $gulpfile;
	}


	/**
	 * Returns working directory.
	 * @return string
	 */
	public function getDirectory()
	{
		return $this->directory;
	}


	/**
	 * Sets working directory. System will switch to this directory before running gulp.
	 * @param string $directory
	 */
	public function setDirectory($directory)
	{
		$this->directory = $directory;
	}


	public function execute($gulpCommand = NULL)
	{
		$path = $this->directory . DIRECTORY_SEPARATOR . $this->gulpfile;
		if (!file_exists($path)) {
			$this->error(sprintf("Cannot find gulpfile '%s'.", $path));
		}

		$cmd = 'cd ' . escapeshellarg($this->directory) . ' && gulp ' . $gulpCommand;
		$command = new Commands\Exec();
		$command->setCommand($cmd);
		$result = $command->execute();
		if ($result->getResult() !== 0) {
			$this->error(sprintf("Gulp task '%s' in directory %s failed.", $gulpCommand, $this->directory));
		}
		return $result;
	}

}