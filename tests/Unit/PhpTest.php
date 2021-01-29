<?php

namespace Vepay\Gateway\Tests\Unit;

use PHPUnit\Framework\TestCase;

class PhpTest extends TestCase
{
    public function testQQ(): void
    {
        $null = null;
        $false = false;

        $this->assertTrue(($undefined ?? true) === true);

        $this->assertTrue(($null ?? false) === false);
        $this->assertTrue(isset($null) === false);

        $this->assertTrue(($false ?? true) === false);
        $this->assertTrue(($false ?? true) === false);
        $this->assertTrue(isset($false) === true);
    }

    public function testTrim(): void
    {
        $this->assertEquals('', trim((string)null));
        $this->assertIsNotString(null);
    }
}
