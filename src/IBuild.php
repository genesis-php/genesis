<?php

namespace Genesis;


/**
 * Created by Adam Bisek <adam.bisek@gmail.com>
 */
interface IBuild
{

	public function __construct(Config\Container $container, array $arguments = NULL);

	public function setup();

	public function runDefault();

}