<?php
namespace Queue\Queues;

class SwooleChannel implements Queue {
    public $types = [
        'string' => [\swoole_table::TYPE_STRING, 16],
        'int' => [\swoole_table::TYPE_INT,  PHP_INT_SIZE]
    ];

    public $tasks;
    public $taskSize;
    public $chan;

    public function __construct($tasks, $taskStructure) {
        $this->tasks = $tasks;
        $taskSize = 0;
        foreach ($taskStructure as $name => $value){
            $taskSize += $value['size'] * $this->types[$value['type']][1];
        }
        $this->taskSize = $taskSize;
        $this->chan = new \Swoole\Channel($this->tasks * $this->taskSize);
    }

    public function push($taskData) {
        return $this->chan->push($taskData);
    }

    public function pop() {
        return $this->chan->pop();
    }

    public function stats() {
        return $this->chan->stats();
    }

    public function destroy() {
        unset($this->chan);
    }
}