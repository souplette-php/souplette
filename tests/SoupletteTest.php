<?php declare(strict_types=1);

namespace Souplette\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\node\Document;
use Souplette\Dom\node\Implementation;
use Souplette\Souplette;

final class SoupletteTest extends TestCase
{
    public function testParseHtml()
    {
        $html = '<!doctype html>';
        $doc = Souplette::parseHtml($html, 'utf-8');
        Assert::assertInstanceOf(Document::class, $doc);
    }

    public function testSerializeDocument()
    {
        $expected = '<!DOCTYPE html><html><head><title>title</title></head><body></body></html>';
        $doc = (new Implementation)->createHTMLDocument('title');
        $html = Souplette::serializeDocument($doc);
        Assert::assertSame($expected, $html);
    }
}
