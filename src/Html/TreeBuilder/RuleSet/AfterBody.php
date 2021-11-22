<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder\RuleSet;

use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;
use Souplette\Html\TreeBuilder\InsertionLocation;
use Souplette\Html\TreeBuilder\InsertionModes;
use Souplette\Html\TreeBuilder\RuleSet;
use Souplette\Html\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-afterbody
 */
final class AfterBody extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::CHARACTER && ctype_space($token->data)) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else if ($type === TokenType::COMMENT) {
            // Insert a comment as the last child of the first element in the stack of open elements (the html element).
            $tree->insertComment($token, new InsertionLocation($tree->openElements->bottom()));
        } else if ($type === TokenType::DOCTYPE) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } else if ($type === TokenType::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else if ($type === TokenType::END_TAG && $token->name === 'html') {
            // If the parser was created as part of the HTML fragment parsing algorithm,
            // this is a parse error; ignore the token. (fragment case)
            if ($tree->isBuildingFragment) {
                // TODO: Parse error.
                return;
            }
            // Otherwise, switch the insertion mode to "after after body".
            $tree->insertionMode = InsertionModes::AFTER_AFTER_BODY;
        } else if ($type === TokenType::EOF) {
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
