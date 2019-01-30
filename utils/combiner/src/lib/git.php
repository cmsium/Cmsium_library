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

    if (!`git init $tempRepoDir`) {
        die("Can not initialize a repository in $tempRepoDir".PHP_EOL);
    }

    $stringDirs = implode(PHP_EOL, $directories).PHP_EOL;
    $sparseFilePath = $tempRepoDir.'/.git/info/sparse-checkout';

    if (!file_put_contents($sparseFilePath, $stringDirs)) {
        die('Can not write sparse-checkout file'.PHP_EOL);
    }
    `cd $tempRepoDir && git config core.sparsecheckout true`;

    if (!`cd $tempRepoDir && git remote add origin -f $url`) {
        die('Can not assign a remote url to temp repo!'.PHP_EOL);
    }

    `cd $tempRepoDir && git pull origin master`;

    $destinationDir = MAINDIR."/$destination";
    Helpers\recursiveCopy($tempRepoDir, $destinationDir, '.git');

    deleteTempDirectory();
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