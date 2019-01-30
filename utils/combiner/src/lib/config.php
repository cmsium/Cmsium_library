<?php

namespace Combiner\Config;

function checkConfig($path) {
    if (!file_exists($path)) {
        die("$path file not found!".PHP_EOL);
    }

    return is_readable($path);
}

function getConfig($path) {
    if (!checkConfig($path)) {
        die('Can not read configuration file!'.PHP_EOL);
    }

    $configJson = file_get_contents($path);
    if (!($configArray = json_decode($configJson, true))) {
        die('Wrong JSON configuration notation!'.PHP_EOL);
    }
    return $configArray;
}

function writeLock($dirPath, $dataArray) {
    $filePath = $dirPath.'/combiner.lock';

    return file_put_contents($filePath, json_encode($dataArray));
}

function generateAutoload($libsDir, $libs) {
    $resultString = '<?php'.PHP_EOL.PHP_EOL;
    $autoloadFunctionTempate = file_get_contents(ASSETS_PATH.'/autoload_function.php.template');
    $autoloadRegisterTempate = file_get_contents(ASSETS_PATH.'/autoload_register.php.template');

    $resultString .= $autoloadFunctionTempate;
    foreach ($libs as $lib) {
        $resultString .= sprintf($autoloadRegisterTempate, MAINDIR."/$libsDir/$lib");
    }

    return file_put_contents(MAINDIR."/$libsDir/autoload.php", $resultString);
}