<?php
/**
 * Библиотека содержит функции дял работы с файлами операционной системы
 */

/**
 * Функция извлечения значений параметров из файла конфигурации
 *
 * @param string $config_name Имя конфига, который нужно прочитать
 * @param string $path Путь до файла с настройками, по умолчанию определяется константой
 * @return string Возвращает значение настройки
 */
function getConfig($config_name, $path = SETTINGS_PATH) {
    if (file_exists($path)) {
        $config = parse_ini_file($path);
        return $config[$config_name];
    } else {
        die('Config file not found!');
    }
}

/**
 * Функция для чтения контента любого файла
 *
 * @param string $path Путь к файлу для чтения
 * @return string Возвращает содержимое файла
 */
function getFileContent($path) {
    if (file_exists($path)) {
        return file_get_contents($path);
    } else {
        die('File not found!');
    }
}

/**
 * Функция для записи любого содержания из строки в файл, ничего не возвращает, сообщает о результате работы в консоль
 *
 * @param string $path Путь к файлу
 * @param string $contents Строка для записи в файл (с перезаписыванием)
 */
function writeFile($path, $contents) {
    preg_match('/^.+\/(.+)$/', $path, $matches);
    if (file_exists($path)) {
        file_put_contents($path, $contents);
        echo "File ".$matches[1]." was rewritten\n";
    } else {
        file_put_contents($path, $contents);
        echo "File ".$matches[1]." was created\n";
    }
}

/**
 * Функция, читающая csv файл, содержащий историю миграций
 *
 * @param string $path
 * @return array Массив, состоящий из значений csv файла
 */
function readMigrationHistory($path) {
    if (file_exists($path)) {
        $file = fopen($path, "r");
        return fgetcsv($file);
        fclose($file);
    } else {
        die('File not found!');
    }
}

/**
 * Функция добавляет версию миграции в файл с историей
 *
 * @param string $path Путь к файлу истории
 * @param string $version Строка, содержащая версию миграции
 */
function writeMigrationHistory($path, $version) {
    preg_match('/^.+\/(.+)$/', $path, $matches);
    if (trim(file_get_contents($path))) {
        file_put_contents($path, ",$version", FILE_APPEND);
    } else {
        file_put_contents($path, $version);
    }
    echo "File ".$matches[1]." was updated\n";
}

/**
 * Функция очищает файл с историей миграций
 *
 * @param string $path Путь к файлу истории миграций
 */
function clearMigrationHistory($path) {
    preg_match('/^.+\/(.+)$/', $path, $matches);
    if (file_exists($path)) {
        file_put_contents($path, "");
        echo "File ".$matches[1]." was cleared\n";
    } else {
        die('File not found!');
    }
}

/**
 * Функция удаляет последнюю версию из файла истории миграций
 *
 * @param $path Путь к файлу с историей миграций
 */
function deleteLastVersion($path) {
    preg_match('/^.+\/(.+)$/', $path, $matches);
    if (count(readMigrationHistory(getConfig('history_path'))) > 1) {
        $str = preg_replace('/,[a-zA-Z0-9._-]+$/', '', file_get_contents($path));
        file_put_contents($path, $str);
    } else {
        clearMigrationHistory($path);
    }
    echo "File ".$matches[1]." was updated\n";
}