<?php
/**
 * Библиотека содержит функции для работы с конвертацией форматов данных (JSON, XML, CSV)
 */

// Parse raw data with mask and content

/**
 * Функция получает значение маски из сырых данных в формате CSV
 *
 * @param string $csv Данные в формате CSV
 * @return array|bool Значение маски в виде массива, иначе false
 */
function getMaskCSV($csv) {
    $data = str_getcsv($csv, "\n");
    $mask = explode(";;", $data[0]);
    return $mask;
}

/**
 * Функция преобразовывает сырые данные в формате CSV в PHP массив для дальнейшей обработки
 *
 * Разделить значений для каждой строки должен иметь вид ";;"
 *
 * @param string $csv Данные в формате CSV
 * @return array|bool Данные в виде массива, иначе false
 */
function getCSVContent($csv) {
    $data = str_getcsv($csv, "\n");
    array_shift($data);
    return $data;
}

/**
 * Функция получает значение маски из сырых данных в формате JSON
 *
 * @param string $json Данные в формате JSON
 * @return array|bool Значение маски в виде массива, иначе false
 */
function getMaskJSON($json) {
    $data = json_decode($json, true);
    $root = array_keys($data)[0];
    $mask = $data[$root][0];
    return $mask;
}

/**
 * Функция преобразовывает сырые данные в формате JSON в PHP массив для дальнейшей обработки
 *
 * @param string $json Данные в формате JSON
 * @return array|bool Данные в виде массива, иначе false
 */
function getJSONContent($json) {
    $data = json_decode($json, true);
    $root = array_keys($data)[0];
    $data_array = $data[$root];
    array_shift($data_array);
    return $data_array;
}

// Something to array

/**
 * Функция преобразовывает строку JSON в формат именнованого PHP массива
 *
 * @param string $json Данные в формате JSON
 * @return array|bool Возвращает массив данных, иначе false
 */
function JSONToArray($json) {
    return json_decode($json, true);
}

/**
 * Функция преобразовывает данные в формате JSON в именованный массив на основе маски
 *
 * Правильное оформление строки JSON для функции можно найти в документации к данной библиотеке
 *
 * @param string $json Данные в формате JSON
 * @return array|bool Возвращает массив данных, иначе false
 */
function listJSONToArray($json) {
    $mask = getMaskJSON($json);
    $data = getJSONContent($json);
    foreach ($data as $row) {
        foreach ($row as $index=>$value) {
            $resulted_row[$mask[$index]] = $value;
        }
        $result[] = $resulted_row;
    }
    return $result;
}

/**
 * Функция представляет строку XML в виде DOM объекта
 *
 * @param string $xml Данные в формате XML
 * @return DOMElement Объект root-элемента
 */
function getXMLRootNode($xml) {
    $object = new DOMDocument();
    $object->loadXML($xml);
    $root = $object->documentElement;
    return $root;
}

/**
 * Рекурсивная функция для составления именованного массива из DOM объекта
 *
 * @param DOMElement $root Объект root-элемента
 * @return array|mixed Массив данных, либо объект для дальнейших итераций
 */
function XMLObjectToArray($root) {
    $result = [];

    if ($root->hasChildNodes()) {
        $children = $root->childNodes;
        if ($children->length == 1) {
            $child = $children->item(0);
            if ($child->nodeType == XML_TEXT_NODE) {
                $result['_value'] = $child->nodeValue;
                return count($result) == 1 ? $result['_value'] : $result;
            }
        }
        $groups = [];
        foreach ($children as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = XMLObjectToArray($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = XMLObjectToArray($child);
                }
            }
        }
    }

    return $result;
}

/**
 * Функция преобразовывает данные в формате XML в именованный массив
 *
 * @param string $xml Данные в формате XML
 * @return array|bool Возвращает массив данных, иначе false
 */
function XMLToArray($xml) {
    $root = getXMLRootNode($xml);
    $result = XMLObjectToArray($root);
    return $result;
}

/**
 * Функция преобразовывает данные в формате CSV в именованный массив на основе маски
 *
 * Правильное оформление строки CSV для функции можно найти в документации к данной библиотеке
 *
 * @param string $csv Данные в формате CSV
 * @return array|bool Возвращает массив данных, иначе false
 */
function CSVToArray($csv) {
    $mask = getMaskCSV($csv);
    $data = getCSVContent($csv);
    foreach($data as $line) {
        $row = explode(";;", $line);
        foreach ($row as $index=>$value) {
            $resulted_row[$mask[$index]] = $value;
        }
        $result[] = $resulted_row;
    }
    return $result;
}

// Array to something

/**
 * Функция преобразовывает данные из массива в формат JSON
 *
 * @param array $content Исходный массив данных
 * @return string|bool Данные в JSON формате, либо false
 */
function arrayToJSON($content) {
    return json_encode($content);
}

function simpleXMLObjectToXML($array, &$base) {
    foreach($array as $key => $value) {
        if(is_array($value)) {
            if(!is_numeric($key)){
                $subnode = $base->addChild("$key");
                simpleXMLObjectToXML($value, $subnode);
            }else{
                $subnode = $base->addChild("item$key");
                simpleXMLObjectToXML($value, $subnode);
            }
        }else {
            $base->addChild("$key",htmlspecialchars("$value"));
        }
    }
}

function arrayToXML($array, $root = false) {
    if ($root) {
        $base = new SimpleXMLElement("<?xml version=\"1.0\"?><$root></$root>");
    } else {
        $base = new SimpleXMLElement("<?xml version=\"1.0\"?><root></root>");
    }
    simpleXMLObjectToXML($array, $base);
    return $base->asXML();
}

/**
 * Функция преобразует массив данных в CSV
 *
 * @param array $array Исходный массив данных
 * @return string Строка в CSV
 */
function arrayToCSV($array) {
    $result = '';
    foreach ($array as $row) {
        $result = $result.implode(";;", $row)."\n";
    }
    return $result;
}

// ADDITIONAL FUNCTIONS

/**
 * Функция преобразовывает данные из формата JSON в формат XML
 *
 * @param string $json Строка, содержащая данные в JSON формате
 * @return  string|bool Данные в XML, либо false
 */
function JSONToXML($json) {
    $options = array(
        "addDecl"   => true,
        "encoding"  => "UTF-8",
        "indent"    => '  ',
        "rootName"  => 'root'
    );
    $serializer = new XML_Serializer($options);
    $object = json_decode($json);

    if ($serializer->serialize($object)) {
        return $serializer->getSerializedData();
    } else {
        return false;
    }
}

/**
 * Функция преобразовывает данные из формата JSON в формат CSV
 *
 * Многоуровневые JSON строки не поддерживаются
 *
 * @param string $json Строка, содержащая данные в JSON формате
 * @return bool|string Данные в CSV, либо false
 */
function JSONToCSV($json) {
    if (!$object = json_decode($json, true)) {
        return false;
    }

    $line = "";
    foreach ($object as $row) {
        $line = $line.join(",", $row)."\n";
    }
    return $line;
}

/**
 * Функция преобразовывает данные из формата JSON в формат CSV
 *
 * Многоуровневые XML узлы не поддерживаются
 *
 * @param string $xml трока, содержащая данные в XML формате
 * @return bool|string Данные в CSV, либо false
 */
function XMLToCSV($xml) {
    if (!$object = simplexml_load_string($xml)) {
        return false;
    }

    $line = "";
    foreach ($object->children() as $item)
    {
        $line = $line.join(",", get_object_vars($item))."\n";
    }
    return $line;
}