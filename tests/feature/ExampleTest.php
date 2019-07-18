<?php

class ExampleTest extends \Testgear\TestCase {

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->setApplication(app());
    }

    public function testJsonExample() {
        $response = $this->getJson('/test');

        $response->assertExactJson([
            'hi' => 'mark'
        ]);
    }

    public function testSeeExample() {
        $response = $this->getJson('/test');

        $response->assertSee('mark');
        $response->assertDontSee('mars');
    }

    public function testHeaderExample() {
        $response = $this->getJson('/test');

        $response->assertHeader('Content-Type', 'application/json');
    }

    public function testCookieExample() {
        $response = $this->getJson('/test');

        $response->assertCookie('foo', 'bar');
    }

}