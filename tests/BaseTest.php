<?php


namespace Genesis\Tests;

/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
abstract class BaseTest extends \PHPUnit_Framework_TestCase
{

	protected $executable;


	public function setUp()
	{
		$this->executable = __DIR__ . '/../genesis';
	}


	protected function execute($command, array $options = [])
	{
		$opts = [
			'--working-dir' => '01',
		];
		if (isset($options['working-dir'])) {
			$opts['--working-dir'] = $options['working-dir'];
		}
		if (isset($options['config'])) {
			$opts['--config'] = $options['config'];
		}

		$cmd = $this->executable . ' ';
		foreach ($opts as $k => $v) {
			$cmd .= $k . ' ' . $v . ' ';
		}
		$cmd .= $command;
		exec($cmd, $output, $return);
		return [
			'code' => $return, 'output' => $output,
		];
	}

}