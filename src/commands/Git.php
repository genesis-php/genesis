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

	private $workingDirectory;

	/**
	 * Redirect stderr to stdout? - for testing purposes only
	 * Git writes his progressive output do stderr (even if everything ok) - at least when clonning
	 * @var bool
	 */
	private $redirectStderrToStdout = FALSE;


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
	 * @return string
	 */
	public function getWorkingDirectory()
	{
		return $this->workingDirectory;
	}


	/**
	 * Sets path to working directory
	 * @param string $workingTree
	 */
	public function setWorkingDirectory($workingDirectory)
	{
		$this->workingDirectory = $workingDirectory;
	}


	/**
	 * @return boolean
	 */
	public function isRedirectStderrToStdout()
	{
		return $this->redirectStderrToStdout;
	}


	/**
	 * @param boolean $redirectStderrToStdout
	 */
	public function setRedirectStderrToStdout($redirectStderrToStdout)
	{
		$this->redirectStderrToStdout = $redirectStderrToStdout;
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
		$command = "clone";
		$command .= " --depth 1 --recursive $url";
		$command .= ($branch ? " --branch $branch" : '');
		$command .= ($dir ? ' ' . escapeshellarg($dir) : '');
		$this->setCommand($command);
	}


	/**
	 * @return ExecResult
	 */
	public function execute()
	{
		$command = '';
		if ($this->workingDirectory !== NULL) {
			$command .= 'cd ' . escapeshellarg($this->workingDirectory) . ' && ';
		}
		$command .= escapeshellarg($this->gitExecutable);
		$execCommand = $command . ' ' . $this->command;
		if ($this->redirectStderrToStdout) {
			$execCommand .= ' 2>&1';
		}
		return $this->exec($execCommand);
	}


	private function exec($command)
	{
		$exec = new Exec();
		$exec->setCommand($command);
		return $exec->execute();
	}

}