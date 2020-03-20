<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionLocation;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-afterbody
 */
final class AfterBody extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::CHARACTER && ctype_space($token->data)) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } elseif ($type === TokenTypes::COMMENT) {
            // Insert a comment as the last child of the first element in the stack of open elements (the html element).
            $tree->insertComment($token, new InsertionLocation($tree->openElements->bottom()));
        } elseif ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'html') {
            // If the parser was created as part of the HTML fragment parsing algorithm,
            // this is a parse error; ignore the token. (fragment case)
            if ($tree->isBuildingFragment) {
                // TODO: Parse error.
                return;
            }
            // Otherwise, switch the insertion mode to "after after body".
            $tree->insertionMode = InsertionModes::AFTER_AFTER_BODY;
        } elseif ($type === TokenTypes::EOF) {
            // TODO: stop parsing.
            return;
        } else {
            // TODO: Parse error.
            // Switch the insertion mode to "in body" and reprocess the token.
            $tree->insertionMode = InsertionModes::IN_BODY;
            $tree->processToken($token);
        }
    }
}
