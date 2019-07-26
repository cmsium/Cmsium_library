<?php

namespace Tests\Feature;

class ExampleTest extends \Testgear\TestCase {

    use \Testgear\DB\RefreshTables;

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->setApplication(app());
    }

    protected function setUp(): void {
        parent::setUp();
    }

    public function testJsonExample() {
        $response = $this->getJson('/test', ['x-user-token' => 'as8dasg8daygsd']);

        $response->assertJson([
            'hi' => 'mark'
        ]);
        $this->assertDatabaseHas('staff', ['name' => 'Richard', 'phone' => '9746356473']);
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