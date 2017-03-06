<?php

// Functional Test

$loader = require_once './vendor/autoload.php';

$app = G2Design\G2App::init($loader);


$instance = Website::loadFrom(__DIR__.'/theme');

$instance->attachTo($app);

$app->start();