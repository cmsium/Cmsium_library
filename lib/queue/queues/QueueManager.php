<?php
namespace Queue\Queues;

use Queue\Exceptions\NoQueuesException;
use Queue\Exceptions\PushErrorException;
use Queue\Exceptions\WrongQueueException;
use Queue\Task;


class QueueManager {
    public $headers = ['queue_tag' => 0];
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

    public function route($headers, $data, $mode = null) {
        if (!$mode){
            $mode = $this->mode;
        }
        switch ($mode){
            case self::DIRECT: $this->direct($headers, $data); break;
            case self::FANOUT: $this->fanout($headers, $data); break;
            case self::TOPIC: $this->topic($headers, $data); break;
        }
    }

    public function push($queueTag, $queue, $taskData) {
        $queue->push($taskData);
    }

    public function direct($headers, $data) {
        $queue_tag = $this->getQueueTag($headers);
        $queue = $this->getQueue($queue_tag);
        $this->push($queue_tag, $queue, $data);
    }

    public function fanout($headers, $data) {
        if (empty($this->queues)){
            throw new NoQueuesException();
        }
        foreach ($this->queues as $key => $queue){
            $this->push($key, $queue, $data);
        }
    }

    public function topic($headers, $data) {
        $count = 0;
        foreach ($this->queues as $key => $queue){
            $queue_tag = $this->getQueueTag($headers);
            //TODO wildcard instead of regexp
            if (preg_match($queue_tag, $key)) {
                $count++;
                $this->push($key, $queue, $data);
            }
        }
        if ($count === 0) {
            throw new WrongQueueException();
        }
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getQueueTag($headers) {
        if (isset($headers['queue_tag'])) {
            return $headers['queue_tag'];
        }else {
            return $headers[$this->getHeaders()['queue_tag']];
        }
    }

    public function getQueue($name) {
        if (!key_exists($name, $this->queues)){
            throw new WrongQueueException();
        }
        return $this->queues[$name];
    }
}