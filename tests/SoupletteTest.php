<?php declare(strict_types=1);

namespace Souplette\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Document;
use Souplette\Dom\Implementation;
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
        $expected = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body></body></html>';
        $doc = (new Implementation)->createShell();
        $html = Souplette::serializeDocument($doc);
        Assert::assertSame($expected, $html);
    }
}
