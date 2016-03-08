<?php


namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 * @internal You should NOT use
 */
class SelfInit extends Command
{

	private $distDirectory;


	public function setDistDirectory($distDirectory)
	{
		$this->distDirectory = $distDirectory;
	}


	public function execute($workingDirectory, $dir)
	{
		$buildDir = $workingDirectory . DIRECTORY_SEPARATOR . $dir;
		if(is_dir($buildDir)){
			$this->error("Directory '$dir' in working directory '$workingDirectory' already exists.");
		}
		$directory = new Filesystem\Directory();
		$directory->create($buildDir);

		$file = new Filesystem\File();
		foreach ($directory->read($this->distDirectory) as $fileInfo) {
			$newFile = $buildDir . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
			$file->copy($fileInfo->getPathName(), $newFile);
			if($fileInfo->getFilename() === 'build'){
				$file->makeExecutable($newFile);
			}
		}

		$this->log("Build initialized in '$buildDir'.", self::SUCCESS);
		$this->log("You can start by typing '$dir/build' in '$workingDirectory'.", self::SUCCESS);
	}

}