<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Pdf;
use PHPUnit\Framework\TestCase;

class PdfTest extends TestCase
{
    private Pdf $pdf;

    protected function setUp(): void
    {
        $this->pdf = new Pdf();
    }

    // --- get / set ---

    public function testSetAndGetProperty(): void
    {
        $this->pdf->set('izPageNoOffset', 5);
        $this->assertSame(5, $this->pdf->get('izPageNoOffset'));
    }

    public function testGetReturnsDefaultWhenNotSet(): void
    {
        $this->assertSame('fallback', $this->pdf->get('nonExistentProp', 'fallback'));
    }

    public function testGetReturnsNullByDefault(): void
    {
        $this->assertNull($this->pdf->get('nonExistentProp'));
    }

    public function testSetReturnsThis(): void
    {
        $result = $this->pdf->set('izPageNoOffset', 1);
        $this->assertSame($this->pdf, $result);
    }

    // --- izPageNoOffset ---

    public function testDefaultPageNoOffset(): void
    {
        $this->assertSame(0, $this->pdf->izPageNoOffset);
    }

    // --- izSourcesPath ---

    public function testSetSourcesPath(): void
    {
        $this->pdf->set('izSourcesPath', '/some/path/');
        $this->assertSame('/some/path/', $this->pdf->get('izSourcesPath'));
    }

    // --- PageNo with offset ---

    public function testPageNoWithOffset(): void
    {
        $this->pdf->AddPage();
        $this->pdf->izPageNoOffset = 0;
        $pageWithoutOffset = $this->pdf->PageNo();

        $this->pdf->izPageNoOffset = 2;
        $pageWithOffset = $this->pdf->PageNo();

        $this->assertSame($pageWithoutOffset - 2, $pageWithOffset);
    }

    // --- AddPage resets izOnLeft ---

    public function testAddPageSetsIzOnLeftTrue(): void
    {
        $this->pdf->set('izOnLeft', false);
        $this->pdf->AddPage();
        $this->assertTrue($this->pdf->get('izOnLeft'));
    }

    // --- pageHoldsEnoughSpace ---

    public function testPageHoldsEnoughSpaceReturnsBool(): void
    {
        $this->pdf->AddPage();
        $result = $this->pdf->pageHoldsEnoughSpace(10);
        $this->assertIsBool($result);
    }

    public function testPageHoldsEnoughSpaceSmallHeight(): void
    {
        $this->pdf->AddPage();
        // A very small height should fit on a fresh page
        $this->assertTrue($this->pdf->pageHoldsEnoughSpace(1));
    }
}
