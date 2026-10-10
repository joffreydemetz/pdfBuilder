<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Color;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    public static function hexAndRgb(): array
    {
        return [
            'red' => ['#FF0000', [255, 0, 0], 'FF0000'],
            'green' => ['#00FF00', [0, 255, 0], '00FF00'],
            'blue' => ['#0000FF', [0, 0, 255], '0000FF'],
            'white' => ['#FFFFFF', [255, 255, 255], 'FFFFFF'],
            'black' => ['#000000', [0, 0, 0], '000000'],
            'grey' => ['#808080', [128, 128, 128], '808080'],
            'single-digit channels are zero-padded' => ['#0080FF', [0, 128, 255], '0080FF'],
            'mixed channels' => ['#123456', [18, 52, 86], '123456'],
            'lowercase hex, uppercase name' => ['#ab12cd', [171, 18, 205], 'AB12CD'],
            'another mix' => ['#6496C8', [100, 150, 200], '6496C8'],
        ];
    }

    #[DataProvider('hexAndRgb')]
    public function testHexParsesToRgbAndName(string $hex, array $rgb, string $name): void
    {
        $color = new Color($hex);

        $this->assertSame($rgb, $color->toArray());
        $this->assertSame($name, $color->name);
    }

    #[DataProvider('hexAndRgb')]
    public function testRgbParsesToTheSameRgbAndItsHexName(string $hex, array $rgb, string $name): void
    {
        $color = new Color($rgb);

        $this->assertSame($rgb, $color->toArray());
        $this->assertSame($name, $color->name);
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

    public function testParseHexWithoutHash(): void
    {
        $color = new Color('FF8800');
        $this->assertSame([255, 136, 0], $color->toArray());
    }

    public function testNameStripsNonHexCharacters(): void
    {
        $color = new Color('#AB CD EF');
        // The hex parse strips non-hex chars, so "ABCDEF" remains
        $this->assertSame('ABCDEF', $color->name);
    }

    public static function valueShifts(): array
    {
        return [
            'lighten a dark slate by 50' => ['#334455', 'lighten', 50, [127, 170, 212], '7FAAD4'],
            'lighten grey by 20' => ['#808080', 'lighten', 20, [179, 179, 179], 'B3B3B3'],
            'lighten clamps at white' => ['#808080', 'lighten', 90, [255, 255, 255], 'FFFFFF'],
            'darken clamps at black' => ['#808080', 'darken', 90, [0, 0, 0], '000000'],
        ];
    }

    #[DataProvider('valueShifts')]
    public function testLightenAndDarkenShiftTheValueAndRenameTheColor(string $hex, string $method, int $percent, array $rgb, string $name): void
    {
        $color = (new Color($hex))->{$method}($percent);

        $this->assertSame($rgb, $color->toArray());
        $this->assertSame($name, $color->name);
    }

    public static function hueSectors(): array
    {
        return [
            'orange (0-60°)' => ['#FF8000', [127, 63, 0]],
            'chartreuse (60-120°)' => ['#80FF00', [63, 127, 0]],
            'spring green (120-180°)' => ['#00FF80', [0, 127, 64]],
            'azure (180-240°)' => ['#0080FF', [0, 64, 127]],
            'violet (240-300°)' => ['#8000FF', [63, 0, 127]],
            'rose (300-360°)' => ['#FF0080', [127, 0, 64]],
        ];
    }

    #[DataProvider('hueSectors')]
    public function testDarkenBy50HalvesAFullValueColorAndKeepsItsHue(string $hex, array $rgb): void
    {
        $this->assertSame($rgb, (new Color($hex))->darken(50)->toArray());
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

    public function testParseOverridesPreviousColor(): void
    {
        $color = new Color('#FF0000');
        $this->assertSame([255, 0, 0], $color->toArray());

        $color->parse('#0000FF');
        $this->assertSame([0, 0, 255], $color->toArray());
    }

    public function testDarkenMakesTheColorDarker(): void
    {
        $grey = (new Color('#808080'))->darken(20)->toArray();
        $this->assertSame([77, 77, 77], $grey, 'value 0.50 -> 0.30');

        $red = (new Color('#CC0000'))->darken(50)->toArray();
        $this->assertLessThan(204, $red[0]);
        $this->assertSame([0, 0], [$red[1], $red[2]]);
    }
}
