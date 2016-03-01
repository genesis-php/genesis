<?php


namespace Genesis\Tests;


use Tester;

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
			'--working-dir' => 'tests/01',
		];
		if (isset($options['working-dir'])) {
			$opts['--working-dir'] = $options['working-dir'];
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