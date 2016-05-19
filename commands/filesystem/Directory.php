<?php

namespace Genesis\Commands\Filesystem;


use Genesis\Commands\Command;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Directory extends Command
{

	public function read($directory)
	{
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
		return $files;
	}


	public function clean($directory)
	{
		if (!is_dir($directory)) {
			$this->error("'$directory' is not an directory.");
		}
		foreach ($this->read($directory) as $file) {
			if ($file->isLink()) {
				$this->checkPath($file->getPathName(), $directory);
				$result = @unlink($file->getPathname());
				if (!$result) {
					$this->error("Cannot delete symlink '$file'.");
				}
			} elseif ($file->isDir()) {
				$this->checkPath($file->getPathName(), $directory);
				$result = @rmdir($file->getPathName());
				if (!$result) {
					$this->error("Cannot delete file '$file'.");
				}
			} elseif ($file->isFile()) {
				$this->checkPath($file->getPathName(), $directory);
				$result = @unlink($file->getPathname());
				if (!$result) {
					$this->error("Cannot delete file '$file'.");
				}
			}
		}
	}


	public function create($dir, $chmod = '0777')
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