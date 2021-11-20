<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\SelectorQuery;
use Souplette\Tests\Utils;

class SelectorQueryTestCase extends TestCase
{
    protected static function assertMatches(
        \DOMDocument $doc,
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

    protected static function assertQueryAll(
        \DOMDocument $doc,
        string $selectorText,
        array $expectedPaths,
        ?\DOMElement $root = null,
    ) {
        if (!$root) $root = $doc;
        $results = SelectorQuery::queryAll($root, $selectorText) ?? [];
        $actualPaths = array_map(fn($el) => Utils::elementPath($el), $results);
        Assert::assertEquals($expectedPaths, $actualPaths);
    }

    protected static function assertQueryFirst(
        \DOMDocument $doc,
        string $selectorText,
        string $expectedPath,
        ?\DOMElement $root = null,
    ) {
        if (!$root) $root = $doc;
        $result = SelectorQuery::queryFirst($root, $selectorText);
        Assert::assertSame($expectedPath, $result ? Utils::elementPath($result) : null);
    }

    protected static function assertClosest(
        \DOMElement $root,
        string $selectorText,
        ?string $expectedPath = null,
    ) {
        $result = SelectorQuery::closest($root, $selectorText);
        Assert::assertSame($expectedPath, $result ? Utils::elementPath($result) : null);
    }

    protected static function loadXml(string $xml): \DOMDocument
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        return $doc;
    }

    /**
     * @return iterable<\DOMElement>
     */
    private static function elements(\DOMDocument $doc): iterable
    {
        $xpath = new \DOMXPath($doc);
        yield from $xpath->query('//*');
    }
}
