<?php
namespace Queue;

define('ROOTDIR', __DIR__);
include ROOTDIR.'/ManifestParser.php';

$parser = new ManifestParser();
$queues = $parser->getQueues();
foreach ($queues as $name => $queue){
    `php QueueServer.php $name`;
}