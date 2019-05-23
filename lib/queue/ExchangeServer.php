<?php
namespace Queue;

define('ROOTDIR', __DIR__);
foreach (glob(ROOTDIR."/exceptions/*.php") as $name){
    include $name;
}
foreach (glob(ROOTDIR."/queues/*.php") as $name){
    include $name;
}
foreach (glob(ROOTDIR."/tasks/*.php") as $name){
    include $name;
}
include ROOTDIR.'/ManifestParser.php';

$manager = new Queues\QueueManager();
$parser = new ManifestParser();
$queues = $parser->getQueues();
foreach ($queues as $name => $queue_info){
    $queue = new Queues\QueueClient($name, $queue_info->host, $queue_info->port);
    $manager->registerQueue($queue);
}


//TODO normal config
$ini = parse_ini_file(ROOTDIR."/config/exchange.ini");
$server = new \swoole_server($ini['host'], $ini['port']);
//$server->on('connect', function($server, $fd){
//    TODO logs
//});

$server->on('receive', function($server, $fd, $from_id, $message) use ($manager) {
    try {
        $message = json_decode($message, true);
        $command = $message[0];
        switch ($command) {
            case 'push':
                if (isset($message[3])) {
                    $mode = $message[3];
                } else {
                    $mode = null;
                }
                $result = $manager->route($message[1], $message[2], $mode);
                $server->send($fd, json_encode($result));
                break;
            case 'headers':
                $server->send($fd, json_encode($manager->getHeaders()));
                break;
            case 'queue':
                $queue = $message[1];
                $server->send($fd, json_encode($manager->getQueue($queue)->getInfo()));
        }
    } catch (\Exception $e){
        //TODO logs
        var_dump($e->getMessage());
        $server->send($fd, json_encode(false));
    }

});

//$server->on('close', function($server, $fd){
//    TODO logs
//});

$server->start();