<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    private Helper $helper;

    protected function setUp(): void
    {
        $this->helper = new Helper();
    }

    // --- cleanText ---

    public function testCleanTextReplacesRightSingleQuote(): void
    {
        $input = "it\xE2\x80\x99s";
        $this->assertSame("it's", $this->helper->cleanText($input));
    }

    public function testCleanTextReplacesLeftSingleQuote(): void
    {
        $input = "\xE2\x80\x98hello\xE2\x80\x99";
        $this->assertSame("'hello'", $this->helper->cleanText($input));
    }

    public function testCleanTextLeavesRegularTextUnchanged(): void
    {
        $this->assertSame('Hello world', $this->helper->cleanText('Hello world'));
    }

    public function testCleanTextEmptyString(): void
    {
        $this->assertSame('', $this->helper->cleanText(''));
    }

    // --- uppercaseString ---

    public function testUppercaseStringBasic(): void
    {
        $this->assertSame('HELLO', $this->helper->uppercaseString('hello'));
    }

    public function testUppercaseStringUtf8(): void
    {
        $this->assertSame('ÉÀÜÖ', $this->helper->uppercaseString('éàüö'));
    }

    public function testUppercaseStringAlreadyUpper(): void
    {
        $this->assertSame('ABC', $this->helper->uppercaseString('ABC'));
    }

    public function testUppercaseStringMixedCase(): void
    {
        $this->assertSame('HELLO WORLD', $this->helper->uppercaseString('Hello World'));
    }

    // --- px2mm ---

    public function testPx2mmDefaultDpi(): void
    {
        // 72 DPI: dpcm = 72 * 0.393701 ≈ 28.346472
        // 72 / 28.346472 = 2.54 cm = 25.4 mm
        $result = $this->helper->px2mm(72);
        $this->assertEqualsWithDelta(25.4, $result, 0.1);
    }

    public function testPx2mmZeroPixels(): void
    {
        $this->assertEqualsWithDelta(0, $this->helper->px2mm(0), 0.001);
    }

    public function testPx2mmCustomDpi(): void
    {
        // At 96 DPI: 96 px = 1 inch = 25.4 mm
        $result = $this->helper->px2mm(96, 96);
        $this->assertEqualsWithDelta(25.4, $result, 0.1);
    }

    public function testPx2mmWithFloat(): void
    {
        $result = $this->helper->px2mm(39.333333333333336);
        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testPx2mmWithFloatMatchesCalculation(): void
    {
        $px = 10.5;
        $dpcm = 72 * 0.393701;
        $expected = ($px / $dpcm) * 10;
        $this->assertEqualsWithDelta($expected, $this->helper->px2mm($px), 0.001);
    }

    // --- px2pt ---

    public function testPx2ptDefaultDpi(): void
    {
        // At 72 DPI: (px / 72) * 72 = px (identity)
        $this->assertEqualsWithDelta(10, $this->helper->px2pt(10), 0.1);
    }

    public function testPx2ptZero(): void
    {
        $this->assertEqualsWithDelta(0, $this->helper->px2pt(0), 0.001);
    }

    public function testPx2ptCustomDpi(): void
    {
        // At 96 DPI: (96 / 96) * 72 = 72
        $this->assertEqualsWithDelta(72, $this->helper->px2pt(96, 96), 0.1);
    }

    public function testPx2ptWithFloat(): void
    {
        // 7.5 px at 72 DPI: (7.5 / 72) * 72 = 7.5
        $this->assertEqualsWithDelta(7.5, $this->helper->px2pt(7.5), 0.1);
    }

    public function testPx2ptWithFloatCustomDpi(): void
    {
        $px = 45.132075471698116;
        $result = $this->helper->px2pt($px, 96);
        $expected = round(($px / 96) * 72, 1);
        $this->assertEqualsWithDelta($expected, $result, 0.1);
    }

    // --- mm2px ---

    public function testMm2pxDefaultDpi(): void
    {
        // 25.4 mm = 1 inch => 72 px at 72 DPI
        $result = $this->helper->mm2px(25);
        // 25mm * 0.039370079 = 0.98425... in * 72 = 70.866... ≈ 71
        $this->assertEqualsWithDelta(71, $result, 1);
    }

    public function testMm2pxZero(): void
    {
        $this->assertEqualsWithDelta(0, $this->helper->mm2px(0), 0.001);
    }

    public function testMm2pxWithFloat(): void
    {
        // 12.7 mm = 0.5 inch => 36 px at 72 DPI
        $result = $this->helper->mm2px(12.7);
        $this->assertEqualsWithDelta(36, $result, 1);
    }

    // --- percent2mm ---

    public function testPercent2mm(): void
    {
        $this->assertEqualsWithDelta(50, $this->helper->percent2mm(50, 100), 0.001);
    }

    public function testPercent2mmFull(): void
    {
        $this->assertEqualsWithDelta(200, $this->helper->percent2mm(100, 200), 0.001);
    }

    public function testPercent2mmZero(): void
    {
        $this->assertEqualsWithDelta(0, $this->helper->percent2mm(0, 100), 0.001);
    }

    public function testPercent2mmSmall(): void
    {
        $this->assertEqualsWithDelta(25, $this->helper->percent2mm(25, 100), 0.001);
    }

    public function testPercent2mmWithFloats(): void
    {
        $this->assertEqualsWithDelta(33.75, $this->helper->percent2mm(33.75, 100), 0.001);
    }

    public function testPercent2mmWithFloatBase(): void
    {
        $this->assertEqualsWithDelta(85.0, $this->helper->percent2mm(50, 170.0), 0.001);
    }

    // --- border ---

    public function testBorderDefaultValues(): void
    {
        $result = $this->helper->border(0.1, '#000000');
        $this->assertSame(0.1, $result['width']);
        $this->assertSame('#000000', $result['color']);
        $this->assertSame('square', $result['cap']);
        $this->assertSame('round', $result['join']);
        $this->assertSame(0, $result['dash']);
        $this->assertSame(0, $result['phase']);
    }

    public function testBorderWithDash(): void
    {
        $result = $this->helper->border(1, [0, 0, 0], false, true);
        $this->assertSame('butt', $result['cap']);
        $this->assertSame('miter', $result['join']);
        $this->assertTrue($result['dash']);
    }

    public function testBorderWithRounded(): void
    {
        $result = $this->helper->border(1, [0, 0, 0], true);
        $this->assertSame('butt', $result['cap']);
        $this->assertSame('miter', $result['join']);
    }

    public function testBorderWithColorArray(): void
    {
        $color = [255, 0, 0];
        $result = $this->helper->border(0.5, $color);
        $this->assertSame($color, $result['color']);
    }

    // --- borders ---

    public function testBordersAll(): void
    {
        $result = $this->helper->borders('all', 1, [0, 0, 0]);
        $this->assertArrayHasKey('all', $result);
        $this->assertCount(1, $result);
    }

    public function testBordersIndividualSides(): void
    {
        $result = $this->helper->borders('TB', 1, [0, 0, 0]);
        $this->assertArrayHasKey('T', $result);
        $this->assertArrayHasKey('B', $result);
        $this->assertCount(2, $result);
    }

    public function testBordersSingleSide(): void
    {
        $result = $this->helper->borders('L', 1, [0, 0, 0]);
        $this->assertArrayHasKey('L', $result);
        $this->assertCount(1, $result);
    }

    // --- spanner ---

    public function testSpanner(): void
    {
        $result = $this->helper->spanner(12, 'Hello');
        $this->assertSame('<span style="line-height:12pt;">Hello</span>', $result);
    }

    public function testSpannerWithDifferentHeight(): void
    {
        $result = $this->helper->spanner(20, 'Test');
        $this->assertSame('<span style="line-height:20pt;">Test</span>', $result);
    }

    public function testSpannerWithEmptyText(): void
    {
        $result = $this->helper->spanner(10, '');
        $this->assertSame('<span style="line-height:10pt;"></span>', $result);
    }
}
