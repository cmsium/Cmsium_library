<?php
namespace Errors;
use Lib\Converters as conv;
use Lib\Headers as header;

function getConfig($config_name) {
    static $config_arr=[];
    $path = \dirname(__FILE__).'/config.ini';
    if (\file_exists($path)) {
        if ($config_arr == null) {
            $config = \parse_ini_file($path);
            $config_arr = $config;
        }
        return $config_arr[$config_name];
    } else {
        renderError(NO_FILE_FOUND);
    }
}


    function renderError($exception, $header_only = true, $additional_info = null) {
        header\respondCustom(['header' => 'App-Exception', 'value' => $exception['code']]);
        if ($header_only === true) {
            \ob_clean();
            exit;
        } else {
            switch (getConfig('message_output_format')) {
                case 'text':
                    \ob_clean();
                    echo $exception['text'];
                    exit;
                case 'xml':
                    \ob_clean();
                    $xml_array = $exception + $additional_info;
                    $xml = conv\arrayToXML($xml_array, 'exception');
                    header\respondXML();
                    echo $xml;
                    exit;
                case 'json':
                    \ob_clean();
                    $json_array = $exception + $additional_info;
                    $json = conv\arrayToJSON($json_array);
                    header\respondJSON();
                    echo $json;
                    exit;
                default:
                    return false;
            }
        }
    }
