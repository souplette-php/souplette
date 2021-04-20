<?php declare(strict_types=1);

namespace Souplette\Css\Selectors;

use DOMElement;
use DOMParentNode;
use Souplette\Css\Selectors\Query\Compiler;
use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;
use Souplette\Css\Syntax\TokenStream\TokenStream;
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
        foreach (self::descendantOrSelf($element) as $candidate) {
            if ($candidate === $element) continue;
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
        foreach (self::descendantOrSelf($element) as $candidate) {
            if ($candidate === $element) continue;
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
        foreach (self::ancestors($element) as $candidate) {
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

    /**
     * @return \Generator & iterable<DOMElement>
     */
    private static function descendantOrSelf(DOMParentNode $root)
    {
        $node = $root;
        while ($node) {
            yield $node;
            if ($node->firstElementChild) {
                $node = $node->firstElementChild;
                continue;
            }
            while ($node) {
                if ($node === $root) {
                    break 2;
                }
                if ($node->nextElementSibling) {
                    $node = $node->nextElementSibling;
                    continue 2;
                }
                $node = $node->parentNode;
            }
        }
    }

    private static function ancestors(DOMParentNode $root)
    {
        $node = $root;
        while ($node = $node->parentNode) {
            yield $node;
        }
    }
}
