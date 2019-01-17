<?php

namespace Migrator\Helpers;

function dashesToCamelCase($string, $capitalizeFirstCharacter = false) {
    $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));

    if (!$capitalizeFirstCharacter) {
        $str[0] = strtolower($str[0]);
    }

    return $str;
}

/**
 * Функция, читающая csv файл, содержащий историю миграций
 *
 * @param string $path
 * @return array Массив, состоящий из значений csv файла
 */
function readMigrations($path) {
    if (file_exists($path)) {
        $file = fopen($path, "r");
        $contents = fgetcsv($file);
        fclose($file);
        return $contents;
    } else {
        return [];
    }
}

/**
 * Функция добавляет версию миграции в файл с историей
 *
 * @param string $path Путь к файлу истории
 * @param string $version Строка, содержащая версию миграции
 */
function writeMigration($path, $version) {
    preg_match('/^.+\/(.+)$/', $path, $matches);
    if (trim(file_get_contents($path))) {
        file_put_contents($path, ",$version", FILE_APPEND);
    } else {
        file_put_contents($path, $version);
    }
}

/**
 * Функция очищает файл с историей миграций
 *
 * @param string $path Путь к файлу истории миграций
 */
function clearMigrations($path) {
    preg_match('/^.+\/(.+)$/', $path, $matches);
    if (file_exists($path)) {
        file_put_contents($path, "");
    }
}

/**
 * Функция удаляет последнюю версию из файла истории миграций
 *
 * @param $path string Путь к файлу с историей миграций
 */
function deleteLastMigration($path) {
    preg_match('/^.+\/(.+)$/', $path, $matches);
    if (count(readMigrations($path)) > 1) {
        $str = preg_replace('/,[a-zA-Z0-9._-]+$/', '', file_get_contents($path));
        file_put_contents($path, $str);
    } else {
        clearMigrations($path);
    }
}