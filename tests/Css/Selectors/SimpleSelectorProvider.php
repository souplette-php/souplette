<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors;

use Souplette\Css\Selectors\Node\AttributeSelector;
use Souplette\Css\Selectors\Node\TypeSelector;
use Souplette\Css\Selectors\Node\UniversalSelector;
use Souplette\Tests\Utils;

final class SimpleSelectorProvider
{
    public static function typeSelectors(): \Generator
    {
        yield 'namespaced element' => [
            'foo|bar',
            new TypeSelector('bar', 'foo')
        ];
        yield 'element in any namespace' => [
            '*|foo',
            new TypeSelector('foo', '*')
        ];
        yield 'element with no namespace' => [
            '|bar',
            new TypeSelector('bar', null)
        ];
        yield 'namespaced universal' => [
            'foo|*',
            new UniversalSelector('foo')
        ];
        yield 'universal in any namespace' => [
            '*|*',
            new UniversalSelector('*')
        ];
        yield 'universal with no namespace' => [
            '|*',
            new UniversalSelector(null)
        ];
        yield 'element without explicit namespace' => [
            'foo',
            new TypeSelector('foo', '*')
        ];
    }

    public static function attributeSelectors(): \Generator
    {
        $names = ['foo'];
        $namespaces = [null, 'ns', '*'];
        $ops = ['=', '^=', '$=', '|=', '*='];
        $values = ['bar', '"bar"'];
        $modifiers = [null, 'i', 's'];

        foreach (Utils::cartesianProduct([$names, $namespaces]) as [$name, $ns]) {
            $selector = new AttributeSelector($name, $ns);
            $input = self::attributeToString(false, $name, $ns);
            yield $input => [$input, $selector];
            $input = self::attributeToString(true, $name, $ns);
            yield $input => [$input, $selector];
        }

        $all = [
            $names,
            $namespaces,
            $ops,
            $values,
            $modifiers,
        ];
        foreach (Utils::cartesianProduct($all) as [$name, $ns, $op, $v, $mod]) {
            $value = preg_replace('/^["\'](.*)["\']$/', '$1', $v);
            $selector = new AttributeSelector($name, $ns, $op, $value, $mod);
            $input = self::attributeToString(false, $name, $ns, $op, $v, $mod);
            yield $input => [$input, $selector];
            $input = self::attributeToString(true, $name, $ns, $op, $v, $mod);
            yield $input => [$input, $selector];
        }

        yield 'no namespace selector' => [
            '[ |foo ]',
            new AttributeSelector('foo', null),
        ];
    }

    private static function attributeToString(
        bool $addWhitespace,
        string $name,
        ?string $ns = null,
        ?string $op = null,
        ?string $value = null,
        ?string $modifier = null,
    ): string {
        $qname = $ns ? "{$ns}|{$name}" : $name;
        if (!$op) {
            return $addWhitespace ? "[ {$qname} ]" : "[{$qname}]";
        }
        if ($addWhitespace) {
            return "[ {$qname} {$op} {$value} {$modifier} ]";
        }

        return "[{$qname}{$op}{$value} {$modifier}]";
    }
}
