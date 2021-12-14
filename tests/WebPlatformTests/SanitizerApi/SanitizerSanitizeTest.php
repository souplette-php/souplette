<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\SanitizerApi;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Document;
use Souplette\Dom\DocumentFragment;
use Souplette\Html\HtmlParser;
use Souplette\Html\HtmlSerializer;
use Souplette\Html\Sanitizer\Sanitizer;
use Souplette\Html\Sanitizer\SanitizerConfig;
use Souplette\Souplette;
use Souplette\Tests\Dom\DomBuilder;

final class SanitizerSanitizeTest extends TestCase
{
    /**
     * @dataProvider jsonTestCasesProvider
     */
    public function testSanitizeDocument(?SanitizerConfig $config, string $input, string $expected)
    {
        $sanitizer = $config ? new Sanitizer($config) : Sanitizer::getDefault();
        $doc = Souplette::parseHtml("<!DOCTYPE html><body>{$input}");
        $fragment = $sanitizer->sanitize($doc);
        Assert::assertInstanceOf(DocumentFragment::class, $fragment);
        $result = (new HtmlSerializer())->serialize($fragment);
        Assert::assertSame($expected, $result);
    }

    /**
     * @dataProvider jsonTestCasesProvider
     */
    public function testSanitizeTemplate(?SanitizerConfig $config, string $input, string $expected)
    {
        $sanitizer = $config ? new Sanitizer($config) : Sanitizer::getDefault();
        $doc = new Document();
        $nodes = (new HtmlParser())->parseFragment($doc->createElement('template'), $input);
        $content = $doc->createDocumentFragment();
        $content->append(...$nodes);
        $fragment = $sanitizer->sanitize($content);
        Assert::assertInstanceOf(DocumentFragment::class, $fragment);
        $result = (new HtmlSerializer())->serialize($fragment);
        Assert::assertSame($expected, $result);
    }

    public function jsonTestCasesProvider(): iterable
    {
        $data = json_decode(file_get_contents(__DIR__ . '/testcases.json'), true);
        foreach ($data as $i => $datum) {
            $testCase = self::parseTestCase($datum);
            if (!$testCase) continue;
            $key = sprintf('#%d %s', $i, $testCase['message']);
            yield $key => [
                $testCase['config'],
                $testCase['input'],
                $testCase['expected'],
            ];
        }
    }

    private static function parseTestCase(array $testCase): ?array
    {
        $value = $testCase['value'] ?? null;
        if (!$value || !\is_scalar($value)) return null;
        $filterCrap = fn($crap) => array_filter($crap, \is_string(...));

        $configInput = $testCase['config_input'] ?? [];
        $config = null;
        if ($configInput) {
            $config = SanitizerConfig::create();
            if ($configInput['allowCustomElements'] ?? false) {
                $config->allowCustomElements();
            }
            if ($configInput['allowComments'] ?? false) {
                $config->allowComments();
            }
            if ($allowed = $configInput['allowElements'] ?? null) {
                $config->allowElements(...$filterCrap($allowed));
            }
            if ($drop = $configInput['dropElements'] ?? null) {
                $config->dropElements(...$filterCrap($drop));
            }
            if ($block = $configInput['blockElements'] ?? null) {
                $config->blockElements(...$filterCrap($block));
            }
            if ($allowed = $configInput['allowAttributes'] ?? null) {
                foreach ($allowed as $attr => $elements) {
                    $config->allowAttribute($attr, $filterCrap($elements));
                }
            }
            if ($drop = $configInput['dropAttributes'] ?? null) {
                foreach ($drop as $attr => $elements) {
                    $config->dropAttribute($attr, $filterCrap($elements));
                }
            }
        }

        return [
            'config' => $config,
            'input' => (string)$value,
            'expected' => $testCase['result'],
            'message' => $testCase['message'],
        ];
    }
}
