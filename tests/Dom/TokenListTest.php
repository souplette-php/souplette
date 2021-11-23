<?php declare(strict_types=1);

namespace Souplette\Tests\Dom;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Element;
use Souplette\Dom\Exception\InvalidCharacter;
use Souplette\Dom\Exception\SyntaxError;
use Souplette\Dom\TokenList;

final class TokenListTest extends TestCase
{
    public function testTokenListConstruction()
    {
        $doc = DomBuilder::create()
            ->tag('html')
                ->attr('class', 'foo bar baz')
            ->getDocument();
        $node = $doc->documentElement;
        /** @var Element $node */
        $classList = $node->classList;
        Assert::assertInstanceOf(TokenList::class, $classList);
        Assert::assertCount(3, $classList);
        Assert::assertSame(3, $classList->length);
        Assert::assertSame(['foo', 'bar', 'baz'], iterator_to_array($classList));
        Assert::assertSame('foo bar baz', $classList->value);
        Assert::assertSame('foo bar baz', (string)$classList);
    }

    public function testContains()
    {
        $doc = DomBuilder::create()
            ->tag('html')
                ->attr('class', 'foo bar baz')
            ->getDocument();
        /** @var Element $node */
        $node = $doc->documentElement;
        foreach (['foo', 'bar', 'baz'] as $token) {
            Assert::assertTrue($node->classList->contains($token));
        }
        Assert::assertFalse($node->classList->contains('nope'));
    }

    public function testAdd()
    {
        $doc = DomBuilder::create()
            ->tag('html')
                ->attr('class', 'foo')
            ->getDocument();
        /** @var Element $node */
        $node = $doc->documentElement;
        //
        $node->classList->add('bar', 'baz');
        Assert::assertSame('foo bar baz', $node->getAttribute('class'));
        //
        $node->classList->add('foo', 'baz');
        Assert::assertSame('foo bar baz', $node->getAttribute('class'), 'Should not add duplicates');
    }

    public function testRemove()
    {
        $doc = DomBuilder::create()
            ->tag('html')
                ->attr('class', 'foo bar baz qux')
            ->getDocument();
        /** @var Element $node */
        $node = $doc->documentElement;
        $node->classList->remove('baz', 'bar');
        Assert::assertSame('foo qux', $node->getAttribute('class'));
    }

    public function testAddAfterRemove()
    {
        $doc = DomBuilder::create()
            ->tag('html')
                ->attr('class', 'foo bar baz')
            ->getDocument();
        /** @var Element $node */
        $node = $doc->documentElement;
        $node->classList->remove('bar');
        $node->classList->add('qux');
        Assert::assertSame('foo baz qux', $node->getAttribute('class'));
    }

    public function testReplace()
    {
        $doc = DomBuilder::create()
            ->tag('html')
                ->attr('class', 'foo bar baz')
            ->getDocument();
        /** @var Element $node */
        $node = $doc->documentElement;
        $node->classList->replace('bar', 'qux');
        Assert::assertSame('foo qux baz', $node->getAttribute('class'));
    }

    public function testToggle()
    {
        $doc = DomBuilder::create()
            ->tag('html')
                ->attr('class', 'foo bar baz')
            ->getDocument();
        /** @var Element $node */
        $node = $doc->documentElement;

        $isActive = $node->classList->toggle('bar');
        Assert::assertFalse($isActive);
        Assert::assertSame('foo baz', $node->getAttribute('class'));

        $isActive = $node->classList->toggle('bar');
        Assert::assertTrue($isActive);
        Assert::assertSame('foo baz bar', $node->getAttribute('class'));
    }

    public function testToggleForceParameter()
    {
        $doc = DomBuilder::create()
            ->tag('html')
                ->attr('class', 'foo bar baz')
            ->getDocument();
        /** @var Element $node */
        $node = $doc->documentElement;

        $isActive = $node->classList->toggle('bar', true);
        Assert::assertTrue($isActive);
        Assert::assertSame('foo bar baz', $node->getAttribute('class'));

        $isActive = $node->classList->toggle('bar', false);
        Assert::assertFalse($isActive);
        Assert::assertSame('foo baz', $node->getAttribute('class'));
    }

    public function testValueReflectsAttributeChanges()
    {
        $doc = DomBuilder::create()->tag('html')->getDocument();
        /** @var Element $node */
        $node = $doc->documentElement;
        $classList = $node->classList;
        Assert::assertSame('', $classList->value);
        $node->setAttribute('class', 'foo bar');
        Assert::assertSame('foo bar', $classList->value);
        $node->getAttributeNode('class')->value = 'baz qux';
        Assert::assertSame('baz qux', $classList->value);
    }

    public function testWhitespaceInToken()
    {
        $this->expectException(InvalidCharacter::class);
        $doc = DomBuilder::create()->tag('html')->getDocument();
        /** @var Element $node */
        $node = $doc->documentElement;
        $node->classList->add('foo bar');
    }

    public function testEmptyToken()
    {
        $this->expectException(SyntaxError::class);
        $doc = DomBuilder::create()->tag('html')->getDocument();
        /** @var Element $node */
        $node = $doc->documentElement;
        $node->classList->add('');
    }
}
