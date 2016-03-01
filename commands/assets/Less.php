<?php


namespace Genesis\Commands\Assets;


use Genesis\Commands;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Less extends Commands\Command
{

	private $executable = 'lessc';


	public function compile(array $files, array $options = NULL)
	{
		if (count($files) === 0) {
			return;
		}

		$opts = '--no-color --verbose ';
		if (isset($options['compress']) && $options['compress']) {
			$opts .= '--compress ';
		}
		if (isset($options['relativeUrls']) && $options['relativeUrls']) {
			$opts .= '--relative-urls ';
		}

		foreach ($files as $source => $target) {
			$cmd = escapeshellarg($this->executable) . ' ' . $opts . escapeshellarg($source) . ' ' . escapeshellarg($target);
			$command = new Commands\Exec();
			$result = $command->execute($cmd);
			if ($result->getResult() !== 0) {
				$this->error(sprintf("LESS compilation of file '%s' failed.", $source));
			}
		}
	}

}