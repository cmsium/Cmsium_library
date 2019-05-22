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