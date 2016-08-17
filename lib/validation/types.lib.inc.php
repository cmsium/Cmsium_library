<?php
//Функции валидаций разных типов
//--------------------------------



/**
*Проверить на соответствие шаблону
*
*@param string $pattern Шаблон; 
*@param string $value Проверяемая строка;
*
*@return string|false Валидированное значение;
*/
function Preg ($pattern,$value){
    if (preg_match($pattern,$value))
        return $value;
    return false;
}



/**
*Проверяет как integer
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;
*/
function Int($value,$props){
    $res = is_integer($value);
    if (!$res)
        return false;
    return $value;
}



/**
*Проверяет как беззнаковый int    
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function UnsignedInt ($value,$props) {
    $res = Int($value,$props);
    if ($res)
        if ($res >= 0)
            return $res;
    return false;
  }



/**
*Ппроверяет как int в заданном диапозоне
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function RangedInt ($value,$props){
    $value = Int($value,$props);
    if ($value >= $props['min'] 
    and $value <= $props['max'])
        return $value;
    return false;
}



/**
*Проверяет int на совпадение из списка  
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function IntFromList ($value,$props) {
    $res = Int($value,$props);
    if ($res)
        return ValueFromList($res,$props);
    return false;
  }



/**
*Проверяет на boolean 
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function Bool ($value,$props){
    if ($value === "true" or $value === 1) return true;
    if ($value === "false" or $value === 0) return false;
}



/**
*Проверяет имя латинскими буквами и цифрами
*(начиная с буквы), заданной длинны
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function LatinName ($value,$props){
    $pattern = "/^[A-Z][\w-]{{$props['min']},{$props['max']}}$/";
    if (Preg ($pattern,$value))
        switch ($props['output']){
            case 'string':
                return $value;
                break;
            case 'binary':
                return StrToBinS ($value);
                break;
            case 'md5':
                return md5($value);
                break;

            default:
                return $value;
        }
    return false;
}



/**
*Проверяет текст заданной длинны
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function Text ($value,$props){
    $pattern = "/^[\w-№\s\.:]{{$props['min']},{$props['max']}}$/";
    if (Preg ($pattern,$value))
        switch ($props['output']){
            case 'string':
                return $value;
                break;
            case 'binary':
                return StrToBinS ($value);
                break;
            case 'md5':
                return md5($value);
                break;

            default:
                return $value;
        }
    return false;

}



/**
*Проверяет как цифры и буквы заданной длинны
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function AlphaNumeric ($value,$props){
    $pattern = "/^[\w\d]{{$props['min']},{$props['max']}}$/";
    if (Preg ($pattern,$value))
        switch ($props['output']){
            case 'string':
                return $value;
                break;
            case 'binary':
                return StrToBinS ($value);
                break;
            case 'md5':
                return md5($value);
                break;

            default:
                return $value;
        }
    return false;

}


/**
 * Проверяет как md5 хэш
 * @param string $value Проверяемая строка;
 * @param array $props Параметры типа;
 *
 * @return string|false Валидированное значение;
 */
function Md5Type ($value, $props){
    $pattern = "/^[\dA-F]{32}$/";
    if (Preg ($pattern,$value))
        switch ($props['output']){
            case 'string':
                return $value;
                break;
            case 'binary':
                return StrToBinS ($value);
                break;

            default:
                return $value;
        }
    return false;

}



/**
*Проверяет IPv4 адрес
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function IPv4 ($value,$props){
    $pattern = "/^([\d]{1,3})\.([\d]{1,3})\.([\d]{1,3})\.([\d]{1,3})(\/([\d]{1,3}))?$/";
    if (!Preg($pattern, $value))
        return false;
    $res = preg_split("/\./", $value);
    foreach ($res as &$groupValue) {
        $groupValue = (INT)$groupValue;
        if ($groupValue < 0 or
            $groupValue > 255
        )
            return false;
    }
    switch ($props['output']) {
        case 'string':
            return $value;
            break;
        case 'binary':
            return StrToBinS($value);
            break;
        case 'md5':
            return md5($value);
            break;
        case 'int':
            return IPv4toInt($value);
        default:
            return $value;

    }
}


/**
 *Проверяет IPv4integer адрес
 *
 *@param string $value Проверяемая строка;
 *@param array $props Параметры типа;
 *
 *@return string|false Валидированное значение;

 */
function IPv4Int ($value,$props){
    $pattern = "/^[\d]{0,10}$/";
    if (Preg ($pattern,$value))
        switch ($props['output']){
            case 'string':
                return IntToIPv4($value);
                break;
            default:
                return $value;
        }
    return false;

}


/**
*Проверяет URL
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function URL ($value,$props){
    if (filter_var($value,FILTER_VALIDATE_URL))
        switch ($props['output']){
            case 'string':
                return $value;
                break;
            case 'binary':
                return StrToBinS ($value);
                break;
            case 'md5':
                return md5($value);
                break;

            default:
                return $value;
            }
    return false;

}



/**
*Проверяет e-mail
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function E_Mail ($value,$props){
    $pattern = "/^([\w\d-\.?]{1,})\@([\w\d-\.?]{1,})$/";
    if (Preg ($pattern,$value))
        switch ($props['output']){
            case 'string':
                return $value;
                break;
            case 'binary':
                return StrToBinS ($value);
                break;
            case 'md5':
                return md5($value);
                break;

            default:
                return $value;
        }
    return false;

}



/**
*Проверяет как послледовательность цифр
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function StrNumbers ($value,$props){
    $pattern = "/^\d{{$props['min']},{$props['max']}}$/";
    if (Preg ($pattern,$value))
        switch ($props['output']){
            case 'string':
                return $value;
                break;
            case 'binary':
                return StrToBinS ($value);
                break;
            case 'md5':
                return md5($value);
                break;
            case 'int':
                return (INT)$value;
                break;
            default:
                return $value;
        }
    return false;

}




/**
*Проверяет на совпадение со строкой из списка
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function ValueFromList ($value,$props){
    if (!isset($props['list']))
        return false;
    if (in_array($value,$props['list']))
        return $value;
    return false;
}



/**
*Проверяет дату
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function DateType ($value,$props){
    $date = date_create_from_format($props['format'], $value);
    if ($date)
        switch ($props['output']){
            case 'string':
                return $value; 
                break;
            case 'int':
                return date_timestamp_get($date); 
                break;
            default: 
                return $value;
        }
    return false;
}



/**
*проверяет как пользовательский тип
*
*@param string $value Проверяемая строка;
*@param array $props Параметры типа;
*
*@return string|false Валидированное значение;

*/
function Custom ($value, $props){
    if (isset($props['name']))    
        return $props['name']($value,$props);
    echo "Parameter 'name' did not found <br>";
    return false;
}
?>