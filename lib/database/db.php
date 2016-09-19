<?php
/**
 * Библиотека содержит функции для работы с БД: подключение, исполнение запросов
 */

/**
 * Функция подключения к БД с помощью настроек, указанных в файле конфигурации
 *
 * @param string $config_path Путь к файлам настроек, по умолчанию определяется в константах defaults.php
 */
function connectDB($config_path = SETTINGS_PATH) {
    try {
        global $conn;
        $conn = new PDO("mysql:host=".getConfig('servername', $config_path).";dbname=".getConfig('dbname', $config_path), getConfig('username', $config_path), getConfig('password', $config_path));
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if (DEBUG_MODE) {
            echo "Connected successfully\n";
        }
    } catch(PDOException $e) {
        return false;
        if (DEBUG_MODE) {
            echo "Connection failed: ".$e->getMessage();
        }
    }
}

/**
 * Функция уничтожает текущее подключение к БД
 */
function closeConnection() {
    global $conn;
    $conn = null;
    if (DEBUG_MODE) {
        echo "Connection closed\n";
    }
}

/**
 * Функция подключается к БД и осуществляет изъятие информации о таблицах в БД, указанной в конфигурации
 *
 * @return mixed Массив данных с таблицами в БД
 */
function getSchemaTables() {
    connectDB();
    global $conn;
    $dbname = getConfig('dbname');
    $query = $conn->query("SELECT table_name, column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '$dbname' ORDER BY table_name, ordinal_position");
    return $query->fetchAll();
    closeConnection();
}

/**
 * Функция подключается к БД и создаёт указанную в настройках БД
 */
function createDB() {
    try {
        $dbname = getConfig('dbname');
        $dbh = new PDO("mysql:host=".getConfig('servername'), getConfig('username'), getConfig('password'));
        $dbh->exec("CREATE SCHEMA IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8;");
        if (DEBUG_MODE) {
            echo "Database $dbname was created successfully\n";
        }
        $dbh = null;
    } catch (PDOException $e) {
        die("DB ERROR: ". $e->getMessage());
    }
}

/**
 * Функция подключается к БД и уничтожает указанную в настройках БД
 */
function dropDB() {
    try {
        $dbname = getConfig('dbname');
        $dbh = new PDO("mysql:host=".getConfig('servername'), getConfig('username'), getConfig('password'));
        $dbh->exec("DROP SCHEMA IF EXISTS `$dbname`;");
        if (DEBUG_MODE) {
            echo "Database $dbname was destroyed successfully\n";
        }
        $dbh = null;
    } catch (PDOException $e) {
        die("DB ERROR: ". $e->getMessage());
    }
}

/**
 * Функция исполняет запрос к БД, переданный в параметрах
 *
 * @param string $query Запрос в БД
 * @param string $config_path Путь к настройкам, по умолчанию определяется константой в defaults.php
 * @return bool
 */
function performQuery($query, $config_path = SETTINGS_PATH) {
    connectDB($config_path);
    global $conn;
    try {
        $conn->query($query);
        return true;
    } catch(PDOException $e) {
        if (DEBUG_MODE) {
            echo "Failed: ".$e->getMessage()."\n";
        }
        return false;
    }
    closeConnection();
}

/**
 * Функция исполняет запрос к БД, переданный в параметрах с возвращением именованного массива значений
 *
 * @param string $query Запрос в БД
 * @param string $config_path Путь к настройкам, по умолчанию определяется константой в defaults.php
 * @return bool|array False если запрос не удался или нечего возвращать, иначе именованный массив
 */
function performQueryFetch($query, $config_path = SETTINGS_PATH) {
    connectDB($config_path);
    global $conn;
    try {
        $sth = $conn->prepare($query);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : false;
    } catch(PDOException $e) {
        if (DEBUG_MODE) {
            echo "Failed: ".$e->getMessage()."\n";
        }
        return false;
    }
    closeConnection();
}