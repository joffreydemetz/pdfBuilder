<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Helper;
use JDZ\Pdf\Tests\Support\SpyPdf;
use JDZ\Pdf\Toc;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    private const IMAGES = __DIR__ . '/fixtures/images/';

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

    public static function uppercaseStrings(): array
    {
        return [
            'lowercase ascii' => ['hello', 'HELLO'],
            'accented utf-8' => ['éàüö', 'ÉÀÜÖ'],
            'already uppercase' => ['ABC', 'ABC'],
            'mixed case with a space' => ['Hello World', 'HELLO WORLD'],
        ];
    }

    #[DataProvider('uppercaseStrings')]
    public function testUppercaseString(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->helper->uppercaseString($input));
    }

    public function testUppercaseStringWorksInUtf8AndRestoresTheInternalEncoding(): void
    {
        $previous = mb_internal_encoding();
        mb_internal_encoding('ISO-8859-1');

        try {
            $this->assertSame('ÉTÉ', $this->helper->uppercaseString('été'));
            $this->assertSame('ISO-8859-1', mb_internal_encoding());
        } finally {
            mb_internal_encoding($previous);
        }
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

    public static function percentages(): array
    {
        return [
            'half' => [50, 100, 50],
            'full' => [100, 200, 200],
            'zero' => [0, 100, 0],
            'quarter' => [25, 100, 25],
            'fractional percent' => [33.75, 100, 33.75],
            'float base' => [50, 170.0, 85],
        ];
    }

    #[DataProvider('percentages')]
    public function testPercent2mm(int|float $percent, int|float $base, int|float $expected): void
    {
        $this->assertEqualsWithDelta($expected, $this->helper->percent2mm($percent, $base), 1e-9);
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

    public static function spans(): array
    {
        return [
            '12pt' => [12, 'Hello', '<span style="line-height:12pt;">Hello</span>'],
            '20pt' => [20, 'Test', '<span style="line-height:20pt;">Test</span>'],
            'empty text' => [10, '', '<span style="line-height:10pt;"></span>'],
        ];
    }

    #[DataProvider('spans')]
    public function testSpanner(int $height, string $text, string $expected): void
    {
        $this->assertSame($expected, $this->helper->spanner($height, $text));
    }

    // --- exportToc ---

    public function testExportTocWritesALinkedPageNumberAtEveryMarkPosition(): void
    {
        $pdf = new SpyPdf();
        $pdf->AddPage();
        $pdf->AddPage();
        $pdf->AddPage();
        $pdf->setPage(1);
        $pdf->izPageNoOffset = 1;
        $pdf->set('tocFont', 'courier');

        $toc = new Toc();
        $toc->setPosition('intro', ['p' => 1, 'x' => 150, 'y' => 40, 'w' => 30, 'h' => 6]);
        $toc->setPosition('intro', ['p' => 2, 'x' => 120, 'y' => 20, 'w' => 25, 'h' => 5]);
        $toc->setPosition('outro', ['p' => 2, 'x' => 150, 'y' => 60, 'w' => 30, 'h' => 6]);
        $toc->setPage('intro', 1);
        $toc->setPage('outro', 2);
        $pdf->calls = [];

        $timeLimit = (int)ini_get('max_execution_time');
        $this->helper->exportToc($toc, $pdf);
        set_time_limit($timeLimit);

        $this->assertSame([[1], [2], [2]], $pdf->argsOf('setPage'));
        $this->assertSame([[150], [120], [150]], $pdf->argsOf('setX'));
        $this->assertSame([[40, false], [20, false], [60, false]], $pdf->argsOf('setY'));
        $this->assertSame(array_fill(0, 3, ['courier', '', 7.5]), $pdf->argsOf('setFont'));
        $this->assertSame(array_fill(0, 3, [[255, 255, 255]]), $pdf->argsOf('setFillColorArray'));
        $this->assertSame(array_fill(0, 3, [[0, 0, 0]]), $pdf->argsOf('setTextColorArray'));
        $this->assertSame(
            array_fill(0, 3, [0, $this->helper->px2mm(3), $this->helper->px2mm(10), $this->helper->px2mm(2)]),
            $pdf->argsOf('setCellPaddings')
        );

        $cells = $pdf->argsOf('Cell');
        $links = array_column($cells, 7);
        $this->assertCount(3, array_unique($links), 'one link per position');
        $this->assertSame([
            [30, 6, 1, 0, 0, 'R', false, $links[0]],
            [25, 5, 1, 0, 0, 'R', false, $links[1]],
            [30, 6, 2, 0, 0, 'R', false, $links[2]],
        ], $cells);
        // each link targets the mark's page shifted by the page-number offset
        $this->assertSame([[$links[0], 0, 2], [$links[1], 0, 2], [$links[2], 0, 3]], $pdf->argsOf('setLink'));

        $this->assertSame('lastPage', $pdf->methods()[count($pdf->calls) - 1]);
        $this->assertSame(3, $pdf->getPage());
    }

    public function testExportTocWithoutMarksLeavesThePdfUntouched(): void
    {
        $pdf = new SpyPdf();
        $pdf->AddPage();
        $pdf->AddPage();
        $pdf->setPage(1);
        $pdf->calls = [];

        $this->helper->exportToc(new Toc(), $pdf);

        $this->assertSame([], $pdf->calls);
        $this->assertSame(1, $pdf->getPage());
    }

    // --- getImageInfos ---

    public static function images(): array
    {
        return [
            'png keeps its 72 dpi' => ['landscape-200x100.png', 200, 100, \IMAGETYPE_PNG, 72],
            'jpeg keeps its 150 dpi' => ['photo-30x20.jpg', 30, 20, \IMAGETYPE_JPEG, 150],
            'gif has no dpi, GD reports 96' => ['flat-16x8.gif', 16, 8, \IMAGETYPE_GIF, 96],
        ];
    }

    #[DataProvider('images')]
    public function testGetImageInfosReadsSizeTypeAndResolution(string $file, int $width, int $height, int $type, int $dpi): void
    {
        $path = self::IMAGES . $file;

        $this->assertSame([
            'path' => $path,
            'width' => $width,
            'height' => $height,
            'type' => $type,
            'resX' => $dpi,
            'resY' => $dpi,
        ], (array)$this->helper->getImageInfos($path));
    }

    public function testGetImageInfosThrowsForAMissingImage(): void
    {
        $path = self::IMAGES . 'missing.png';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('image ' . $path . ' not found');

        $this->helper->getImageInfos($path);
    }

    // --- imageSize (landscape-200x100.png at 72 dpi) ---

    public static function imageBoxes(): array
    {
        $natural = ['wPx' => 200, 'hPx' => 100, 'w' => 70.55551745557612, 'h' => 35.27775872778806];
        $heightCapped = ['wPx' => 56.0, 'hPx' => 28.0, 'w' => 19.755544887561314, 'h' => 9.877772443780657];

        return [
            'no box: natural size' => [null, null, $natural],
            'box larger than the image: natural size' => [500, 500, $natural],
            'height cap (10mm = 28px) scales both sides' => [null, 10, $heightCapped],
            'width cap (22mm = 62px) scales both sides' => [22, null, ['wPx' => 62.0, 'hPx' => 31.0, 'w' => 21.8722104112286, 'h' => 10.9361052056143]],
            'height cap applied, width cap already met' => [60, 10, $heightCapped],
        ];
    }

    #[DataProvider('imageBoxes')]
    public function testImageSizeFitsTheImageInTheBox(int|float|null $w, int|float|null $h, array $size): void
    {
        $path = self::IMAGES . 'landscape-200x100.png';

        $this->assertSame([
            'path' => $path,
            'oriW' => 200,
            'oriH' => 100,
            'wPx' => $size['wPx'],
            'hPx' => $size['hPx'],
            'resX' => 72,
            'resY' => 72,
            'w' => $size['w'],
            'h' => $size['h'],
        ], $this->helper->imageSize($path, $w, $h));
    }

    public function testImageSizeThrowsForAMissingImage(): void
    {
        $path = self::IMAGES . 'missing-too.png';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('image ' . $path . ' not found');

        $this->helper->imageSize($path, 10, 10);
    }

    // --- regressions ---

    private ?string $tmp = null;

    protected function tearDown(): void
    {
        if (null === $this->tmp) {
            return;
        }

        foreach (glob($this->tmp . '/*/*') ?: [] as $file) {
            unlink($file);
        }
        foreach (glob($this->tmp . '/*') ?: [] as $entry) {
            is_dir($entry) ? rmdir($entry) : unlink($entry);
        }
        rmdir($this->tmp);
    }

    /** a $w x $h image written under a temp dir removed in tearDown */
    private function image(string $relative, int $w, int $h, string $type = 'png'): string
    {
        $this->tmp ??= sys_get_temp_dir() . '/jdz-pdf-' . bin2hex(random_bytes(6));
        $path = $this->tmp . '/' . $relative;
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $image = imagecreatetruecolor($w, $h);
        match ($type) {
            'png' => imagepng($image, $path),
            'webp' => imagewebp($image, $path),
            'bmp' => imagebmp($image, $path),
        };

        return $path;
    }

    public static function bordersSides(): array
    {
        return [
            'the four sides are "all"' => ['TRBL', ['all']],
            'in any order' => ['LBRT', ['all']],
            'some sides' => ['TB', ['T', 'B']],
            'no side' => ['', []],
        ];
    }

    /**
     * strpos() had its arguments reversed: four sides were never merged into
     * "all", and no side at all gave "all".
     */
    #[DataProvider('bordersSides')]
    public function testBordersPerSide(string $sides, array $keys): void
    {
        $border = $this->helper->border(2, [0, 0, 0]);

        $this->assertSame(array_fill_keys($keys, $border), $this->helper->borders($sides, 2, [0, 0, 0]));
    }

    /**
     * The size cache was keyed by file name: a second logo.png got the first
     * one's path and size.
     */
    public function testImagesWithTheSameNameInDifferentFoldersAreSizedApart(): void
    {
        $a = $this->image('a/logo.png', 100, 50);
        $b = $this->image('b/logo.png', 30, 30);

        $sizeA = $this->helper->imageSize($a);
        $sizeB = $this->helper->imageSize($b);

        $this->assertSame([$a, 100, 50], [$sizeA['path'], $sizeA['oriW'], $sizeA['oriH']]);
        $this->assertSame([$b, 30, 30], [$sizeB['path'], $sizeB['oriW'], $sizeB['oriH']]);
    }

    public static function otherFormats(): array
    {
        return [
            'webp' => ['webp'],
            'bmp' => ['bmp'],
        ];
    }

    /**
     * WebP and BMP images had no resolution: imageSize() warned, then hit a
     * TypeError. They are read at GD's 96 dpi.
     */
    #[DataProvider('otherFormats')]
    public function testOtherImageFormatsAreSizedAt96Dpi(string $type): void
    {
        $size = $this->helper->imageSize($this->image('picture.' . $type, 96, 48, $type));

        $this->assertSame([96, 96], [$size['resX'], $size['resY']]);
        $this->assertEqualsWithDelta([25.4, 12.7], [$size['w'], $size['h']], 0.01);
    }
}
