<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Font;
use PHPUnit\Framework\TestCase;

class FontTest extends TestCase
{
    public function testConstructSetsName(): void
    {
        $font = new Font('helvetica');
        $result = $font->toArray();
        $this->assertSame('helvetica', $result[0]);
    }

    public function testDefaultSizeIsNull(): void
    {
        $font = new Font('arial');
        $result = $font->toArray();
        $this->assertNull($result[2]);
    }

    public function testDefaultStyleIsNull(): void
    {
        $font = new Font('arial');
        $result = $font->toArray();
        $this->assertNull($result[1]);
    }

    public function testSetSizeReturnsThis(): void
    {
        $font = new Font('helvetica');
        $result = $font->setSize(12);
        $this->assertSame($font, $result);
    }

    public function testSetSize(): void
    {
        $font = new Font('helvetica');
        $font->setSize(14);
        $result = $font->toArray();
        $this->assertSame(14, $result[2]);
    }

    public function testSetStyleReturnsThis(): void
    {
        $font = new Font('helvetica');
        $result = $font->setStyle('B');
        $this->assertSame($font, $result);
    }

    public function testSetStyle(): void
    {
        $font = new Font('helvetica');
        $font->setStyle('BI');
        $result = $font->toArray();
        $this->assertSame('BI', $result[1]);
    }

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

    public function testChaining(): void
    {
        $font = new Font('courier');
        $result = $font->setSize(16)->setStyle('B')->toArray();

        $this->assertSame(['courier', 'B', 16], $result);
    }

    public function testDifferentFontNames(): void
    {
        $fonts = ['helvetica', 'courier', 'montserrat', 'merienda'];

        foreach ($fonts as $name) {
            $font = new Font($name);
            $this->assertSame($name, $font->toArray()[0]);
        }
    }
}
