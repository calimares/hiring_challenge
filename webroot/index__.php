<?php



/**
 * Load composer libraries
 */
require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/includes.php';

/**
 * Load .env
 */
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

\microf\Dispatcher::getInstance()->dispatch();