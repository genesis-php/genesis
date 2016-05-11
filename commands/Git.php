<?php


namespace Genesis\Commands;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Git extends Command
{

	/** @var string */
	private $gitExecutable = 'git';

	private $command;


	/**
	 * @return string
	 */
	public function getGitExecutable()
	{
		return $this->gitExecutable;
	}


	/**
	 * @param string $gitExecutable
	 */
	public function setGitExecutable($gitExecutable)
	{
		if ($gitExecutable == '') {
			throw new \InvalidArgumentException("Git executable cannot be empty.");
		}
		$this->gitExecutable = $gitExecutable;
	}


	/**
	 * @return mixed
	 */
	public function getCommand()
	{
		return $this->command;
	}


	/**
	 * @param mixed $command
	 */
	public function setCommand($command)
	{
		$this->command = $command;
	}


	public function cloneRepo($url, $branch = NULL, $dir = NULL)
	{
		$command = escapeshellarg($this->gitExecutable) . " clone";
		$command .= " --depth 1 --recursive $url";
		$command .= ($branch ? " --branch $branch" : '');
		$command .= ($dir ? ' ' . escapeshellarg($dir) : '');
		$this->setCommand($command);
	}


	public function execute()
	{
		return $this->exec(escapeshellarg($this->gitExecutable) . ' ' . $this->command);
	}


	private function exec($command)
	{
		$exec = new Exec();
		$exec->setCommand($command);
		return $exec->execute();
	}

}