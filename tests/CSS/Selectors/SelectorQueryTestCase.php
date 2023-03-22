<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors;

use DOMXPath;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\SelectorQuery;
use Souplette\DOM\Document;
use Souplette\DOM\Element;
use Souplette\Tests\Utils;

class SelectorQueryTestCase extends TestCase
{
    protected static function assertMatches(
        Document $doc,
        string $selectorText,
        array $matchingPaths,
    ) {
        foreach (self::elements($doc) as $element) {
            $path = Utils::elementPath($element);
            $mustMatch = \in_array($path, $matchingPaths);
            $result = SelectorQuery::matches($element, $selectorText);
            $msg = sprintf(
                "%s %s %s",
                $path,
                $mustMatch ? 'must match' : 'must not match',
                $selectorText,
            );
            Assert::assertSame($mustMatch, $result, $msg);
        }
    }

    protected static function assertQueryFirst(
        Document $doc,
        string $selectorText,
        string $expectedPath,
        ?Element $root = null,
    ) {
        if (!$root) $root = $doc;
        $result = SelectorQuery::first($root, $selectorText);
        Assert::assertSame($expectedPath, $result ? Utils::elementPath($result) : null);
    }

    protected static function assertClosest(
        Element $root,
        string $selectorText,
        ?string $expectedPath = null,
    ) {
        $result = SelectorQuery::closest($root, $selectorText);
        Assert::assertSame($expectedPath, $result ? Utils::elementPath($result) : null);
    }

    protected static function loadXml(string $xml): Document
    {
        $doc = new Document();
        $doc->loadXML($xml);
        return $doc;
    }

    /**
     * @return iterable<Element>
     */
    private static function elements(Document $doc): iterable
    {
        $xpath = new DOMXPath($doc);
        yield from $xpath->query('//*');
    }
}
