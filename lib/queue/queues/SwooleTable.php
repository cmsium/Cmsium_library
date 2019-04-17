<?php
namespace Queue\Queues;

class SwooleTable implements Queue {
    public $types = [
        'string' => [\swoole_table::TYPE_STRING, 16],
        'int' => [\swoole_table::TYPE_INT,  PHP_INT_SIZE]
    ];

    public $tasks;
    public $table;
    public $head = 0;
    public $tail = 0;

    public function __construct($tasks, $taskStructure) {
        $this->tasks = $tasks;
        $this->table = new \Swoole\Table($this->tasks);
        foreach ($taskStructure as $name => $value){
            $this->table->column($name, $this->types[$value['type']][0], $value['size']);
        }
        $this->table->create();
    }

    public function push($taskData) {
        $this->table[$this->tail] = $taskData;
        if (!$this->table->exist($this->tail)){
            return false;
        }
        $this->tail++;
        return true;
    }

    public function pop() {
        if (!$this->table->exist($this->head)){
            return false;
        }
        $result = $this->table[$this->head];
        $this->table->del($this->head);
        $this->head++;
        return $result->value;
    }

    public function stats() {
        return ['queue_num' => $this->table->count(), 'head' => $this->head, 'tail' => $this->tail];
    }

    public function destroy() {
        $this->table->destroy();
    }
}