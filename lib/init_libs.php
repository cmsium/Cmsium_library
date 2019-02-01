<?php
/**
 * Данный скрипт предназначен для подключения всех библиотек и файлов конфигураций
 */
require_once __DIR__.'/../config/defaults.php';

/**
 * Функция производит рекурсивное подключение всех php файлов из указанной директории
 *
 * @param string $dir Путь для подключения файлов библиотек
 * @param int $depth "Глубина" рекурсивного подключения файлов из директорий
 */
function requireProcedural($dir, $depth=0) {
    // require all php files
    if ($depth === 0) {
        $scan = glob("$dir/*.p");
    } else {
        $scan = glob("$dir/*");
    }

    foreach ($scan as $path) {
        if (preg_match('/\.php$/', $path)) {
            require_once $path;
        }
        elseif (is_dir($path)) {
            requireProcedural($path, $depth+1);
        }
    }
}

function requireAutoload($dir, $className, $depth=0) {
    $scan = glob("$dir/*");

    foreach ($scan as $path) {
        $fileName = basename($path);
        if ($fileName == "$className.php") {
            require_once $path;
            break;
        }
        elseif (is_dir($path)) {
            requireAutoload($path, $className,$depth+1);
        }
    }
}

requireProcedural(ROOTDIR."/lib");

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    requireAutoload(ROOTDIR.'/lib', $className);
});