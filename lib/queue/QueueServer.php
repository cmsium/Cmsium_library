<?php
namespace Queue;

define('ROOTDIR', __DIR__);
foreach (glob(ROOTDIR."/exceptions/*.php") as $name){
    include $name;
}
foreach (glob(ROOTDIR."/overflow/*.php") as $name){
    include $name;
}
foreach (glob(ROOTDIR."/queues/*.php") as $name){
    include $name;
}
foreach (glob(ROOTDIR."/tasks/*.php") as $name){
    include $name;
}
include ROOTDIR.'/ManifestParser.php';

$queue_name = $argv[1];
$parser = new ManifestParser();
$queue_info = $parser->checkQueue($queue_name);

$queue = $parser->getQueue($queue_name);
$server = new \swoole_server($queue_info->host, $queue_info->port);


//$server->on('connect', function($server, $fd){
//    TODO logs
//});

$server->on('receive', function($server, $fd, $from_id, $message) use ($queue) {
    try {
        $message = json_decode($message, true);
        $command = $message[0];
        switch ($command) {
            case 'push':
                $data = $message[1];
                $result = $queue->push($data);
                $server->send($fd, json_encode($result));
                break;
            case 'pop':
                $response = $queue->pop();
                $server->send($fd, json_encode($response));
                break;
            case 'stats':
                $server->send($fd, json_encode($queue->stats()));
                break;
            case 'destroy':
                $queue->destroy();
        }
    } catch (\Exception $e) {
        //TODO logs
        var_dump($e->getMessage());
        $server->send($fd, json_encode(false));
    }
});

//$server->on('close', function($server, $fd){
//    TODO logs
//});

$server->start();