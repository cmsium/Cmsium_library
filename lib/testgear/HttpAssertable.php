<?php

namespace Testgear;

trait HttpAssertable {

    /**
     * @var \PHPUnit\Framework\TestCase
     */
    protected $testCase;

    public function setTestCase(\PHPUnit\Framework\TestCase $testCase) {
        $this->testCase = $testCase;
    }

    public function assertSee() {
        // TODO: Implement
    }

    public function assertJson() {
        // TODO: Implement
    }

    public function assertExactJson($data) {
        if (is_array($data)) {
            $data = json_encode($data);
        }

        $actualData = json_encode(json_decode($this->result, true));
        $message = 'Expected JSON does not match application response.';
        $this->testCase->assertJsonStringEqualsJsonString($data, $actualData, $message);
    }

    public function assertStatus() {
        // TODO: Implement
    }

}