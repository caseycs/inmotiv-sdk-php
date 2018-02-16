<?php
declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv(__DIR__ . '/..');
$dotenv->load();
