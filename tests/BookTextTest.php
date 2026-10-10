<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\BookText;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BookTextTest extends TestCase
{
    private BookText $text;

    protected function setUp(): void
    {
        $this->text = new BookText();
    }

    // --- Language management ---

    public function testDefaultLanguageIsEnglish(): void
    {
        $this->text->addStrings('en', ['HELLO' => 'Hello']);
        $this->assertSame('Hello', $this->text->get('hello'));
    }

    public function testSetLanguage(): void
    {
        $this->text->addStrings('fr', ['HELLO' => 'Bonjour']);
        $this->text->setLanguage('fr');
        $this->assertSame('Bonjour', $this->text->get('hello'));
    }

    public function testSetInvalidLanguageFallsBackToDefault(): void
    {
        $this->text->addStrings('en', ['HELLO' => 'Hello']);
        $this->text->setLanguage('zz');
        $this->assertSame('Hello', $this->text->get('hello'));
    }

    public function testSetEmptyLanguageFallsBackToDefault(): void
    {
        $this->text->addStrings('en', ['HELLO' => 'Hello']);
        $this->text->setLanguage('');
        $this->assertSame('Hello', $this->text->get('hello'));
    }

    public function testSetAvailableLanguages(): void
    {
        $result = $this->text->setAvailableLanguages(['de', 'es']);
        $this->assertSame($this->text, $result);

        $this->text->addStrings('de', ['HELLO' => 'Hallo']);
        $this->text->setLanguage('de');
        $this->assertSame('Hallo', $this->text->get('hello'));
    }

    public function testAddAvailableLanguage(): void
    {
        $result = $this->text->addAvailableLanguage('de');
        $this->assertSame($this->text, $result);

        $this->text->addStrings('de', ['HELLO' => 'Hallo']);
        $this->text->setLanguage('de');
        $this->assertSame('Hallo', $this->text->get('hello'));
    }

    // --- addStrings ---

    public function testAddStringsMergesWithExisting(): void
    {
        $this->text->addStrings('en', ['KEY1' => 'value1']);
        $this->text->addStrings('en', ['KEY2' => 'value2']);

        $this->assertSame('value1', $this->text->get('key1'));
        $this->assertSame('value2', $this->text->get('key2'));
    }

    // --- get ---

    public function testGetConvertsKeyToUppercase(): void
    {
        $this->text->addStrings('en', ['MYKEY' => 'myvalue']);
        $this->assertSame('myvalue', $this->text->get('mykey'));
        $this->assertSame('myvalue', $this->text->get('MYKEY'));
        $this->assertSame('myvalue', $this->text->get('MyKey'));
    }

    public function testGetReturnsKeyWhenNotFound(): void
    {
        $this->assertSame('UNKNOWN', $this->text->get('unknown'));
    }

    public function testGetFallsBackToDefaultLanguage(): void
    {
        $this->text->addStrings('en', ['HELLO' => 'Hello']);
        $this->text->setLanguage('fr');
        // French has no 'HELLO', should fall back to English
        $this->assertSame('Hello', $this->text->get('hello'));
    }

    public function testGetCurrentLanguageTakesPrecedence(): void
    {
        $this->text->addStrings('en', ['HELLO' => 'Hello']);
        $this->text->addStrings('fr', ['HELLO' => 'Bonjour']);
        $this->text->setLanguage('fr');
        $this->assertSame('Bonjour', $this->text->get('hello'));
    }

    // --- plural / pluralSimple ---

    private function addFileStrings(): void
    {
        $this->text->addStrings('en', [
            'FILE_0' => 'files (none)',
            'FILE_1' => 'file',
            'FILE_MORE' => 'files',
        ]);
    }

    public static function counts(): array
    {
        return [
            'zero uses _0' => [0, '0 files (none)', 'files (none)'],
            'one uses _1' => [1, '1 file', 'file'],
            'two uses _MORE' => [2, '2 files', 'files'],
            'many uses _MORE' => [10, '10 files', 'files'],
        ];
    }

    #[DataProvider('counts')]
    public function testPluralPrefixesTheCount(int $count, string $plural, string $simple): void
    {
        $this->addFileStrings();

        $this->assertSame($plural, $this->text->plural('file', $count));
    }

    #[DataProvider('counts')]
    public function testPluralSimpleReturnsTheFormOnly(int $count, string $plural, string $simple): void
    {
        $this->addFileStrings();

        $this->assertSame($simple, $this->text->pluralSimple('file', $count));
    }

    // --- sprintf ---

    public function testSprintf(): void
    {
        $this->text->addStrings('en', [
            'GREETING' => 'Hello %1$s, you have %2$s messages',
        ]);
        $result = $this->text->sprintf('greeting', 'John', '5');
        $this->assertSame('Hello John, you have 5 messages', $result);
    }

    public function testSprintfWithPlaceholderSyntax(): void
    {
        $this->text->addStrings('en', [
            'MSG' => 'Hello [[%1:name]], welcome to [[%2:place]]',
        ]);
        $result = $this->text->sprintf('msg', 'Alice', 'Wonderland');
        $this->assertSame('Hello Alice, welcome to Wonderland', $result);
    }

    // --- printf ---

    public function testPrintfPrintsTheTranslatedFormat(): void
    {
        $this->text->addStrings('fr', ['GREETING' => 'Bonjour %s, %d messages']);
        $this->text->setLanguage('fr');

        $this->expectOutputString('Bonjour Bob, 3 messages');

        $this->text->printf('greeting', 'Bob', 3);
    }

    /**
     * printf() is sprintf() printed: it skipped the [[%n:name]] placeholders
     * (ValueError) and returned the byte count as a string.
     */
    public function testPrintfPrintsAndReturnsWhatSprintfBuilds(): void
    {
        $this->text->addStrings('en', ['MSG' => 'Hello [[%1:name]], welcome to [[%2:place]]']);

        $this->expectOutputString('Hello Alice, welcome to Wonderland');

        $this->assertSame('Hello Alice, welcome to Wonderland', $this->text->printf('msg', 'Alice', 'Wonderland'));
    }
}
