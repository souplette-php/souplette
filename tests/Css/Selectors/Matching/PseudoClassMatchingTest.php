<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Matching;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Tests\Html\DomBuilder;

final class PseudoClassMatchingTest extends TestCase
{
    use XpathMatchingTrait;

    private static function assertResultsHaveTargetAttribute(\DOMDocument $document, string $selector)
    {
        $nodes = self::querySelector($document, $selector);
        foreach ($nodes as $node) {
            Assert::assertTrue($node->hasAttribute('target'));
        }
    }

    public function testRoot()
    {
        $doc = DomBuilder::create()
            ->tag('div')->attr('target', '')
            ->tag('div')
            ->getDocument();
        self::assertResultsHaveTargetAttribute($doc, ':root');
    }

    public function testEmpty()
    {
        // TODO: figure out what whitespace characters could be excluded when matching :empty
        // https://drafts.csswg.org/selectors/#the-empty-pseudo
        $doc = DomBuilder::create()->tag('html')->tag('body')
            ->tag('div')
                ->tag('a')->attr('target', '')->close()
                ->tag('a')->attr('target', '')
                    ->text("\n \t ")
                ->close()
            ->getDocument();

        self::assertResultsHaveTargetAttribute($doc, ':empty');
    }

    public function testFirstChild()
    {
        $doc = DomBuilder::create()
            ->tag('ul')->attr('target', '')
                ->tag('a')->attr('target', '')->close()
                ->tag('b')->close()
            ->close()
            ->tag('div')
            ->getDocument();
        self::assertResultsHaveTargetAttribute($doc, ':first-child');
    }

    public function testLastChild()
    {
        $doc = DomBuilder::create()
            ->tag('ul')
                ->tag('a')->close()
                ->tag('b')->attr('target', '')->close()
            ->close()
            ->tag('div')->attr('target', '')
            ->getDocument();
        self::assertResultsHaveTargetAttribute($doc, ':last-child');
    }

    public function testOnlyChild()
    {
        $doc = DomBuilder::create()
            ->tag('ul')
                ->tag('a')->attr('target', '')->close()
            ->close()
            ->tag('div')
                ->tag('a')->attr('target', '')->close()
                ->tag('b')->attr('target', '')->close()
            ->getDocument();
        self::assertResultsHaveTargetAttribute($doc, ':only-child');
    }
}
