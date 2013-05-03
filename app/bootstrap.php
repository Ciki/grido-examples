<?php

use \Nette\Application\Routers\Route;

// Load Nette Framework
require __DIR__ . '/../libs/autoload.php';


// Configure application
$configurator = new Nette\Config\Configurator;
$configurator->setTempDirectory(__DIR__ . '/../temp');


// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode();
//$configurator->setProductionMode();
$configurator->enableDebugger(__DIR__ . '/../log');


// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');


// Enable RobotLoader - this will load all classes automatically
$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->addDirectory(__DIR__ . '/../libs')
    ->register();


// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config.neon');
$container = $configurator->createContainer();


// Setup router
$uri = $container->parameters['productionMode'] ? 'example/' : '';
$container->router[] = new Route("$uri<filterRenderType>/<action>/", array(
    'presenter' => 'Example',
    'action' => 'default',
    'filterRenderType' => 'inner'
));

return $container;