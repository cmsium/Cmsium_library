<?php

namespace Combiner\Helpers;

function dashesToCamelCase($string, $capitalizeFirstCharacter = false) {
    $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));

    if (!$capitalizeFirstCharacter) {
        $str[0] = strtolower($str[0]);
    }

    return $str;
}

function readTextFile($path) {
    if (is_file($path)) {
        return file_get_contents($path);
    } else {
        return false;
    }
}

function removeDirectory($dir) {
    foreach(scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) continue;
        if (is_dir("$dir/$file")) removeDirectory("$dir/$file");
        else unlink("$dir/$file");
    }
    rmdir($dir);
}

function recursiveCopy($src, $dst, $filter = false) {
    $dir = opendir($src);
    @mkdir($dst);

    while(false !== ($file = readdir($dir))) {
        if (( $file != '.' ) && ( $file != '..' ) && ( $file != $filter )) {
            if ( is_dir($src .'/'.$file) ) {
                recursiveCopy($src.'/'.$file, $dst.'/'.$file);
            }
            else {
                copy($src.'/'.$file, $dst.'/'.$file);
            }
        }
    }
    closedir($dir);
}

function clear($destinationDir) {
    if (is_dir($destinationDir)) {
        removeDirectory($destinationDir);
    }
    if (is_file(MAINDIR.'/combiner.lock')) {
        unlink(MAINDIR.'/combiner.lock');
    }
}