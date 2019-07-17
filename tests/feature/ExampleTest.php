<?php

class ExampleTest extends \Testgear\TestCase {

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->setApplication(app());
    }

    public function testExample() {
        $response = $this->getJson('/test');

        $response->assertExactJson([
            'hi' => 'mork'
        ]);
    }

}