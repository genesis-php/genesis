<?php

namespace Genesis\Commands\Filesystem;


use Genesis\Commands\Command;
use Genesis\Commands;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Symlink extends Command
{

	public function create($target, $link)
	{
		$result = symlink($target, $link);
		if (!$result) {
			$this->error("Cannot create symlink '$target' - '$link'.");
		}
	}


	/**
	 * target is relative to link!
	 * eg: dir, ../mydir, public/symdir
	 */
	public function createRelative($directory, $target, $link)
	{
		if (!is_dir($directory)) {
			$this->error("Directory '$directory' not found.");
		}
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$cmd = 'cd ' . escapeshellarg($directory) . ' && mklink ' . ' /D ' . escapeshellarg($link) . ' ' . escapeshellarg($target);
		} else {
			$cmd = 'cd ' . escapeshellarg($directory) . ' && ln -s ' . escapeshellarg($target) . ' ' . escapeshellarg($link);
		}
		$command = new Commands\Exec();
		$command->setCommand($cmd);
		$result = $command->execute();
		if ($result->getResult() !== 0) {
			$this->error("Cannot create symlink '$target' - '$link' in directory '$directory'.");
		}
		return $result;
	}

}