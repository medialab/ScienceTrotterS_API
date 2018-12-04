#!/usr/bin/env php
<?php

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Database\Builder\DefaultBuilder;
use Database\Mockup\DefaultMockup;

include "database/builder/DefaultBuilder.php";
include "database/mockup/DefaultMockup.php";

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| First we need to get an application instance. This creates an instance
| of the application / container and bootstraps the application so it
| is ready to receive HTTP / Console requests from the environment.
|
*/

$app = require __DIR__.'/bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Artisan Application
|--------------------------------------------------------------------------
|
| When we run the console application, the current CLI command will be
| executed in this console and the response sent back to a terminal
| or another output device for the developers. Here goes nothing!
|
*/

/**
 * INIT DATABASE
 */
$oDefaultBuilder = new DefaultBuilder;
$oDefaultBuilder->init();

/**
  * INIT MOCKUP
  */
$oDefaultMockup = new DefaultMockup;
$oDefaultMockup->init();

