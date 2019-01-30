<?php
define('MAINDIR', dirname(preg_replace('#^phar://#', '', __DIR__)));
define('APPDIR', 'phar://combiner.phar');
define('ASSETS_PATH', APPDIR.'/assets');

// Load all from lib
require APPDIR.'/lib/actions.php';
require APPDIR.'/lib/config.php';
require APPDIR.'/lib/git.php';
require APPDIR.'/lib/helpers.php';

use function Combiner\Helpers\dashesToCamelCase;

$rawCommand = $argv[1];
$command = '\\Combiner\\Actions\\'.dashesToCamelCase($rawCommand);
$argument = $argv[2] ?? false;

if (function_exists($command)) {
    $command($argument);
} else {
    echo 'Command not found!'.PHP_EOL;
}
