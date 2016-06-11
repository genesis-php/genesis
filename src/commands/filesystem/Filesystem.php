<?php


namespace Genesis\Commands\Filesystem;

use Genesis\Cli;
use Genesis\Commands\Command;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Filesystem extends Command
{

	const ERROR = 'error',
		SKIP = 'skip',
		REWRITE = 'rewrite';

	private $directoriesToCreate = [];

	private $directoriesToClean = [];

	private $filesToCopy = [];

	//private $symlinksToCreate; // TODO

	private $symlinksRelativeToCreate = [];


	/**
	 * @return array
	 */
	public function getDirectoriesToCreate()
	{
		return $this->directoriesToCreate;
	}


	/**
	 * @param array $directoriesToCreate
	 */
	public function addDirectoriesToCreate(array $directoriesToCreate)
	{
		$this->directoriesToCreate = array_merge($this->directoriesToCreate, $directoriesToCreate);
	}


	/**
	 * @return array
	 */
	public function getDirectoriesToClean()
	{
		return $this->directoriesToClean;
	}


	/**
	 * @param array $directoriesToClean
	 */
	public function addDirectoriesToClean(array $directoriesToClean)
	{
		$this->directoriesToClean = array_merge($this->directoriesToClean, $directoriesToClean);
	}


	/**
	 * @return array
	 */
	public function getSymlinksRelativeToCreate()
	{
		return $this->symlinksRelativeToCreate;
	}


	/**
	 * @param array $symlinksRelativeToCreate
	 */
	public function addSymlinksRelativeToCreate(array $symlinksRelativeToCreate, $baseDir)
	{
		if (!isset($this->symlinksRelativeToCreate[$baseDir])) {
			$this->symlinksRelativeToCreate[$baseDir] = [];
		}
		$this->symlinksRelativeToCreate[$baseDir] = array_merge($this->symlinksRelativeToCreate[$baseDir], $symlinksRelativeToCreate);
	}


	/**
	 * @return array
	 */
	public function getFilesToCopy()
	{
		return $this->filesToCopy;
	}


	/**
	 * @param array $filesToCopy
	 */
	public function addFilesToCopy(array $filesToCopy, $onDuplicate = 'error')
	{
		foreach ($filesToCopy as $destination => $source) {
			$this->filesToCopy[$destination] = [
				'source' => $source,
				'onDuplicate' => $onDuplicate,
			];
		}
	}


	public function execute()
	{
		if (count($this->directoriesToCreate) > 0) {
			$this->log(Cli::getColoredString('Creating directories', 'light_blue'));
			$command = new Directory();
			foreach ($this->directoriesToCreate as $directory => $chmod) {
				if (is_dir($directory)) {
					$this->log("Directory '$directory' already exists, skipping ...");
					continue;
				}
				$command->create($directory, $chmod);
				$this->log("Directory '$directory' created.");
			}
		}
		if (count($this->directoriesToClean) > 0) {
			$this->log(Cli::getColoredString('Cleaning directories', 'light_blue'));
			$command = new Directory();
			foreach ($this->directoriesToClean as $directory) {
				$command->clean($directory);
				$this->log("Directory '$directory' cleaned.");
			}
		}
		if (count($this->filesToCopy) > 0) {
			$this->log(Cli::getColoredString('Copying files', 'light_blue'));
			$command = new File();
			foreach ($this->filesToCopy as $destination => $options) {
				if (is_file($destination)) {
					if ($options['onDuplicate'] == self::ERROR) {
						$this->error("File '$destination' already exists.");
					}elseif ($options['onDuplicate'] == self::SKIP) {
						$this->log("File '$destination' already exists, skipping ...");
						continue;
					}elseif ($options['onDuplicate'] == self::REWRITE) {
						$command->delete($destination);
					}
				}
				$command->copy($options['source'], $destination);
				$this->log("File '$options[source]' copied to '$destination'.");
			}
		}
		if (count($this->symlinksRelativeToCreate) > 0) {
			$this->log(Cli::getColoredString('Creating symlinks', 'light_blue'));
			$command = new \Genesis\Commands\Filesystem\Symlink();
			foreach ($this->symlinksRelativeToCreate as $baseDir => $symlinks) {
				foreach ($symlinks as $link => $target) {
					$absoluteLinkPath = $baseDir . '/' . $link;
					if (is_link($absoluteLinkPath)) {
						$this->log("Symlink '$link' already exists, skipping ...");
						continue;
					}
					$command->createRelative($baseDir, $target, $link);
					$this->log("Symlink '$link' created.");
				}
			}
		}

	}

}