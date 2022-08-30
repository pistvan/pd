#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

try {
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->load();
} catch (Dotenv\Exception\InvalidPathException) {
	echo "No .env file found. Please create one.\n";
	return 1;
}

$app = new Application();

$app->add(new \App\Command\ParseCommand());

$app->run();
