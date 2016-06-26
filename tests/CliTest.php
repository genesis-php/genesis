<?php

namespace Genesis\Tests;


use Genesis;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 *
 * Tests only genesis executable file
 */
class CliTest extends BaseTest
{

	protected $executable;


	public function setUp()
	{
		$this->executable = __DIR__ . '/../genesis';
	}


	public function testExistingTask()
	{
		$result = $this->execute('info');
		$this->assertEquals(0, $result['code']);
		$line = $result['output'][6];
		$this->assertContains('Running [info]', $line);
	}


	public function testErrorTask()
	{
		$result = $this->execute('error');
		$this->assertEquals(255, $result['code']);
		$line = $result['output'][6];
		$this->assertContains('Running [error]', $line);
		$line = $result['output'][7];
		$this->assertContains('Exited with ERROR', $line);
	}


	public function testEmpty()
	{
		// executes runDefault()
		$result = $this->execute('');
		$this->assertEquals(0, $result['code']);
		$line = $result['output'][6];
		$this->assertContains('Running [default]', $line);
	}


	protected function execute($command, array $options = [])
	{
		$opts = [
			'--working-dir' => '01',
			'--colors' => '0',
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