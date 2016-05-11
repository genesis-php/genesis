<?php


namespace Genesis\Commands\Assets;


use Genesis\Commands;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Less extends Commands\Command
{

	private $executable = 'lessc';

	private $files = [];

	/** @var   */
	private $options;


	/**
	 * @return string
	 */
	public function getExecutable()
	{
		return $this->executable;
	}


	/**
	 * @param string $executable
	 */
	public function setExecutable($executable)
	{
		$this->executable = $executable;
	}


	/**
	 * @return array
	 */
	public function getFiles()
	{
		return $this->files;
	}


	/**
	 * @param array $files
	 */
	public function setFiles($files)
	{
		$this->files = $files;
	}


	/**
	 * @return array|NULL
	 */
	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * @param array|NULL $options
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}


	public function execute()
	{
		if (count($this->files) === 0) {
			return;
		}

		$opts = '--no-color --verbose ';
		if (isset($this->options['compress']) && $this->options['compress']) {
			$opts .= '--compress ';
		}
		if (isset($this->options['relativeUrls']) && $this->options['relativeUrls']) {
			$opts .= '--relative-urls ';
		}

		foreach ($this->files as $source => $target) {
			$cmd = escapeshellarg($this->executable) . ' ' . $opts . escapeshellarg($source) . ' ' . escapeshellarg($target);
			$command = new Commands\Exec();
			$command->setCommand($cmd);
			$result = $command->execute();
			if ($result->getResult() !== 0) {
				$this->error(sprintf("LESS compilation of file '%s' failed.", $source));
			}
		}
	}

}