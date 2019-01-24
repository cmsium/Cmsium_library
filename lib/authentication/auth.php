<?php
/**
 * Библиотека содержит функции для работы с авторизацией пользователя
 */

/**
 * Функция возвращает id пользователя из БД
 *
 * @param string $bus_ticket Хэшированный пароль, по которому будет производитться поиск
 * @param string $config_path Путь к настройкам, по умолчанию определен константой в defaults.php
 * @return bool|string При нахождении пользователя строку - id пользователя, иначе false
 */
function getIdUser($bus_ticket, $config_path = SETTINGS_PATH) {
    global $conn;
    connectDB($config_path);
    $sth = $conn->prepare("SELECT id_user FROM bus_tickets WHERE ticket='$bus_ticket';");
    $sth->execute();
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    closeConnection();
    return $result ? $result['id_user'] : false;
}

/**
 * Функция генерирует хеш пароля с помощью соли (хеш строки, указанной в defaults.php)
 *
 * @param string $raw_password Сырой пароль, принятый с формы
 * @return string Хэш пароля для хранения в БД
 */
function generatePassword($raw_password) {
    $salt = md5(PASSGEN_KEYWORD);
    return md5($raw_password.$salt);
}

// REGISTRATION FUNCTIONS

// TODO: Documentation
function identifyUser($params_array, $config_path = SETTINGS_PATH) {
    global $conn;
    $table_name = 'user_properties';
    $birth_date = $params_array['birth_date'];
    $birthplace = $params_array['birthplace'];
    $result_primary = performQueryFetch("SELECT user_id FROM $table_name 
                                         WHERE birth_date='$birth_date' AND birthplace='$birthplace';", $config_path);
    // TODO: Inner select
    if ($result_primary) {
        $firstname = $params_array['firstname'];
        $middlename = $params_array['middlename'];
        $lastname = $params_array['lastname'];
        $result_secondary = performQueryFetch("SELECT user_id FROM $table_name 
                                               WHERE firstname='$firstname' AND middlename='$middlename' 
                                               AND lastname='$lastname';", $config_path);
        return $result_secondary ? true : false;
    } else {
        return false;
    }
}

/**
 * Функция выполняет запись пользователя в БД из именованного массива с его параметрами
 *
 * @param array $params_array Массив параметров пользователя дял записи
 * @param string $config_path Путь к настройкам, по умолчанию определен константой в defaults.php
 * @return bool True при успешной записи пользователя, иначе false
 */
function createUser($params_array, $config_path = SETTINGS_PATH) {
    global $conn;
    if (!identifyUser($params_array, $config_path)) {
            // Connect to db, hash password and then write to db
        $hashed_password = generatePassword($params_array['password']);
        $query_bus_ticket = "INSERT INTO bus_tickets(ticket) VALUES ('$hashed_password');";
        connectDB($config_path);
        $conn->query($query_bus_ticket);
        $user_id = $conn->lastInsertId();
        if ($user_id) {
            // Iterating through params hash
            foreach ($params_array as $key => $value) {
                $table_name = getTableName($key);
                if ($table_name == 'user_properties') {
                    $user_props_columns[] = $key;
                    $user_props_values[] = "'$value'";
                } elseif ($table_name) {
                    $query = "INSERT INTO $table_name($key, user_id) VALUES ('$value', $user_id);";
                    if (!performQuery($query, $config_path)) {
                        return false;
                    }
                }
            }
            $query_user_props = "INSERT INTO user_properties(" . implode(", ", $user_props_columns) . ", user_id) 
                                 VALUES(" . implode(", ", $user_props_values) . ", $user_id);";
            if (!performQuery($query_user_props, $config_path)) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// REGISTRATION FUNCTIONS END

// AUTHENTICATION FUNCTIONS

/**
 * Функция проверяет наличие пользователя в БД и верность его параметров
 *
 * @param string $type Тип идентификатора, может быть username, email, phone
 * @param string $identifier Идентификатор, по которому происходит поиск пользователя в БД (имя пользователя, телефон и т.д.)
 * @param string $raw_password Пароль, введеный пользователем в форму
 * @param string $config_path Путь к настройкам, по умолчанию определен константой в defaults.php
 * @return bool Наличие верного пользователя в БД, true при успешном нахождении, иначе false
 */
function checkUserPresence($type, $identifier, $raw_password, $config_path = SETTINGS_PATH) {
    // Checks if user exists in the database. Returns boolean
    global $conn;
    $table_name = getTableName($type);
    $hashed_password = generatePassword($raw_password);
    $query = "SELECT b.id_user, b.ticket FROM bus_tickets AS b
              INNER JOIN $table_name AS e ON b.id_user = e.user_id
              WHERE e.$type = '$identifier' AND b.ticket = '$hashed_password';";
    $result = performQueryFetch($query, $config_path);
    return $result ? $result : false;
}

/**
 * Функция генерирует токен авторизации, алгоритм md5
 *
 * Генерируется на основе идентификатора пользователя и времени авторизации
 *
 * @param string $user_id ID пользователя в БД
 * @return string Хэш md5
 */
function generateToken($user_id) {
    $base_string = $user_id.time();
    return md5($base_string);
}

/**
 * Функция создает сессию с помощью генерации токена авторизации
 *
 * @param string $user_id Идентификатор пользователя, используется в генерации токена
 * @return bool True при успешном создании сессии
 */
function generateSession($user_id) {
    session_start();
    $_SESSION['token'] = generateToken($user_id);
    return true;
}

/**
 * Функция проверяет наличие сессии текущего пользователя
 *
 * @return bool True, если сессия найдена, иначе false
 */
function checkSession() {
    session_start();
    if (isset($_SESSION['token'])) {
        return true;
    } else {
        return false;
    }
}

/**
 * Функция уничтожает существующую сессию пользователя и перенаправляет на указанную страницу
 *
 * @param string $page Страница для переадресации
 */
function clearSession($page) {
    session_start();
    session_destroy();
    header("Location: $page");
}

// AUTHENTICATION FUNCTIONS END