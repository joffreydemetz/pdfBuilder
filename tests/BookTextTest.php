<?php

namespace JDZ\Pdf\Tests;

use JDZ\Pdf\BookText;
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

    public function testSetLanguageReturnsThis(): void
    {
        $result = $this->text->setLanguage('en');
        $this->assertSame($this->text, $result);
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

    public function testAddStringsReturnsThis(): void
    {
        $result = $this->text->addStrings('en', ['KEY' => 'value']);
        $this->assertSame($this->text, $result);
    }

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

    public function testGetReturnsKeyWhenNotFoundInAnyLanguage(): void
    {
        $this->text->addStrings('en', ['HELLO' => 'Hello']);
        $this->text->setLanguage('fr');
        $this->assertSame('MISSING', $this->text->get('missing'));
    }

    public function testGetCurrentLanguageTakesPrecedence(): void
    {
        $this->text->addStrings('en', ['HELLO' => 'Hello']);
        $this->text->addStrings('fr', ['HELLO' => 'Bonjour']);
        $this->text->setLanguage('fr');
        $this->assertSame('Bonjour', $this->text->get('hello'));
    }

    // --- plural ---

    public function testPluralWithZero(): void
    {
        $this->text->addStrings('en', [
            'ITEM_0' => 'items',
            'ITEM_1' => 'item',
            'ITEM_MORE' => 'items',
        ]);
        $this->assertSame('0 items', $this->text->plural('item', 0));
    }

    public function testPluralWithOne(): void
    {
        $this->text->addStrings('en', [
            'ITEM_0' => 'items',
            'ITEM_1' => 'item',
            'ITEM_MORE' => 'items',
        ]);
        $this->assertSame('1 item', $this->text->plural('item', 1));
    }

    public function testPluralWithMany(): void
    {
        $this->text->addStrings('en', [
            'ITEM_0' => 'items',
            'ITEM_1' => 'item',
            'ITEM_MORE' => 'items',
        ]);
        $this->assertSame('5 items', $this->text->plural('item', 5));
    }

    public function testPluralWithTwo(): void
    {
        $this->text->addStrings('en', [
            'ITEM_0' => 'items',
            'ITEM_1' => 'item',
            'ITEM_MORE' => 'items',
        ]);
        $this->assertSame('2 items', $this->text->plural('item', 2));
    }

    // --- pluralSimple ---

    public function testPluralSimpleWithZero(): void
    {
        $this->text->addStrings('en', [
            'THING_0' => 'no things',
            'THING_1' => 'one thing',
            'THING_MORE' => 'many things',
        ]);
        $this->assertSame('no things', $this->text->pluralSimple('thing', 0));
    }

    public function testPluralSimpleWithOne(): void
    {
        $this->text->addStrings('en', [
            'THING_0' => 'no things',
            'THING_1' => 'one thing',
            'THING_MORE' => 'many things',
        ]);
        $this->assertSame('one thing', $this->text->pluralSimple('thing', 1));
    }

    public function testPluralSimpleWithMany(): void
    {
        $this->text->addStrings('en', [
            'THING_0' => 'no things',
            'THING_1' => 'one thing',
            'THING_MORE' => 'many things',
        ]);
        $this->assertSame('many things', $this->text->pluralSimple('thing', 10));
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

    public function testSprintfNoArgs(): void
    {
        $result = $this->text->sprintf('key');
        // With one arg (the key itself), it should still process
        $this->assertIsString($result);
    }
}
