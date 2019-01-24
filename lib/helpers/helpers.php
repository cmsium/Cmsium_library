<?php
/**
 * Бибилиотека содержит функции-помощники, используемые в основных функциях библиотек
 */

/**
 * Функция возвращает имя таблицы авторизации на основе передаваемого типа авторизации
 *
 * @param string $type Тип авторизации, может быть phone, username, email
 * @return bool|string Имя таблицы, иначе false
 */
function getTableName($type) {
    switch ($type) {
        case 'phone': return 'phones'; break;
        case 'firstname':
        case 'middlename':
        case 'lastname':
        case 'birth_date':
        case 'birthplace':
        case 'username': return 'user_properties'; break;
        case 'email': return 'emails'; break;
        default: return false;
    }
}