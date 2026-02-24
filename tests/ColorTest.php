<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Color;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    public function testConstructWithoutValue(): void
    {
        $color = new Color();
        $this->assertInstanceOf(Color::class, $color);
    }

    public function testConstructWithHexString(): void
    {
        $color = new Color('#FF0000');
        $this->assertSame([255, 0, 0], $color->toArray());
    }

    public function testConstructWithRgbArray(): void
    {
        $color = new Color([0, 128, 255]);
        $this->assertSame([0, 128, 255], $color->toArray());
    }

    public function testParseHexSetsNameWithoutHash(): void
    {
        $color = new Color('#AB12CD');
        $this->assertSame('AB12CD', $color->name);
    }

    public function testParseHexFullSixDigits(): void
    {
        $color = new Color('#00FF00');
        $this->assertSame([0, 255, 0], $color->toArray());
    }

    public function testParseHexShortThreeDigits(): void
    {
        $color = new Color('#FFF');
        $this->assertSame([255, 255, 255], $color->toArray());
    }

    public function testParseHexShortBlack(): void
    {
        $color = new Color('#000');
        $this->assertSame([0, 0, 0], $color->toArray());
    }

    public function testParseHexBlue(): void
    {
        $color = new Color('#0000FF');
        $this->assertSame([0, 0, 255], $color->toArray());
    }

    public function testParseHexWithoutHash(): void
    {
        $color = new Color('FF8800');
        $this->assertSame([255, 136, 0], $color->toArray());
    }

    public function testParseReturnsThis(): void
    {
        $color = new Color();
        $result = $color->parse('#FF0000');
        $this->assertSame($color, $result);
    }

    public function testParseFromArraySetsRgb(): void
    {
        $color = new Color();
        $color->parse([100, 150, 200]);
        $this->assertSame([100, 150, 200], $color->toArray());
    }

    public function testToArrayReturnsThreeElements(): void
    {
        $color = new Color('#123456');
        $result = $color->toArray();
        $this->assertCount(3, $result);
        $this->assertSame([18, 52, 86], $result);
    }

    public function testLightenReturnsThis(): void
    {
        $color = new Color('#800000');
        $result = $color->lighten(50);
        $this->assertSame($color, $result);
    }

    public function testLightenIncreasesLightness(): void
    {
        $color = new Color('#334455');
        $original = $color->toArray();
        $color->lighten(50);
        $lightened = $color->toArray();

        // At least one channel should increase when lightening a dark color
        $anyLighter = ($lightened[0] > $original[0])
            || ($lightened[1] > $original[1])
            || ($lightened[2] > $original[2]);
        $this->assertTrue($anyLighter);
    }

    public function testLightenWithZeroDoesNotChange(): void
    {
        $color = new Color('#334455');
        $original = $color->toArray();
        $color->lighten(0);
        $this->assertSame($original, $color->toArray());
    }

    public function testLightenWith100DoesNotChange(): void
    {
        $color = new Color('#334455');
        $original = $color->toArray();
        $color->lighten(100);
        $this->assertSame($original, $color->toArray());
    }

    public function testDarkenReturnsThis(): void
    {
        $color = new Color('#FF0000');
        $result = $color->darken(50);
        $this->assertSame($color, $result);
    }

    public function testDarkenWithZeroDoesNotChange(): void
    {
        $color = new Color('#334455');
        $original = $color->toArray();
        $color->darken(0);
        $this->assertSame($original, $color->toArray());
    }

    public function testDarkenWith100DoesNotChange(): void
    {
        $color = new Color('#334455');
        $original = $color->toArray();
        $color->darken(100);
        $this->assertSame($original, $color->toArray());
    }

    public function testWhiteColor(): void
    {
        $color = new Color('#FFFFFF');
        $this->assertSame([255, 255, 255], $color->toArray());
        $this->assertSame('FFFFFF', $color->name);
    }

    public function testBlackColor(): void
    {
        $color = new Color('#000000');
        $this->assertSame([0, 0, 0], $color->toArray());
        $this->assertSame('000000', $color->name);
    }

    public function testGrayColor(): void
    {
        $color = new Color('#808080');
        $this->assertSame([128, 128, 128], $color->toArray());
    }

    public function testParseOverridesPreviousColor(): void
    {
        $color = new Color('#FF0000');
        $this->assertSame([255, 0, 0], $color->toArray());

        $color->parse('#0000FF');
        $this->assertSame([0, 0, 255], $color->toArray());
    }

    public function testHexToRgbAndBackPreservesValues(): void
    {
        $values = [
            '#FF0000' => [255, 0, 0],
            '#00FF00' => [0, 255, 0],
            '#0000FF' => [0, 0, 255],
            '#FFFFFF' => [255, 255, 255],
            '#000000' => [0, 0, 0],
            '#808080' => [128, 128, 128],
        ];

        foreach ($values as $hex => $expectedRgb) {
            $color = new Color($hex);
            $this->assertSame($expectedRgb, $color->toArray(), "Failed for hex: $hex");
        }
    }

    public function testArrayRoundTrip(): void
    {
        $rgb = [42, 128, 200];
        $color = new Color($rgb);
        $this->assertSame($rgb, $color->toArray());
    }

    public function testNameStripsNonHexCharacters(): void
    {
        $color = new Color('#AB CD EF');
        // The hex parse strips non-hex chars, so "ABCDEF" remains
        $this->assertSame('ABCDEF', $color->name);
    }

    public function testChainingParseAndLighten(): void
    {
        $color = new Color();
        $result = $color->parse('#400000')->lighten(20);
        $this->assertSame($color, $result);
    }
}
