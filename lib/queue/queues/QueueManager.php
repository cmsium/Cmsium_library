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

    public function registerQueue(Queue $queue) {
        $this->queues[$queue->getName()] = $queue;
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

    public function push($queueTag, $queue, $taskData) {
        $queue->push($taskData);
    }

    public function direct(Task $task) {
        $queue = $this->getQueue($task->queryTag);
        $this->push($task->queryTag, $queue, $task->data);
    }

    public function fanout(Task $task) {
        if (empty($this->queues)){
            throw new NoQueuesException();
        }
        foreach ($this->queues as $key => $queue){
            $this->push($key, $queue, $task->data);
        }
    }

    public function topic(Task $task) {
        $count = 0;
        foreach ($this->queues as $key => $queue){
            //TODO wildcard instead of regexp
            if (preg_match($task->queryTag, $key)) {
                $count++;
                $this->push($key, $queue, $task->data);
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