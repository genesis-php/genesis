<?php
require __DIR__ . '/../../vendor/autoload.php';
use Tester\Assert;

class NetteTester extends \Tester\TestCase
{

	public function testTrueIsTrue()
	{
		Assert::true(TRUE);
	}

}

(new \NetteTester())->runTest('testTrueIsTrue');