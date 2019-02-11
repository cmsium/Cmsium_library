<?php
ini_set('phar.readonly', '0');

$buildRoot = __DIR__;

if (!is_dir("$buildRoot/build")) {
    mkdir("$buildRoot/build");
}

$phar = new Phar($buildRoot . '/build/combiner.phar', 0, 'combiner.phar');
$phar->buildFromDirectory($buildRoot.'/src');
$phar->setStub("#!/usr/bin/env php\n" . $phar->createDefaultStub("combiner.php"));

rename($buildRoot.'/build/combiner.phar', $buildRoot.'/build/combiner');
system("chmod +x $buildRoot/build/combiner");
echo 'Built Successfully!'.PHP_EOL;