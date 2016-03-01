<?php

namespace Genesis\Commands\Filesystem;


use Genesis\Commands\Command;

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

}