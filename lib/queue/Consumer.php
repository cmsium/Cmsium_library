<?php
namespace Queue;

use Queue\Exceptions\WrongQueueException;
use Queue\Queues\QueueManager;

class Consumer {

    public $queues = [];

    public function subscribe(QueueManager $manager, $queue) {
        $this->queues[$queue] = $manager->getQueue($queue);
    }

    public function on($queue, $callback, $fetchTime = null) {
        $queue = $this->getQueue($queue);
        if (!$fetchTime) {
            \go(function () use ($queue, $callback) {
                while (true) {
                    //TODO make it work
                    $data = $queue->pop();
                    if ($data){
                        $callback($data);
                    }
                }
            });
        } else {
            \swoole_timer_tick($fetchTime , [$this, 'invoke'], [$queue, $callback]);
        }
    }

    public function invoke($tid, $args) {
        $queue = $args[0];
        $callback = $args[1];
        $data = $queue->pop();
        if ($data){
            $callback($data);
        }
    }

    public function getQueue($name) {
        if (!key_exists($name, $this->queues)){
            throw new WrongQueueException();
        }
        return $this->queues[$name];
    }

}