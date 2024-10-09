<?php
include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/global.php';

$argv = $argv ?? $_SERVER['argv'] ?? [];

$app = App\Application::instance();
$app->run($argv);

