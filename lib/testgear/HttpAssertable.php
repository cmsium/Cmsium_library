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

    public function assertSee(string $expectedString) {
        $message = 'Application response does not contain expected string.';
        $this->testCase->assertStringContainsString($expectedString, $this->result, $message);
    }

    public function assertDontSee(string $expectedString) {
        $message = 'Application response contains expected string.';
        $this->testCase->assertStringNotContainsString($expectedString, $this->result, $message);
    }

    public function assertJson(array $data) {
        $actualData = json_decode($this->result, true);
        $message = 'Application response does not contain expected JSON.';
        $this->testCase->assertContains($data, $actualData, $message);
    }

    public function assertExactJson($data) {
        if (is_array($data)) {
            $data = json_encode($data);
        }

        $actualData = json_encode(json_decode($this->result, true));
        $message = 'Expected JSON does not match application response.';
        $this->testCase->assertJsonStringEqualsJsonString($data, $actualData, $message);
    }

    public function assertStatus($status) {
        $message = 'Application status code does not equal to expected.';
        $this->testCase->assertTrue($this->status == $status, $message);
    }

    public function assertStatusNot($status) {
        $message = 'Application status code equals to expected.';
        $this->testCase->assertNotTrue($this->status == $status, $message);
    }

    public function assertHeader($headerName, $value = null) {
        $result = false;

        if (isset($this->header[$headerName])) {
            $result = true;

            if ($value) {
                $result = $this->header[$headerName] == $value;
            }
        }

        $this->testCase->assertTrue($result);
    }

    public function assertHeaderMissing(string $headerName, string $value = null) {
        $result = false;

        if (isset($this->header[$headerName])) {
            $result = true;

            if ($value) {
                $result = $this->header[$headerName] == $value;
            }
        }

        $this->testCase->assertNotTrue($result);
    }

    public function assertCookie(string $name, string $value = null) {
        $result = false;
        $expectedFormat = $value ? "/^$name=$value;/" : "/^$name=\w+;/";

        foreach ($this->cookie as $cookie) {
            $result = preg_match($expectedFormat, $cookie) ? true : false;
        }

        $this->testCase->assertTrue($result, 'Expected cookie not found in application response.');
    }

    public function assertCookieMissing(string $name, string $value = null) {
        $result = false;
        $expectedFormat = $value ? "/^$name=$value;/" : "/^$name=\w+;/";

        foreach ($this->cookie as $cookie) {
            $result = preg_match($expectedFormat, $cookie) ? true : false;
        }

        $this->testCase->assertNotTrue($result, 'Expected cookie found in application response.');
    }

}