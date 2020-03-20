<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionLocation;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-after-after-frameset-insertion-mode
 */
final class AfterAfterFrameset extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::COMMENT) {
            // Insert a comment as the last child of the Document object.
            $tree->insertComment($token, new InsertionLocation($tree->document));
        } elseif (
            $type === TokenTypes::DOCTYPE
            || ($type === TokenTypes::CHARACTER && ctype_space($token->data))
            || ($type === TokenTypes::START_TAG && $token->name === 'html')
        ) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } elseif ($type === TokenTypes::EOF) {
            // TODO: Stop parsing.
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'noframes') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } else {
            // TODO: Parse error. Ignore the token.
            return;
        }
    }
}
