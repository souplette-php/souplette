<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Namespaces;
use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\TreeBuilder\InsertionLocation;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-before-html-insertion-mode
 */
final class BeforeHtml extends RuleSet
{
    public function process(Token $token, TreeBuilder $tree)
    {
        if ($token instanceof Token\Doctype) {
            // TODO: Parse error. Ignore the token.
            return;
        } elseif ($token instanceof Token\Comment) {
            $tree->insertComment($token, new InsertionLocation($tree->document));
            return;
        } elseif ($token instanceof Token\Character && ctype_space($token->data)) {
            // Ignore the token.
            return;
        } elseif ($token instanceof Token\StartTag && $token->name === 'html') {
            $tree->createElement($token, Namespaces::HTML, $tree->document);
            $tree->setInsertionMode(InsertionModes::BEFORE_HEAD);
            return;
        } elseif ($token instanceof Token\EndTag) {
            if ($token->name === 'head' || $token->name === 'body' || $token->name === 'html' || $token->name === 'br') {
                // Act as described in the "anything else" entry below.
            } else {
                // TODO: Parse error. Ignore the token.
                return;
            }
        }
        $html = $tree->createElement($token, Namespaces::HTML, $tree->document);
        $tree->document->appendChild($html);
        $tree->openElements->push($html);
        $tree->setInsertionMode(InsertionModes::BEFORE_HEAD);
    }
}
