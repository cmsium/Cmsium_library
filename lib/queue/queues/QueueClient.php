<?php
namespace Queue\Queues;
use Queue\Exceptions\QueueConnectException;

class QueueClient implements Queue {

    public $name;
    public $host;
    public $port;
    public $client;

    public function __construct(string $name, string $host, $port) {
        $this->name = $name;
        $this->host = $host;
        $this->port = $port;
        $this->client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
    }

    public function connect() {
        if (!$this->client->connect($this->host, $this->port)){
            throw new QueueConnectException();
        }
    }

    public function stats() {
        $this->connect();
        $this->client->send(json_encode(['stats']));
        $result = json_decode($this->client->recv(), true);
        $this->close();
        return $result;
    }

    public function pop() {
        $this->connect();
        $this->client->send(json_encode(['pop']));
        $result = json_decode($this->client->recv(), true);
        $this->close();
        return $result;
    }

    public function push($data) {
        $this->connect();
        $this->client->send(json_encode(['push', $data]));
        $this->close();
    }

    public function destroy() {
        $this->connect();
        $this->client->send(json_encode(['destroy']));
        $this->close();
    }

    public function getName() {
        return $this->name;
    }

    public function close() {
        $this->client->close();
    }
}