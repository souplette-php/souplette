<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Html5Lib;

use ju1ius\HtmlParser\Tests\ResourceCollector;
use ju1ius\HtmlParser\Tests\Tokenizer\TokenizerAssert;
use ju1ius\HtmlParser\Tokenizer\Token;
use PHPUnit\Framework\TestCase;

class TokenizationTest extends TestCase
{
    /**
     * @dataProvider tokenizationProvider
     * @param array $test
     */
    public function testTokenization(array $test)
    {
        $input = $test['input'];
        $expected = self::convertHtml5LibTokens($test['output']);
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public function tokenizationProvider()
    {
        foreach ($this->collectJsonFiles() as $relPath => $jsonFile) {
            foreach ($jsonFile as $i => $test) {
                $key = sprintf('%s [%s]', $relPath, $i);
                yield $key => [$test];
            }
        }
    }

    private static function convertHtml5LibTokens(array $input): array
    {
        $tokens = [];
        foreach ($input as $item) {
            $type = $item[0];
            $args = array_slice($item, 1);
            switch ($type) {
                case 'DOCTYPE':
                    $tokens[] = Token::doctype(...$args);
                    break;
                case 'Comment':
                    $tokens[] = Token::comment($args[0] ?? '');
                    break;
                case 'Character':
                    $tokens[] = Token::character($args[0] ?? '');
                    break;
                case 'StartTag':
                    $tokens[] = Token::startTag($args[0], $args[2] ?? false, $args[1] ?? null);
                    break;
                case 'EndTag':
                    $tokens[] = Token::endTag($args[0]);
                    break;
                default:
                    throw new \UnexpectedValueException($type);
            }
        }

        return $tokens;
    }

    /**
     * @return \Generator|JsonFile[]
     */
    private function collectJsonFiles()
    {
        $path = __DIR__.'/../resources/html5lib-tests/tokenizer';
        foreach (ResourceCollector::collect($path, 'test') as $relPath => $fileInfo) {
            yield $relPath => new JsonFile($fileInfo->getPathname());
        }
    }
}
