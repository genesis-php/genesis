<?php


namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 * @internal You should NOT use
 */
class SelfInit extends Command
{

	private $distDirectory;

	private $workingDirectory;

	private $dirname = 'build';


	/**
	 * @return mixed
	 */
	public function getDistDirectory()
	{
		return $this->distDirectory;
	}


	/**
	 * @param mixed $distDirectory
	 */
	public function setDistDirectory($distDirectory)
	{
		$this->distDirectory = $distDirectory;
	}


	/**
	 * @return mixed
	 */
	public function getWorkingDirectory()
	{
		return $this->workingDirectory;
	}


	/**
	 * @param mixed $workingDirectory
	 */
	public function setWorkingDirectory($workingDirectory)
	{
		$this->workingDirectory = $workingDirectory;
	}


	/**
	 * @return string
	 */
	public function getDirname()
	{
		return $this->dirname;
	}


	/**
	 * @param string $dirname
	 */
	public function setDirname($dirname)
	{
		$this->dirname = $dirname;
	}


	public function execute()
	{
		$buildDir = $this->workingDirectory . DIRECTORY_SEPARATOR . $this->dirname;
		if (is_dir($buildDir)) {
			$this->error("Directory '$this->dirname' in working directory '$this->workingDirectory' already exists.");
		}
		$directory = new Filesystem\Directory();
		$directory->create($buildDir);

		$file = new Filesystem\File();
		foreach ($directory->read($this->distDirectory) as $fileInfo) {
			$newFile = $buildDir . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
			$file->copy($fileInfo->getPathName(), $newFile);
			if ($fileInfo->getFilename() === 'build') {
				$file->makeExecutable($newFile);
			}
		}

		$this->log("Build initialized in '$buildDir'.", self::SUCCESS);
		$this->log("You can start by typing '$this->dirname/build' in '$this->workingDirectory'.", self::SUCCESS);
	}

}