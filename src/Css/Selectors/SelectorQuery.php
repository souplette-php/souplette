<?php declare(strict_types=1);

namespace Souplette\Css\Selectors;

use DOMElement;
use DOMParentNode;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\Selector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Node\Simple\IdSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;
use Souplette\Css\Syntax\TokenStream\TokenStream;
use Souplette\Dom\ElementIterator;
use Souplette\Dom\Internal\DomIdioms;
use Souplette\Dom\Legacy\Element;

final class SelectorQuery
{
    public static function byId(DOMParentNode $element, string $id): DOMElement|Element|null
    {
        return self::first($element, new IdSelector($id));
    }

    public static function byClassNames(DOMParentNode $element, string $classNames): array
    {
        $compound = null;
        foreach (DomIdioms::splitInputOnAsciiWhitespace($classNames) as $class) {
            $selector = new ClassSelector($class);
            $compound = $compound ? $compound->append($selector) : $selector;
        }

        return self::all($element, new ComplexSelector($compound));
    }

    /**
     * @link https://dom.spec.whatwg.org/#dom-element-matches
     */
    public static function matches(DOMElement $element, Selector|string $selector): bool
    {
        // 1. Let `s` be the result of parse a selector from selectors.
        // 2. If `s` is failure, then throw a "SyntaxError" DOMException.
        if (\is_string($selector)) {
            $selector = self::compile($selector);
        }
        // 3. If the result of match a selector against an element, using `s`, `this`, and :scope element `this`,
        // returns success, then return true; otherwise, return false.
        $ctx = QueryContext::of($element);
        return $selector->matches($ctx, $element);
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-element-closest
     */
    public static function closest(DOMElement $element, Selector|string $selector): DOMElement|Element|null
    {
        // 1. Let `s` be the result of parse a selector from selectors.
        // 2. If `s` is failure, then throw a "SyntaxError" DOMException.
        if (\is_string($selector)) {
            $selector = self::compile($selector);
        }
        // 3. Let `elements` be this’s inclusive ancestors that are elements, in reverse tree order.
        // 4. For each `element` in `elements`, if match a selector against an element,
        // using `s`, `element`, and :scope element `this`, returns success, return `element`.
        $ctx = QueryContext::of($element);
        foreach (ElementIterator::ancestors($element) as $candidate) {
            if ($selector->matches($ctx, $candidate)) {
                return $candidate;
            }
        }
        // 5. Return null.
        return null;
    }

    /**
     * The querySelector(selectors) method steps are to return the first result of running
     * {@link https://dom.spec.whatwg.org/#scope-match-a-selectors-string scope-match a selectors string}
     *  selectors against this, if the result is not an empty list; otherwise null.
     */
    public static function first(DOMParentNode $node, Selector|string $selector): DOMElement|Element|null
    {
        if (\is_string($selector)) {
            $selector = self::compile($selector);
        }
        $ctx = QueryContext::of($node);
        foreach (ElementIterator::descendants($node) as $candidate) {
            if ($selector->matches($ctx, $candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * The querySelectorAll(selectors) method steps are to return the static result of running
     * {@link https://dom.spec.whatwg.org/#scope-match-a-selectors-string scope-match a selectors string}
     * selectors against this.
     */
    public static function all(DOMParentNode $node, Selector|string $selector): array
    {
        // To scope-match a selectors string selectors against a `node`, run these steps:
        // Let `s` be the result of parse a selector selectors.
        // If `s` is failure, then throw a "SyntaxError" DOMException.
        if (\is_string($selector)) {
            $selector = self::compile($selector);
        }
        // Return the result of
        // {@link https://drafts.csswg.org/selectors-4/#match-a-selector-against-a-tree match a selector against a tree}
        // with `s` and node’s root using scoping root `node`.
        $ctx = QueryContext::of($node);
        $results = [];
        foreach (ElementIterator::descendants($node) as $candidate) {
            if ($selector->matches($ctx, $candidate)) {
                $results[] = $candidate;
            }
        }
        return $results;
    }

    private static function compile(string $selectorText): SelectorList
    {
        $tokens = new TokenStream(new Tokenizer($selectorText), 2);
        $parser = new SelectorParser($tokens);
        return $parser->parseSelectorList();
    }
}
