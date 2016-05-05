<?php

require_once __DIR__ . '/TestBuild.php';

$container = new Genesis\Config\Container();
$container->setParameters([
	'myTestBootstrapKey' => 'val',
]);
$container->addService('myService', new \ArrayObject);
return $container;