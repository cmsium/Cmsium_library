<?php
namespace Queue;

foreach (glob("exceptions/*.php") as $name){
    include $name;
}
foreach (glob("overflow/*.php") as $name){
    include $name;
}
foreach (glob("queues/*.php") as $name){
    include $name;
}
foreach (glob("tasks/*.php") as $name){
    include $name;
}

$server = new \swoole_server("127.0.0.1", 9502);

$queue = new \Queue\Queues\SwooleTable('test',1000, \Queue\TestTask::$structure);

//$server->on('connect', function($server, $fd){
//    TODO logs
//});

$server->on('receive', function($server, $fd, $from_id, $message) use ($queue) {
    $message = json_decode($message, true);
    $command = $message[0];
    switch ($command){
        case 'push':
            $data = $message[1];
            $queue->push($data);
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
});

//$server->on('close', function($server, $fd){
//    TODO logs
//});

$server->start();