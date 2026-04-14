<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class MinimalTest extends TestCase
{
    public function testCanCreateBasicAssertion(): void
    {
        $this->assertTrue(true);
    }

    public function testSimpleArithmetic(): void
    {
        $result = 2 + 2;
        $this->assertSame(4, $result);
    }
}
