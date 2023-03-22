<?php declare(strict_types=1);

namespace Souplette\Tests\HTML5Lib\Tokenization;

use IntlChar;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Souplette\HTML\Parser\InputPreprocessor;
use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\Tokenizer;
use Souplette\HTML\Tokenizer\TokenizerState;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\Tests\HTML5Lib\JsonFile;
use Souplette\Tests\ResourceCollector;
use SplDoublyLinkedList;
use UnexpectedValueException;

class TokenizationTest extends TestCase
{
    /**
     * @param array $test
     */
    #[DataProvider('tokenizationProvider')]
    public function testTokenization(array $test)
    {
        $doubleEscaped = $test['doubleEscaped'] ?? false;
        $input = $doubleEscaped ? self::unescape($test['input']) : $test['input'];
        $expectedTokens = self::convertHtml5LibTokens($test['output'], $doubleEscaped);
        // TODO: test parse errors.
        //$expectedErrors = self::convertHtml5LibErrors($test['errors'] ?? [], $input);
        $appropriateEndTag = $test['lastStartTag'] ?? null;

        // html5lib tests expect leading BOM to pass through...
        //$input = InputPreprocessor::removeBOM($input);
        $input = InputPreprocessor::normalizeNewlines($input);
        $tokenizer = new Tokenizer($input);
        $initialStates = self::convertHtml5LibStates($test['initialStates'] ?? []);
        foreach ($initialStates as $state) {
            $tokens = iterator_to_array($tokenizer->tokenize($state, $appropriateEndTag), false);
            array_pop($tokens);
            $tokens = self::concatenateCharacterTokens($tokens);
            Assert::assertEquals($expectedTokens, $tokens, 'Tokens differ.');
            //if ($expectedErrors) {
            //    Assert::assertEquals($expectedErrors, $tokenizer->getErrors(), 'Errors differ.');
            //}
        }
    }

    public static function tokenizationProvider(): iterable
    {
        foreach (self::collectJsonFiles() as $relPath => $jsonFile) {
            foreach ($jsonFile as $i => $test) {
                $key = sprintf('%s::%s', $relPath, $i);
                yield $key => [$test];
            }
        }
    }

    /**
     * @return iterable<JsonFile>
     */
    private static function collectJsonFiles(): iterable
    {
        foreach (ResourceCollector::collect('html5lib-tests/tokenizer', '*.test') as $relPath => $fileInfo) {
            if ($relPath === 'xmlViolation.test') {
                // Skip XML violation tests since they are dependant on the DOM implementation
                continue;
            }
            yield $relPath => new JsonFile($fileInfo->getPathname());
        }
    }

    private static function convertHtml5LibTokens(array $input, bool $doubleEscaped = false): array
    {
        $tokens = [];
        foreach ($input as $item) {
            $type = $item[0];
            $args = \array_slice($item, 1);
            if ($doubleEscaped) {
                $args[0] = self::unescape($args[0]);
            }
            switch ($type) {
                case 'DOCTYPE':
                    $tokens[] = Token::doctype($args[0] ?? '', $args[1] ?? null, $args[2] ?? null, !($args[3] ?? true));
                    break;
                case 'Comment':
                    $tokens[] = Token::comment($args[0]);
                    break;
                case 'Character':
                    $tokens[] = Token::character($args[0]);
                    break;
                case 'StartTag':
                    $attrs = $args[1] ?? null;
                    if (empty($attrs)) {
                        $attrs = null;
                    }
                    if ($doubleEscaped && $attrs) {
                        foreach ($attrs as $name => $value) {
                            unset($attrs[$name]);
                            $attrs[self::unescape((string)$name)] = self::unescape($value);
                        }
                    }
                    $tokens[] = Token::startTag($args[0], $args[2] ?? false, $attrs);
                    break;
                case 'EndTag':
                    $tokens[] = Token::endTag($args[0]);
                    break;
                default:
                    throw new UnexpectedValueException($type);
            }
        }

        return $tokens;
    }

    /**
     * @param Token[] $tokens
     * @return Token[]
     */
    private static function concatenateCharacterTokens(array $tokens): array
    {
        $output = new SplDoublyLinkedList();
        foreach ($tokens as $i => $token) {
            if ($output->isEmpty()) {
                $output->push($token);
                continue;
            }
            $last = $output->top();
            if ($token::KIND === TokenKind::Characters && $last::KIND === $token::KIND) {
                $last->data .= $token->data;
            } else {
                $output->push($token);
            }
        }

        return iterator_to_array($output);
    }

    private static function unescape(string $input): string
    {
        return preg_replace_callback('/\\\\u(?P<cp>[a-zA-Z0-9]{4})/', function($matches) {
            return IntlChar::chr(hexdec($matches['cp']));
        }, $input);
    }

    private static function convertHtml5LibErrors(array $errors, string $input): array
    {
        $output = [];
        foreach ($errors as $error) {
            $output[] = [$error['code'], self::sourcePositionToOffset($input, $error['line'], $error['col'])];
        }

        return $output;
    }

    private static function convertHtml5LibStates(array $stateNames): array
    {
        if (!$stateNames) {
            return [TokenizerState::DATA];
        }
        $states = [];
        foreach ($stateNames as $stateName) {
            $name = str_replace([' ', '_STATE'], ['_', ''], strtoupper($stateName));
            $states[] = \constant(sprintf('%s::%s', TokenizerState::class, $name));
        }
        return $states;
    }

    public static function sourcePositionToOffset(string $source, int $lineno, int $col): int
    {
        if ($lineno === 1) {
            return $col - 1;
        }

        $lines = preg_split('/\r\n|\n/', $source, -1, PREG_SPLIT_OFFSET_CAPTURE);
        $line = $lines[$lineno - 1] ?? null;
        if ($line === null) {
            throw new RuntimeException("No such line: $lineno");
        }
        [, $offset] = $line;

        return max(0, min($offset + $col - 1, \strlen($source)));
    }
}
