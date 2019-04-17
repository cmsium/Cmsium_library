<?php
namespace Queue\Queues;

use Queue\Exceptions\NoQueuesException;
use Queue\Exceptions\PushErrorException;
use Queue\Exceptions\WrongQueueException;
use Queue\Task;


class QueueManager {
    public const DIRECT = 0;
    public const FANOUT = 1;
    public const TOPIC = 2;
    public $mode;

    public $queues = [];

    public function __construct($mode = self::DIRECT) {
        $this->mode = $mode;
    }

    public function createQueue($name, Queue $queue) {
        $this->queues[$name] = $queue;
    }

    public function destroyQueue($name) {
        $this->queues[$name]->destroy();
    }

    public function route(Task $task, $mode = null) {
        if (!$mode){
            $mode = $this->mode;
        }
        switch ($mode){
            case self::DIRECT: $this->direct($task); break;
            case self::FANOUT: $this->fanout($task); break;
            case self::TOPIC: $this->topic($task); break;
        }
    }

    public function direct(Task $task) {
        $queue = $this->getQueue($task->queryTag);
        if (!$queue->push($task->data)){
            throw new PushErrorException("$task->queryTag queue push task error");
        }
    }

    public function fanout(Task $task) {
        if (empty($this->queues)){
            throw new NoQueuesException();
        }
        foreach ($this->queues as $key => $queue){
            if (!$queue->push($task->data)){
                throw new PushErrorException("$key queue push task error");
            }
        }
    }

    public function topic(Task $task) {
        $count = 0;
        foreach ($this->queues as $key => $queue){
            if (preg_match($task->queryTag, $key)) {
                $count++;
                if (!$queue->push($task->data)) {
                    throw new PushErrorException("$key queue push task error");
                }
            }
        }
        if ($count === 0) {
            throw new WrongQueueException();
        }
    }

    public function getQueue($name) {
        if (!key_exists($name, $this->queues)){
            throw new WrongQueueException();
        }
        return $this->queues[$name];
    }
}