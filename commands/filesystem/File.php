<?php

namespace Genesis\Commands\Filesystem;


use Genesis\Commands\Command;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class File extends Command
{

	public function create($file, $contents = NULL, $chmod = NULL)
	{
		if (is_file($file)) {
			$this->error("File '$file' already exists.");
		}
		$result = file_put_contents($file, $contents);
		if (!$result) {
			$this->error("Cannot create file '$file'.");
		}
		if ($chmod !== NULL) {
			$result = chmod($file, $chmod);
			if (!$result) {
				$this->error("Cannot chmod file '$file'.");
			}
		}
	}


	public function copy($source, $destination)
	{
		if (!is_file($source)) {
			$this->error("Source file '$source' does not exists.");
		}
		if (is_file($destination)) {
			$this->error("Destination file '$destination' already exists.");
		}
		$result = copy($source, $destination);
		if (!$result) {
			$this->error("Cannot create file '$destination'.");
		}
	}

}