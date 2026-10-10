<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Color;
use JDZ\Pdf\Data;
use JDZ\Pdf\Font;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    private Data $data;

    protected function setUp(): void
    {
        $this->data = new Data();
    }

    // --- sets with colors ---

    public function testSetsWithColorObjects(): void
    {
        $red = new Color('#FF0000');
        $this->data->sets([
            'colors' => [
                'myred' => $red,
            ],
        ]);

        $this->assertSame([255, 0, 0], $this->data->getColor('myred'));
    }

    // --- sets with fonts ---

    public function testSetsWithFontsFromString(): void
    {
        $this->data->sets([
            'fonts' => [
                'default' => 'helvetica',
            ],
        ]);

        $font = $this->data->getFont('default');
        $this->assertSame('helvetica', $font[0]);
    }

    public function testSetsWithFontObjects(): void
    {
        $font = new Font('courier');
        $font->setSize(12)->setStyle('B');
        $this->data->sets([
            'fonts' => [
                'code' => $font,
            ],
        ]);

        $result = $this->data->getFont('code');
        $this->assertSame('courier', $result[0]);
    }

    // --- sets with regular properties ---

    public function testSetsWithRegularProperties(): void
    {
        $this->data->sets([
            'pagePadding' => 10,
            'borderWidth' => 2,
        ]);

        $this->assertSame(10, $this->data->get('pagePadding'));
        $this->assertSame(2, $this->data->get('borderWidth'));
    }

    // --- addColor ---

    public function testAddColor(): void
    {
        $color = new Color('#ABCDEF');
        $result = $this->data->addColor('custom', $color);
        $this->assertSame($this->data, $result);
        $this->assertSame($color->toArray(), $this->data->getColor('custom'));
    }

    // --- getString ---

    public function testGetString(): void
    {
        $this->data->sets(['title' => 'Hello World']);
        $this->assertSame('Hello World', $this->data->getString('title'));
    }

    public function testGetStringDefault(): void
    {
        $this->assertSame('default', $this->data->getString('missing', 'default'));
    }

    public function testGetStringCleansSmartQuotes(): void
    {
        $this->data->sets(['text' => "it\xE2\x80\x99s"]);
        $this->assertSame("it's", $this->data->getString('text'));
    }

    // --- getUppercaseString ---

    public function testGetUppercaseString(): void
    {
        $this->data->sets(['title' => 'hello world']);
        $this->assertSame('HELLO WORLD', $this->data->getUppercaseString('title'));
    }

    public function testGetUppercaseStringDefault(): void
    {
        $this->assertSame('DEFAULT', $this->data->getUppercaseString('missing', 'default'));
    }

    // --- getArrayItem ---

    public static function arrayItems(): array
    {
        return [
            'first item' => [['tags' => ['red', 'green']], 'tags', 0, 'red'],
            'second item' => [['tags' => ['red', 'green']], 'tags', 1, 'green'],
            'a scalar is a one-item list' => [['title' => 'Hello'], 'title', 0, 'Hello'],
            'missing key' => [[], 'tags', 0, false],
        ];
    }

    #[DataProvider('arrayItems')]
    public function testGetArrayItem(array $properties, string $name, int $index, mixed $expected): void
    {
        $this->data->sets($properties);

        $this->assertSame($expected, $this->data->getArrayItem($name, $index));
    }

    // --- getColor ---

    public static function storedColors(): array
    {
        return [
            'stored from a hex code' => ['brand', [51, 102, 153]],
            'stored from an rgb list' => ['accent', [10, 20, 30]],
            'property naming a stored color' => ['titleColor', [51, 102, 153]],
            'property holding a hex code' => ['linkColor', [0, 255, 0]],
        ];
    }

    #[DataProvider('storedColors')]
    public function testGetColorResolvesStoredNames(string $key, array $rgb): void
    {
        $this->data->sets([
            'colors' => [
                'brand' => '#336699',
                'accent' => [10, 20, 30],
            ],
            'titleColor' => 'brand',
            'linkColor' => '#00FF00',
        ]);

        $this->assertSame($rgb, $this->data->getColor($key));
    }

    public function testGetColorByName(): void
    {
        $this->data->addColor('red', new Color('#FF0000'));
        $this->assertSame([255, 0, 0], $this->data->getColor('red'));
    }

    public function testGetColorByHex(): void
    {
        $result = $this->data->getColor('#00FF00');
        $this->assertSame([0, 255, 0], $result);
    }

    public function testGetColorByPropertyReference(): void
    {
        $this->data->addColor('red', new Color('#FF0000'));
        $this->data->sets(['themeColor' => 'red']);
        // getColor('themeColor') should resolve via get('themeColor') -> 'red' -> getColor('red')
        $this->assertSame([255, 0, 0], $this->data->getColor('themeColor'));
    }

    public function testGetColorThrowsForUnknown(): void
    {
        $this->expectException(\Exception::class);
        $this->data->getColor('nonexistent');
    }

    // --- getColorObject ---

    public function testGetColorObjectReturnsClone(): void
    {
        $original = new Color('#FF0000');
        $this->data->addColor('red', $original);

        $clone = $this->data->getColorObject('red');
        $this->assertNotSame($original, $clone);
        $this->assertSame($original->toArray(), $clone->toArray());
    }

    public function testGetColorObjectFallsBackToBlack(): void
    {
        $this->data->addColor('black', new Color('#000000'));

        $result = $this->data->getColorObject('nonexistent');
        $this->assertSame([0, 0, 0], $result->toArray());
    }

    // --- getFont ---

    public function testGetFontByName(): void
    {
        $this->data->addFont('default', new Font('helvetica'));
        $result = $this->data->getFont('default');
        $this->assertSame('helvetica', $result[0]);
    }

    public function testGetFontFallsBackToDefault(): void
    {
        $this->data->addFont('default', new Font('helvetica'));
        $result = $this->data->getFont('nonexistent');
        $this->assertSame('helvetica', $result[0]);
    }

    public function testGetFontByPropertyReference(): void
    {
        $this->data->addFont('heading', new Font('montserrat'));
        $this->data->sets(['h1Font' => 'heading']);
        $result = $this->data->getFont('h1Font');
        $this->assertSame('montserrat', $result[0]);
    }

    // --- getBorderWidth ---

    public function testGetBorderWidth(): void
    {
        $this->data->sets(['borderWidth' => 2]);
        $this->assertSame(2, $this->data->getBorderWidth(1));
    }

    public function testGetBorderWidthWithMultiplier(): void
    {
        $this->data->sets(['borderWidth' => 3]);
        $this->assertSame(9, $this->data->getBorderWidth(3));
    }

    // --- px2mm ---

    public function testPx2mm(): void
    {
        $this->data->sets(['size' => 72]);
        $result = $this->data->px2mm('size');
        $this->assertEqualsWithDelta(25.4, $result, 0.1);
    }

    public function testPx2mmWithDefault(): void
    {
        $result = $this->data->px2mm('missing', 72);
        $this->assertEqualsWithDelta(25.4, $result, 0.1);
    }

    public function testPx2mmThrowsWhenNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->data->px2mm('missing');
    }

    // --- px2pt ---

    public function testPx2pt(): void
    {
        $this->data->sets(['size' => 10]);
        $result = $this->data->px2pt('size');
        $this->assertEqualsWithDelta(10, $result, 0.1);
    }

    public function testPx2ptThrowsWhenNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->data->px2pt('missing');
    }

    // --- mm2px ---

    public function testMm2px(): void
    {
        $this->data->sets(['size' => 25]);
        $result = $this->data->mm2px('size');
        $this->assertEqualsWithDelta(71, $result, 1);
    }

    public function testMm2pxThrowsWhenNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->data->mm2px('missing');
    }

    // --- percent2mm ---

    public function testPercent2mm(): void
    {
        $this->data->sets(['pct' => 50]);
        $result = $this->data->percent2mm('pct', 200);
        $this->assertEqualsWithDelta(100, $result, 0.001);
    }

    public function testPercent2mmThrowsWhenNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->data->percent2mm('missing', 200);
    }
}
