<?php


//Функции проверки перед валидацией    
//------------------------------------



/*
*Получить массив отвалидированных значений
*
*@param array $data Данные; 
*@param array $mask Маска;
*
*@return array Массив отвалидированных данных;

function ValidateData ($data,$mask){
    $res = array();
    foreach ($data as $key => $value){
        if ($value)
            $res[$key] = $value; 
    }
    return $res;
}




*Получить массив неотвалидированных значений
*
*@param array $data Данные; 
*@param array $mask Маска;
*
*@return array Массив неотвалидированных типов;

function NotValidateData ($data,$mask){
    $res = array();
    foreach ($data as $key => $value){
        if (!$value)
            $res[] = $key; 
    }
    return $res;
}
*/



/**
*Валидирует массив данных из $_POST по маске
*
*@param array $mask Маска;
 * @param array $array Массив для валидации;
*
*@return array|false Отвалидированный массив;
*/
function ValidateByMask ($array,$mask){
    $data = array();
    foreach ($mask as $key => $value){
        $res = Validate($array,$key,array('func' => GetFuncFromMask($key,$mask),
                                   'props' => GetPropsFromMask($key,$mask),
                                   'required' => GetReqsFromMask ($key,$mask)));
        if ($res === false)
            return false;
        $data[$key] = $res;
    }
    return $data;
}




/**
*Отвалидировать параметр
*
 * @param array $array Массив для валидации
*@param string $paramName Имя валидируемого параметра
*@param array $paramProps Параметры типа
*
*@return mixed|NULL Отвалидированный параметр 
*/
function Validate($array, $paramName, $paramProps){
    if (isset($array[$paramName])){
        if (empty($array[$paramName]))
            if ($paramProps['required'])
                return false;
            else
                return NULL;
        $check = Check( $paramProps['func'],
                        $array[$paramName],
                        $paramProps['props']);
        if (!$check)
            return false;
        return $check;
        }
    elseif (!$paramProps['required'])
        return NULL;
    else
        return false;
}



/**
*Прогоняет через нужную валидацию
*
*@param string $Type Тип; 
*@param string $Value Значение; 
*@param array $props Дополнительные параметры;
*
*@return mixed[]|false Валидированое значение;
*/
function Check ($type,$value,$props){
    return @$type($value,$props);
}



/**
*Получить параметры типа по маске
*
*@param string $Type Тип;  
*@param array $mask Маска;
*
*@return array Параметры типа;
*/
function GetPropsFromMask ($type,$mask){
    $props = array();
    if (empty($mask[$type]) or !isset($mask[$type]['props']))
        return $props;
    foreach ($mask[$type]['props'] as $key => $value){
        $props[$key] = $value; 
    }
    return $props;
}



/**
*Получить параметры обязательности по маске
*
*@param string $Type Тип;  
*@param array $mask Маска;
*
*@return boolean Флаг обязательности;
*/
function GetReqsFromMask ($type,$mask){
    if (empty($mask[$type]) or !isset($mask[$type]['required'])){
        return false;
    }
    return $mask[$type]['required'];
}



/**
*Получить функцию валидации по маске
*
*@param string $Type Тип;  
*@param array $mask Маска;
*
*@return array Набор функций для валидации;
*/
function GetFuncFromMask ($type,$mask){
    $func = "";
    if (empty($mask[$type]) or !isset($mask[$type]['func'])){
        return $func;
    }
    return $mask[$type]['func'];
}

?>