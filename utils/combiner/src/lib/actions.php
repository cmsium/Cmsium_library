<?php

namespace Combiner\Actions;

use Combiner\Config;
use Combiner\Git;
use Combiner\Helpers;

function install($package = false) {
    echo 'Starting install...'.PHP_EOL;
    $configPath = MAINDIR.'/combiner.conf.json';
    $config = Config\getConfig($configPath);
    echo 'Config parsed successfully...'.PHP_EOL;

    if ($package) {
        $directories = [$package];
        $config['libraries'] = $directories;
    } else {
        $directories = $config['libraries'];
    }

    // TODO: Check if new libs appeared, if so - install only them
    if (is_dir(MAINDIR."/{$config['destination_dir']}")) {
        die('Libraries already installed. Use update!'.PHP_EOL);
    }

    $dirsWithDependencies = Git\pullDirectories($config['git_url'], $directories, $config['destination_dir']);
    echo 'Libraries pulled successfully!'.PHP_EOL;

    // Rewrite config to add dependencies...
    $config['libraries'] = $dirsWithDependencies;

    $lastCommitHash = Git\getLastCommitHash($config['git_url']);
    $config['last_hash'] = $lastCommitHash;
    if (!Config\writeLock(MAINDIR, $config)) {
        die('Can not write lock file!'.PHP_EOL);
    }
    echo 'Lock file created...'.PHP_EOL;

    echo 'Generating autoload file...'.PHP_EOL;
    if (!Config\generateAutoload($config['destination_dir'], $config['libraries'])) {
        die('Could not create autoload file!'.PHP_EOL);
    }

    echo 'Installed successfully!'.PHP_EOL;
}

function update() {
    echo 'Starting update...'.PHP_EOL;
    $lockPath = MAINDIR.'/combiner.lock';
    $lockConfig = Config\getConfig($lockPath);

    echo 'Checking commit hashes...'.PHP_EOL;
    $lastCommitHash = Git\getLastCommitHash($lockConfig['git_url']);
    if ($lastCommitHash === $lockConfig['last_hash']) {
        die('Project is up to date!'.PHP_EOL);
    }
    $lockConfig['last_hash'] = $lastCommitHash;

    $libsDir = MAINDIR."/{$lockConfig['destination_dir']}";
    Helpers\removeDirectory($libsDir);
    echo 'Old library directory cleared.'.PHP_EOL;

    Git\pullDirectories($lockConfig['git_url'], $lockConfig['libraries'], $lockConfig['destination_dir']);
    echo 'Libraries pulled successfully!'.PHP_EOL;

    if (!Config\writeLock(MAINDIR, $lockConfig)) {
        die('Can not update lock file!'.PHP_EOL);
    }
    echo 'Lock file updated...'.PHP_EOL;

    echo 'Updating autoload file...'.PHP_EOL;
    if (!Config\generateAutoload($lockConfig['destination_dir'], $lockConfig['libraries'])) {
        die('Could not create autoload file!'.PHP_EOL);
    }

    echo 'Update done!'.PHP_EOL;
}

function clear() {
    echo 'Starting clean-up...'.PHP_EOL;
    $configPath = MAINDIR.'/combiner.conf.json';
    $config = Config\getConfig($configPath);

    echo 'Config parsed successfully...'.PHP_EOL;

    Helpers\clear(MAINDIR.'/'.$config['destination_dir']);
    echo 'Cleared!'.PHP_EOL;
}

function help() {
    $output = Helpers\readTextFile(ASSETS_PATH.'/help.txt');
    if ($output) {
        echo $output;
    } else {
        die('Could not open manual!'.PHP_EOL);
    }
}