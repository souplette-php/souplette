<?php declare(strict_types=1);

namespace JoliPotage\Tests\Html5Lib\TreeConstruction;

use JoliPotage\Html\Parser\Parser;
use PHPUnit\Framework\Assert;

final class TreeConstructionAssert
{
    public static function assertTestPasses(TreeConstructionTestDTO $test)
    {
        $parser = new Parser($test->scriptingEnabled);
        $serializer = new Serializer();
        if ($test->contextElement) {
            $doc = new \DOMDocument();
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
        if ($test->shouldFail) {
            Assert::assertNotSame($test->output, $result);
        } else {
            Assert::assertSame($test->output, $result);
        }
    }

    private static function normalizeTreeDump(string $treeDump): string
    {
        return preg_replace('/^\| {2}/m', '', $treeDump);
    }
}
