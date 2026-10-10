<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Font;
use PHPUnit\Framework\TestCase;

class FontTest extends TestCase
{
    public function testToArrayFormat(): void
    {
        $font = new Font('montserrat');
        $font->setStyle('I');
        $font->setSize(10);
        $result = $font->toArray();

        $this->assertCount(3, $result);
        $this->assertSame('montserrat', $result[0]);
        $this->assertSame('I', $result[1]);
        $this->assertSame(10, $result[2]);
    }
}
