<?php


namespace Genesis\Commands;


use Genesis\InvalidArgumentException;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class Git extends Command
{

	/** @var string */
	private $gitExecutable = 'git';

	private $command;


	/**
	 * Returns path to git executable
	 * @return string
	 */
	public function getGitExecutable()
	{
		return $this->gitExecutable;
	}


	/**
	 * Sets path to git executable
	 * @param string $gitExecutable
	 */
	public function setGitExecutable($gitExecutable)
	{
		if ($gitExecutable == '') {
			throw new InvalidArgumentException("Git executable cannot be empty.");
		}
		$this->gitExecutable = $gitExecutable;
	}


	/**
	 * Return setted git command
	 * @return mixed
	 */
	public function getCommand()
	{
		return $this->command;
	}


	/**
	 * Sets git command (to be executed later)
	 * @param mixed $command
	 */
	public function setCommand($command)
	{
		$this->command = $command;
	}


	/**
	 * Sets git command for cloning repository (to be executed later)
	 */
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