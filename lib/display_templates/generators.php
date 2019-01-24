<?php
/**
 * Библиотека содержит функции для генерации HTML элементов на базе XML/XSLT
 */

function getXMLFromString($xml) {
    $document = new DOMDocument();
    $document->loadXML($xml);
    return $document ? $document : false;
}

function getXSLFromFile($xsl_path) {
    $xsl = new DOMDocument;
    $xsl->load($xsl_path);
    return $xsl ? $xsl : false;
}

function generateHTML($xml, $xsl_path) {
    $document = getXMLFromString($xml);
    $xsl = getXSLFromFile($xsl_path);

    $processor = new XSLTProcessor;
    $processor->importStyleSheet($xsl);
    $result = $processor->transformToXML($document);
    return $result ? $result : false;
}

/**
function generateForm($xml) {
    return generateHTML($xml, 'form_generator.xsl');
}
 */