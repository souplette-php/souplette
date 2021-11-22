<?php declare(strict_types=1);

namespace Souplette\Tests\Html5Lib\TreeConstruction;

use PHPUnit\Framework\Assert;
use Souplette\Dom\Node\HtmlDocument;
use Souplette\Html\Parser;

final class TreeConstructionAssert
{
    public static function assertTestPasses(TreeConstructionTestDTO $test)
    {
        $parser = new Parser($test->scriptingEnabled);
        $serializer = new Serializer();
        if ($test->contextElement) {
            $doc = new HtmlDocument();
            [$ns, $localName] = $test->contextElement;
            $context = $doc->createElementNS($ns, $localName);
            $nodes = $parser->parseFragment($context, $test->input, 'utf-8');
            $frag = $doc->createDocumentFragment();
            foreach ($nodes as $node) {
                $node = $doc->importNode($node, true);
                $frag->appendChild($node);
            }
            $result = $serializer->serialize($frag);
        } else {
            $doc = $parser->parse($test->input, 'utf-8');
            $result = $serializer->serialize($doc);
        }

        $result = self::normalizeTreeDump($result);
        if ($test->mustFail) {
            $message = sprintf('Must fail (%s), input: %s', $test->mustFailReason, $test->input);
            Assert::assertNotSame($test->output, $result, $message);
        } else {
            $message = sprintf('Input html: %s', $test->input);
            Assert::assertSame($test->output, $result, $message);
        }
    }

    private static function normalizeTreeDump(string $treeDump): string
    {
        return preg_replace('/^\| {2}/m', '', $treeDump);
    }
}
