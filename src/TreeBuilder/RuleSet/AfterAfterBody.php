<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionLocation;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-after-after-body-insertion-mode
 */
final class AfterAfterBody extends RuleSet
{
    public function process(Token $token, TreeBuilder $tree)
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
            $tree->processToken($token, InsertionModes::IN_BODY);
        } elseif ($type === TokenTypes::EOF) {
            // TODO: Stop parsing.
            return;
        } else {
            // TODO: Parse error.
            // Switch the insertion mode to "in body" and reprocess the token.
            $tree->setInsertionMode(InsertionModes::IN_BODY);
            $tree->processToken($token);
        }
    }
}
