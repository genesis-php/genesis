<?php


namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Git extends Command
{

	/** @var string */
	private $gitExecutable;


	public function __construct($gitExecutable = 'git')
	{
		if ($gitExecutable == '') {
			throw new \InvalidArgumentException("Git executable cannot be empty.");
		}
		$this->gitExecutable = $gitExecutable;
	}


	public function cloneRepo($url, $branch = NULL, $dir = NULL)
	{
		$command = escapeshellarg($this->gitExecutable) . " clone";
		$command .= " --depth 1 --recursive $url";
		$command .= ($branch ? " --branch $branch" : '');
		$command .= ($dir ? ' ' . escapeshellarg($dir) : '');
		return $this->exec($command);
	}


	public function execute($cmd)
	{
		return $this->exec(escapeshellarg($this->gitExecutable) . ' ' . $cmd);
	}


	private function exec($command)
	{
		$exec = new Exec();
		return $exec->execute($command);
	}

}