<?php

namespace Genesis\Commands\Filesystem;


use Genesis\Commands\Command;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Directory extends Command
{

	public function clean($directory)
	{
		if (!is_dir($directory)) {
			$this->error("'$directory' is not an directory.");
		}
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory), \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($files as $file) {
			if (in_array($file->getBasename(), array('.', '..'))) {
				continue;
			}
			if ($file->isLink()) {
				$this->checkPath($file->getPathName(), $directory);
				unlink($file->getPathname());
			} elseif ($file->isDir()) {
				$this->checkPath($file->getPathName(), $directory);
				rmdir($file->getPathName());
			} elseif ($file->isFile()) {
				$this->checkPath($file->getPathName(), $directory);
				unlink($file->getPathname());
			}
		}
	}


	public function create($dir, $chmod = NULL)
	{
		if (is_dir($dir)) {
			$this->error("Dir '$dir' already exists.");
		}
		$result = mkdir($dir, $chmod, TRUE);
		exec('chmod ' . escapeshellarg($chmod) . ' ' . escapeshellarg($dir)); // TODO: find workaround - native PHP chmod didnt work
		if (!$result) {
			$this->error("Cannot create dir '$dir'.");
		}
	}


	private function checkPath($current, $directory)
	{
		if (strpos($current, $directory) !== 0) {
			$this->error("Cannot access directory '$current' outside working directory '$directory'.");
		}
	}

}