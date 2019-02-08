<?php

namespace Combiner\Git;

use Combiner\Helpers;

function checkRepo($url) {
    $result = `git ls-remote $url`;
    return $result ? $result : false;
}

function pullDirectories($url, array $directories, $destination) {
    if (!checkRepo($url)) {
        die('Can not connect to repository!'.PHP_EOL);
    }

    $tempRepoDir = createTempDirectory().'/tmp_repo';

    initRepo($tempRepoDir);

    $stringDirs = implode(PHP_EOL, $directories).PHP_EOL;
    $sparseFilePath = $tempRepoDir.'/.git/info/sparse-checkout';

    initSparseCheckout($sparseFilePath, $stringDirs, $tempRepoDir);
    pullLibraries($url, $tempRepoDir);

    // Check if any dependencies present and add them to checkout
    if ($dependencies = getDependencies($directories, $tempRepoDir)) {
        $dirsWithDependencies = array_merge($dependencies, $directories);
        $stringDirs = implode(PHP_EOL, $dirsWithDependencies).PHP_EOL;

        initSparseCheckout($sparseFilePath, $stringDirs, $tempRepoDir);
        pullLibraries($url, $tempRepoDir);
    }

    // Copy everything from temp dir to persistent dir
    $destinationDir = MAINDIR."/$destination";
    Helpers\recursiveCopy($tempRepoDir, $destinationDir, '.git');

    deleteTempDirectory();
    return $dirsWithDependencies ?? $directories;
}

function getDependencies($directories, $tempRepoDir) {
    $allDependencies = [];

    foreach ($directories as $directory) {
        $dependenciesFile = $tempRepoDir."/$directory/depends.json";
        if (file_exists($dependenciesFile)) {
            $dependencies = json_decode(file_get_contents($dependenciesFile), true);
            $allDependencies = array_merge($allDependencies, $dependencies);
        }
    }

    return array_unique($allDependencies);
}

/**
 * @param $url
 * @param string $tempRepoDir
 */
function pullLibraries($url, string $tempRepoDir) {
    `cd $tempRepoDir && git remote add origin -f $url`;
    `cd $tempRepoDir && git reset --hard && git pull origin master`;
}

/**
 * @param string $sparseFilePath
 * @param string $stringDirs
 * @param string $tempRepoDir
 */
function initSparseCheckout(string $sparseFilePath, string $stringDirs, string $tempRepoDir) {
    if (!file_put_contents($sparseFilePath, $stringDirs)) {
        die('Can not write sparse-checkout file'.PHP_EOL);
    }
    `cd $tempRepoDir && git config core.sparsecheckout true`;
}

/**
 * @param string $tempRepoDir
 */
function initRepo(string $tempRepoDir) {
    if (!`git init $tempRepoDir`) {
        die("Can not initialize a repository in $tempRepoDir".PHP_EOL);
    }
}

function createTempDirectory() {
    if (!mkdir(MAINDIR.'/.combiner')) {
        die('Can not create temporary directory!'.PHP_EOL);
    }
    return MAINDIR.'/.combiner';
}

function deleteTempDirectory() {
    if (is_dir(MAINDIR.'/.combiner')) {
        Helpers\removeDirectory(MAINDIR.'/.combiner');
    }
}

function getLastCommitHash($url) {
    if (!$lsOutput = checkRepo($url)) {
        die('Can not connect to repository!'.PHP_EOL);
    }

    return explode("\t", $lsOutput, 2)[0];
}