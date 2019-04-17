<?php
namespace Queue;

class TestTask extends Task {
    public static $structure = [
        'name' => ['type' => 'string', 'size' => 10],
        'age' => ['type' => 'int', 'size' => 3],
    ];
}