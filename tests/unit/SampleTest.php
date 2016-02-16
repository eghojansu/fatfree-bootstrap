<?php

namespace tests\unit;

use tests\Test;

class SampleTest extends Test
{
    public function get($app,$params)
    {
        $test = $this->newTest();

        $test->message('Sample Test');

        $this->testResult($test);
    }
}