<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Tests\Support\ConcreteBuilder;
use JDZ\Utils\Data as jData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    private function builderOn(string $sourcesPath): ConcreteBuilder
    {
        return new ConcreteBuilder($sourcesPath, sys_get_temp_dir() . '/test-output.pdf', new jData());
    }

    private function packageBuilder(): ConcreteBuilder
    {
        return $this->builderOn(str_replace('\\', '/', realpath(__DIR__ . '/../resources')) . '/');
    }

    private function fixtureBuilder(): ConcreteBuilder
    {
        return $this->builderOn(str_replace('\\', '/', realpath(__DIR__ . '/fixtures')) . '/');
    }

    /**
     * resources/config/modelizer.base.yml once merged: its scalar keys in file order,
     * then the colors, then the fonts.
     */
    private static function baseScalars(): array
    {
        return [
            'pageWidth' => 1,
            'centerVertically' => false,
            'betweenParts' => 7,
            'borderWidth' => 0.1,
            'defaultSize' => 9,
            'h1Fsize' => 17,
            'h2Fsize' => 16,
            'h3Fsize' => 15,
            'h4Fsize' => 13,
            'h5Fsize' => 12,
            'h6Fsize' => 11,
            'pFsize' => 9,
            'liFsize' => 9,
            'bulletFsize' => 12,
            'h1Align' => 'L',
            'h2Align' => 'L',
            'h3Align' => 'L',
            'h4Align' => 'L',
            'h5Align' => 'L',
            'h6Align' => 'L',
            'linkStyle' => 'B',
            'pageNbFont' => 'helvetica',
            'pageNbSize' => 8,
            'fontDefault' => 'helvetica',
            'fontDark' => 'helvetica',
            'fontLight' => 'helvetica',
        ];
    }

    private static function baseColorsAndFonts(): array
    {
        return [
            'colors' => [
                'white' => '#FFFFFF',
                'black' => '#000000',
                'grayish' => '#a19f8c',
                'links' => '#0b8181',
                'hr' => '#5B4731',
                'defaultColor' => '#000000',
                'textColor' => '#000000',
                'linkColor' => '#0b8181',
                'colorTheme' => '#000000',
                'colorTitle' => '#000000',
                'colorText' => '#000000',
                'h1Color' => '#000000',
                'h2Color' => '#000000',
                'h3Color' => '#000000',
                'h4Color' => '#000000',
                'h5Color' => '#000000',
                'h6Color' => '#000000',
                'pColor' => '#000000',
                'liColor' => '#000000',
                'pageNbColor' => '#000000',
            ],
            'fonts' => [
                'default' => 'helvetica',
                'black' => 'helvetica',
                'light' => 'helvetica',
            ],
        ];
    }

    public static function packagedModelizers(): array
    {
        $fullPage = ['pageWidth' => 1, 'marginTop' => 0, 'marginBottom' => 0];

        return [
            'blank adds nothing to base' => ['blank', [], self::baseScalars() + self::baseColorsAndFonts()],
            'image zeroes the vertical margins' => ['image', $fullPage, self::baseScalars() + ['marginTop' => 0, 'marginBottom' => 0] + self::baseColorsAndFonts()],
            'pdf zeroes the vertical margins' => ['pdf', $fullPage, self::baseScalars() + ['marginTop' => 0, 'marginBottom' => 0] + self::baseColorsAndFonts()],
        ];
    }

    #[DataProvider('packagedModelizers')]
    public function testLoadModelizerConfigFileReturnsOwnKeysAndListsBaseAsParent(string $modelizer, array $ownKeys, array $merged): void
    {
        $inherits = [];
        $config = $this->packageBuilder()->exposeLoadModelizerConfigFile($modelizer, $inherits);

        $this->assertSame($ownKeys, $config);
        $this->assertSame(['base'], $inherits);
    }

    #[DataProvider('packagedModelizers')]
    public function testLoadModelizerConfigMergesBaseUnderThePackagedModelizer(string $modelizer, array $ownKeys, array $merged): void
    {
        $this->assertSame($merged, $this->packageBuilder()->exposeLoadModelizerConfig($modelizer));
    }

    public function testLoadModelizerConfigFileNonExistent(): void
    {
        $builder = $this->packageBuilder();
        $inherits = [];
        $config = $builder->exposeLoadModelizerConfigFile('nonexistent_model', $inherits);

        $this->assertSame([], $config);
    }

    /**
     * 1.1.2: an unknown modelizer is the base one. It used to be an empty
     * config: no colours, no fonts, and the first getColor() threw.
     */
    public function testLoadModelizerConfigOfAnUnknownModelizerIsTheBaseOne(): void
    {
        $builder = $this->packageBuilder();

        $this->assertSame($builder->exposeLoadModelizerConfig('base'), $builder->exposeLoadModelizerConfig('nonexistent_model'));
        $this->assertSame(self::baseScalars() + self::baseColorsAndFonts(), $builder->exposeLoadModelizerConfig('nonexistent_model'));
    }

    public function testLoadModelizerConfigOfAnUnknownModelizerWithoutABaseOneIsEmpty(): void
    {
        $this->assertSame(['colors' => [], 'fonts' => []], $this->fixtureBuilder()->exposeLoadModelizerConfig('nonexistent_model'));
    }

    public function testLoadModelizerConfigOfAnEmptyFileIsNotTheBaseOne(): void
    {
        $this->assertSame(['colors' => [], 'fonts' => []], $this->fixtureBuilder()->exposeLoadModelizerConfig('empty'));
    }

    public function testLoadModelizerConfigFileListsAncestorsParentFirstAndOnce(): void
    {
        // leaf inherits [child, extra]; both inherit parent
        $inherits = [];
        $config = $this->fixtureBuilder()->exposeLoadModelizerConfigFile('leaf', $inherits);

        $this->assertSame(['parent', 'child', 'extra'], $inherits);
        $this->assertSame([
            'colors' => ['accent' => [1, 2, 3]],
            'fonts' => ['title' => 'courier'],
            'align' => 'R',
        ], $config);
    }

    public function testLoadModelizerConfigLetsEachDescendantOverrideItsAncestors(): void
    {
        $this->assertSame([
            'size' => 11,          // parent 9 -> child 10 -> extra 11
            'align' => 'R',        // parent L -> leaf R
            'extraOnly' => true,
            'colors' => [
                'ink' => '#222222',    // parent #111111 -> child #222222
                'paper' => '#FFFFFF',
                'accent' => [1, 2, 3],
            ],
            'fonts' => [
                'default' => 'helvetica',
                'title' => 'courier', // parent helvetica -> leaf courier
            ],
        ], $this->fixtureBuilder()->exposeLoadModelizerConfig('leaf'));
    }

    /**
     * An empty or comment-only file parses to null: it used to be a TypeError
     * (the method returns an array).
     */
    public function testAnEmptyConfigFileIsAnEmptyConfig(): void
    {
        $inherits = [];

        $this->assertSame([], $this->fixtureBuilder()->exposeLoadModelizerConfigFile('empty', $inherits));
        $this->assertSame([], $inherits);
    }

    /**
     * a inherits b inherits a: it used to recurse until memory ran out.
     */
    public function testCircularInheritanceIsRefused(): void
    {
        $inherits = [];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Circular modelizer inheritance: loop-a > loop-b > loop-a');

        $this->fixtureBuilder()->exposeLoadModelizerConfigFile('loop-a', $inherits);
    }
}
