<?php


namespace Genesis\Tests;


use Genesis\InputArgs;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 *
 * InputArgsTest
 */
class InputArgsTest extends BaseTest
{

	public function testSettersAndGetters()
	{
		$inputArgs = new InputArgs();
		$inputArgs->setArguments(['arg1', 'arg2']);
		$this->assertEquals(['arg1', 'arg2'], $inputArgs->getArguments());
		$inputArgs->setOptions(['opt1' => 'val1', 'opt2' => 'val2']);
		$this->assertEquals(['opt1' => 'val1', 'opt2' => 'val2'], $inputArgs->getOptions());
		$this->assertEquals('val2', $inputArgs->getOption('opt2'));
		$this->assertEquals(NULL, $inputArgs->getOption('nonExistingOpt'));
	}
}