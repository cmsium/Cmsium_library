<?php
namespace Queue;


use Queue\Queues\QueueClient;

foreach (glob("exceptions/*.php") as $name){
    include $name;
}
foreach (glob("queues/*.php") as $name){
    include $name;
}
foreach (glob("tasks/*.php") as $name){
    include $name;
}

$manager = new Queues\QueueManager();


$queue = new Queues\QueueClient('test', "127.0.0.1", 9502);
$manager->registerQueue($queue);


$server = new \swoole_server("127.0.0.1", 9503);
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
                $manager->route($message[1], $message[2], $mode);
                break;
            case 'headers':
                $server->send($fd, json_encode($manager->getHeaders()));
                break;
        }
    } catch (\Exception $e){
        //TODO logs
        var_dump($e->getMessage());
    }

});

//$server->on('close', function($server, $fd){
//    TODO logs
//});

$server->start();