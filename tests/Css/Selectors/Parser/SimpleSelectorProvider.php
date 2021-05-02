<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Parser;

use Souplette\Css\Selectors\Node\Functional\NthChild;
use Souplette\Css\Selectors\Node\Functional\NthCol;
use Souplette\Css\Selectors\Node\Functional\NthLastChild;
use Souplette\Css\Selectors\Node\Functional\NthLastCol;
use Souplette\Css\Selectors\Node\Functional\NthLastOfType;
use Souplette\Css\Selectors\Node\Functional\NthOfType;
use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\Simple\AttributeSelector;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Node\Simple\UniversalSelector;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Css\Syntax\Tokenizer\Token\Functional;
use Souplette\Css\Syntax\Tokenizer\Token\Number;
use Souplette\Css\Syntax\Tokenizer\Token\RightParen;
use Souplette\Tests\Utils;

final class SimpleSelectorProvider
{
    public static function typeSelectors(): \Generator
    {
        yield 'element in any namespace' => [
            '*|foo',
            new TypeSelector('foo', '*')
        ];
        yield 'element with no namespace' => [
            '|bar',
            new TypeSelector('bar', null)
        ];
        yield 'universal in any namespace' => [
            '*|*',
            new UniversalSelector('*')
        ];
        yield 'universal with no namespace' => [
            '|*',
            new UniversalSelector(null)
        ];
        yield 'element in undeclared default namespace' => [
            'foo',
            new TypeSelector('foo', '*')
        ];
        yield 'element in default namespace' => [
            'foo',
            new TypeSelector('foo', 'https://example.org/default'),
            ['' => 'https://example.org/default'],
        ];
    }

    public static function namespacedTypeSelectors(): \Generator
    {
        $namespaces = [
            'foo' => 'https://example.org/foo',
        ];
        yield 'namespaced element' => [
            'foo|bar',
            new TypeSelector('bar', $namespaces['foo']),
            $namespaces,
        ];
        yield 'namespaced universal' => [
            'foo|*',
            new UniversalSelector($namespaces['foo']),
            $namespaces,
        ];
    }

    public static function simpleFunctionalPseudoClasses(): \Generator
    {
        // An+B syntax is tested separately so we just ensure the correct classes are returned
        // TODO: test nth(-last)?-child(An+B of S) when selector list is implemented
        yield ':nth-child(1)' => [':nth-child(1)', new NthChild(new AnPlusB(0, 1))];
        yield ':nth-last-child(1)' => [':nth-last-child(1)', new NthLastChild(new AnPlusB(0, 1))];
        yield ':nth-of-type(1)' => [':nth-of-type(1)', new NthOfType(new AnPlusB(0, 1))];
        yield ':nth-last-of-type(1)' => [':nth-last-of-type(1)', new NthLastOfType(new AnPlusB(0, 1))];
        yield ':nth-col(1)' => [':nth-col(1)', new NthCol(new AnPlusB(0, 1))];
        yield ':nth-last-col(1)' => [':nth-last-col(1)', new NthLastCol(new AnPlusB(0, 1))];
        // unknown functions match anything
        yield ':foo()' => [':foo()', new FunctionalSelector('foo')];
        yield ':foo(bar(42))' => [':foo(bar(42))', new FunctionalSelector('foo', [
            new Functional('bar', 5),
            new Number('42', 9),
            new RightParen(11),
        ])];
    }

    public static function attributeSelectors(): \Generator
    {
        $names = ['foo'];
        $prefixes = [null, 'ns', '*'];
        $namespaces = [
            'ns' => 'https://example.org/ns',
        ];
        $ops = ['=', '^=', '$=', '|=', '*='];
        $values = ['bar', '"bar"'];
        $modifiers = [null, 'i', 's'];

        foreach (Utils::cartesianProduct([$names, $prefixes]) as [$name, $prefix]) {
            $ns = match ($prefix) {
                null, '*' => $prefix,
                default => $namespaces[$prefix],
            };
            $selector = new AttributeSelector($name, $ns);
            $input = self::attributeToString(false, $name, $prefix);
            yield $input => [$input, $selector, $namespaces];
            $input = self::attributeToString(true, $name, $prefix);
            yield $input => [$input, $selector, $namespaces];
        }

        $all = [
            $names,
            $prefixes,
            $ops,
            $values,
            $modifiers,
        ];
        foreach (Utils::cartesianProduct($all) as [$name, $prefix, $op, $v, $mod]) {
            $value = preg_replace('/^["\'](.*)["\']$/', '$1', $v);
            $ns = match ($prefix) {
                null, '*' => $prefix,
                default => $namespaces[$prefix],
            };
            $selector = new AttributeSelector($name, $ns, $op, $value, $mod);
            $input = self::attributeToString(false, $name, $prefix, $op, $v, $mod);
            yield $input => [$input, $selector, $namespaces];
            $input = self::attributeToString(true, $name, $prefix, $op, $v, $mod);
            yield $input => [$input, $selector, $namespaces];
        }

        yield 'no namespace selector' => [
            '[ |foo ]',
            new AttributeSelector('foo', null),
        ];
    }

    private static function attributeToString(
        bool $addWhitespace,
        string $name,
        ?string $prefix = null,
        ?string $op = null,
        ?string $value = null,
        ?string $modifier = null,
    ): string {
        $qname = $prefix ? "{$prefix}|{$name}" : $name;
        if (!$op) {
            return $addWhitespace ? "[ {$qname} ]" : "[{$qname}]";
        }
        if ($addWhitespace) {
            return "[ {$qname} {$op} {$value} {$modifier} ]";
        }

        return "[{$qname}{$op}{$value} {$modifier}]";
    }
}
