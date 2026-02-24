<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Toc;
use PHPUnit\Framework\TestCase;

class TocTest extends TestCase
{
    private Toc $toc;

    protected function setUp(): void
    {
        $this->toc = new Toc();
    }

    // --- hasMarks / getMarks ---

    public function testNewTocHasNoMarks(): void
    {
        $this->assertFalse($this->toc->hasMarks());
    }

    public function testGetMarksReturnsFalseWhenEmpty(): void
    {
        $this->assertFalse($this->toc->getMarks());
    }

    // --- getItem ---

    public function testGetItemCreatesNewEntry(): void
    {
        $item = $this->toc->getItem('chapter1');
        $this->assertIsObject($item);
        $this->assertSame(1, $item->page);
        $this->assertSame([], $item->positions);
    }

    public function testGetItemReturnsSameEntryOnSecondCall(): void
    {
        $item1 = $this->toc->getItem('chapter1');
        $item2 = $this->toc->getItem('chapter1');
        $this->assertSame($item1, $item2);
    }

    public function testGetItemCreatesEntryMakesHasMarksTrue(): void
    {
        $this->toc->getItem('chapter1');
        $this->assertTrue($this->toc->hasMarks());
    }

    public function testGetItemWithIntKey(): void
    {
        $item = $this->toc->getItem(1);
        $this->assertIsObject($item);
        $this->assertSame(1, $item->page);
    }

    // --- setPosition ---

    public function testSetPositionReturnsThis(): void
    {
        $result = $this->toc->setPosition('ch1', [
            'p' => 1, 'x' => 10, 'y' => 20, 'w' => 50, 'h' => 10,
        ]);
        $this->assertSame($this->toc, $result);
    }

    public function testSetPositionAddsPositionToItem(): void
    {
        $this->toc->setPosition('ch1', [
            'p' => 1, 'x' => 10, 'y' => 20, 'w' => 50, 'h' => 10,
        ]);

        $item = $this->toc->getItem('ch1');
        $this->assertCount(1, $item->positions);
        $this->assertSame(1, $item->positions[0]->p);
        $this->assertSame(10, $item->positions[0]->x);
        $this->assertSame(20, $item->positions[0]->y);
        $this->assertSame(50, $item->positions[0]->w);
        $this->assertSame(10, $item->positions[0]->h);
    }

    public function testSetPositionMultiplePositions(): void
    {
        $this->toc->setPosition('ch1', [
            'p' => 1, 'x' => 10, 'y' => 20, 'w' => 50, 'h' => 10,
        ]);
        $this->toc->setPosition('ch1', [
            'p' => 2, 'x' => 15, 'y' => 25, 'w' => 55, 'h' => 12,
        ]);

        $item = $this->toc->getItem('ch1');
        $this->assertCount(2, $item->positions);
    }

    // --- setPage ---

    public function testSetPageUpdatesPageNumber(): void
    {
        $this->toc->getItem('ch1');
        $this->toc->setPage('ch1', 5);

        $item = $this->toc->getItem('ch1');
        $this->assertSame(5, $item->page);
    }

    public function testSetPageReturnsThisForExistingKey(): void
    {
        $this->toc->getItem('ch1');
        $result = $this->toc->setPage('ch1', 3);
        $this->assertSame($this->toc, $result);
    }

    public function testSetPageReturnsNullForNonExistentKey(): void
    {
        $result = $this->toc->setPage('nonexistent', 3);
        $this->assertNull($result);
    }

    // --- getMarks ---

    public function testGetMarksReturnsArrayWhenPopulated(): void
    {
        $this->toc->setPosition('ch1', [
            'p' => 1, 'x' => 10, 'y' => 20, 'w' => 50, 'h' => 10,
        ]);
        $this->toc->setPage('ch1', 3);

        $marks = $this->toc->getMarks();
        $this->assertIsArray($marks);
        $this->assertArrayHasKey('ch1', $marks);
        $this->assertSame(3, $marks['ch1']->page);
    }

    public function testMultipleItems(): void
    {
        $this->toc->setPosition('ch1', [
            'p' => 1, 'x' => 10, 'y' => 20, 'w' => 50, 'h' => 10,
        ]);
        $this->toc->setPosition('ch2', [
            'p' => 2, 'x' => 10, 'y' => 30, 'w' => 50, 'h' => 10,
        ]);

        $marks = $this->toc->getMarks();
        $this->assertCount(2, $marks);
        $this->assertArrayHasKey('ch1', $marks);
        $this->assertArrayHasKey('ch2', $marks);
    }
}
