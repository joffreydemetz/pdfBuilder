<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Book;
use JDZ\Pdf\Color;
use JDZ\Pdf\Data;
use JDZ\Pdf\Font;
use JDZ\Pdf\Modelizer;
use JDZ\Pdf\Pdf;
use JDZ\Pdf\Toc;
use PHPUnit\Framework\TestCase;

/**
 * Concrete Modelizer subclass for testing.
 */
class ConcreteModelizer extends Modelizer
{
    public bool $headerCalled = false;
    public bool $footerCalled = false;
    public bool $pageCalled = false;

    public function Header(): void
    {
        $this->headerCalled = true;
    }

    public function Footer(): void
    {
        $this->footerCalled = true;
    }

    public function Page(): void
    {
        $this->pageCalled = true;
    }

    // Expose protected methods for testing
    public function exposeLiToDash(string $content): string
    {
        return $this->liToDash($content);
    }

    public function exposeLightColorArray(string $key, int $value): array
    {
        return $this->lightColorArray($key, $value);
    }

    public function getName(): string
    {
        return $this->name;
    }
}

class ModelizerTest extends TestCase
{
    private Pdf $pdf;
    private Data $data;

    protected function setUp(): void
    {
        $this->pdf = new Pdf();
        $this->data = new Data();

        // Set up basic data needed by Modelizer
        $this->data->sets([
            'pagePadding' => 10,
            'marginTop' => 20,
            'marginBottom' => 15,
            'borderWidth' => 1,
            'colors' => [
                'black' => '#000000',
                'white' => '#FFFFFF',
                'themeColor' => '#336699',
            ],
            'fonts' => [
                'default' => 'helvetica',
            ],
        ]);
    }

    private function createModelizer(?Book $book = null): ConcreteModelizer
    {
        return new ConcreteModelizer($this->pdf, $this->data, $book);
    }

    public function testConstructor(): void
    {
        $modelizer = $this->createModelizer();
        $this->assertInstanceOf(Modelizer::class, $modelizer);
    }

    public function testConstructorWithBook(): void
    {
        $book = new Book(new Toc());
        $modelizer = $this->createModelizer($book);
        $this->assertInstanceOf(Modelizer::class, $modelizer);
    }

    public function testDefaultName(): void
    {
        $modelizer = $this->createModelizer();
        $this->assertSame('blank', $modelizer->getName());
    }

    public function testHeaderCanBeCalled(): void
    {
        $modelizer = $this->createModelizer();
        $modelizer->Header();
        $this->assertTrue($modelizer->headerCalled);
    }

    public function testFooterCanBeCalled(): void
    {
        $modelizer = $this->createModelizer();
        $modelizer->Footer();
        $this->assertTrue($modelizer->footerCalled);
    }

    public function testPageCanBeCalled(): void
    {
        $modelizer = $this->createModelizer();
        $modelizer->Page();
        $this->assertTrue($modelizer->pageCalled);
    }

    // --- liToDash ---

    public function testLiToDashConvertsListItems(): void
    {
        $modelizer = $this->createModelizer();
        $html = '<ul><li>Item 1</li><li>Item 2</li></ul>';
        $result = $modelizer->exposeLiToDash($html);

        $this->assertStringContainsString('- Item 1', $result);
        $this->assertStringContainsString('- Item 2', $result);
        $this->assertStringNotContainsString('<ul>', $result);
        $this->assertStringNotContainsString('</ul>', $result);
        $this->assertStringNotContainsString('<li>', $result);
    }

    public function testLiToDashEmptyString(): void
    {
        $modelizer = $this->createModelizer();
        $this->assertSame('', $modelizer->exposeLiToDash(''));
    }

    public function testLiToDashNoListItems(): void
    {
        $modelizer = $this->createModelizer();
        $result = $modelizer->exposeLiToDash('<p>Hello</p>');
        $this->assertSame('<p>Hello</p>', $result);
    }

    // --- lightColorArray ---

    public function testLightColorArray(): void
    {
        $modelizer = $this->createModelizer();
        $result = $modelizer->exposeLightColorArray('themeColor', 50);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function testLightColorArrayReturnsLighterColor(): void
    {
        $modelizer = $this->createModelizer();
        $original = $this->data->getColor('themeColor');
        $lightened = $modelizer->exposeLightColorArray('themeColor', 50);

        // At least one channel should be >= original (lightening makes it brighter)
        $anyLighter = ($lightened[0] >= $original[0])
            || ($lightened[1] >= $original[1])
            || ($lightened[2] >= $original[2]);
        $this->assertTrue($anyLighter);
    }

    // --- load ---

    public function testLoad(): void
    {
        $modelizer = $this->createModelizer();
        $modelizer->load();

        // After load, pageWidth should be set
        $pageWidth = $this->data->get('pageWidth');
        $this->assertNotNull($pageWidth);
        $this->assertGreaterThan(0, $pageWidth);
    }
}
