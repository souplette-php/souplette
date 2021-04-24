<?php declare(strict_types=1);

namespace Souplette\Css\Selectors;

use DOMElement;
use DOMParentNode;
use Souplette\Css\Selectors\Query\Compiler;
use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;
use Souplette\Css\Syntax\TokenStream\TokenStream;
use Souplette\Html\Dom\ElementIterator;
use Souplette\Html\Dom\Node\HtmlElement;

final class SelectorQuery
{
    public static function matches(DOMElement $element, string $selectorText): bool
    {
        $eval = self::compile($selectorText);
        $ctx = new QueryContext($element);
        return $eval->matches($ctx, $element);
    }

    public static function queryFirst(DOMParentNode $element, string $selectorText): DOMElement|HtmlElement|null
    {
        $eval = self::compile($selectorText);
        $ctx = new QueryContext($element);
        foreach (ElementIterator::descendants($element) as $candidate) {
            if ($eval->matches($ctx, $candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    public static function queryAll(DOMParentNode $element, string $selectorText): array
    {
        $eval = self::compile($selectorText);
        $ctx = new QueryContext($element);
        $results = [];
        foreach (ElementIterator::descendants($element) as $candidate) {
            if ($eval->matches($ctx, $candidate)) {
                $results[] = $candidate;
            }
        }
        return $results;
    }

    public static function closest(DOMElement $element, string $selectorText): DOMElement|HtmlElement|null
    {
        $eval = self::compile($selectorText);
        $ctx = new QueryContext($element);
        foreach (ElementIterator::ancestors($element) as $candidate) {
            if ($eval->matches($ctx, $candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    private static function compile(string $selectorText): EvaluatorInterface
    {
        $tokens = new TokenStream(new Tokenizer($selectorText), 2);
        $parser = new SelectorParser($tokens);
        $selector = $parser->parseSelectorList();
        return (new Compiler())->compile($selector);
    }
}
