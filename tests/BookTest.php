<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\Book;
use JDZ\Pdf\BookText;
use JDZ\Pdf\Toc;
use PHPUnit\Framework\TestCase;

class BookTest extends TestCase
{
    public function testConstructWithoutToc(): void
    {
        $book = new Book();
        $this->assertNull($book->toc);
    }

    public function testConstructCreatesBookText(): void
    {
        $book = new Book();
        $this->assertInstanceOf(BookText::class, $book->text);
    }

    public function testConstructWithToc(): void
    {
        $toc = new Toc();
        $book = new Book($toc);
        $this->assertSame($toc, $book->toc);
    }

    public function testTextIsUsable(): void
    {
        $book = new Book();
        $book->text->addStrings('en', ['TITLE' => 'My Book']);
        $this->assertSame('My Book', $book->text->get('title'));
    }

    public function testTocIsUsable(): void
    {
        $toc = new Toc();
        $book = new Book($toc);
        $book->toc->setPosition('ch1', [
            'p' => 1, 'x' => 0, 'y' => 0, 'w' => 100, 'h' => 10,
        ]);
        $this->assertTrue($book->toc->hasMarks());
    }
}
