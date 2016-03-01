<?php

// composer loader
require_once __DIR__ . '/../vendor/autoload.php';

// genesis loader
require_once __DIR__ . '/../Loader.php';
$loader = new Genesis\Loader();
$loader->register();