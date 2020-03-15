<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inselectintable
 */
final class InSelectInTable extends RuleSet
{
    public function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::START_TAG && (
            $token->name === 'caption'
            || $token->name === 'table'
            || $token->name === 'tbody'
            || $token->name === 'tfoot'
            || $token->name === 'thead'
            || $token->name === 'tr'
            || $token->name === 'td'
            || $token->name === 'th'
        )) {
            // TODO: Parse error.
            // Pop elements from the stack of open elements until a select element has been popped from the stack.
            $tree->openElements->popUntilTag('select');
            // Reset the insertion mode appropriately.
            $tree->resetInsertionModeAppropriately();
            // Reprocess the token.
            $tree->processToken($token);
        } elseif ($type === TokenTypes::END_TAG && (
            $token->name === 'caption'
            || $token->name === 'table'
            || $token->name === 'tbody'
            || $token->name === 'tfoot'
            || $token->name === 'thead'
            || $token->name === 'tr'
            || $token->name === 'td'
            || $token->name === 'th'
        )) {
            // TODO: Parse error.
            // If the stack of open elements does not have an element in table scope
            // that is an HTML element with the same tag name as that of the token, then ignore the token.
            if (!$tree->openElements->hasTagInTableScope($token->name)) {
                return;
            }
            // Otherwise:
            // Pop elements from the stack of open elements until a select element has been popped from the stack.
            $tree->openElements->popUntilTag('select');
            // Reset the insertion mode appropriately.
            $tree->resetInsertionModeAppropriately();
            // Reprocess the token.
            $tree->processToken($token);
        } else {
            // Process the token using the rules for the "in select" insertion mode.
            $tree->processToken($token, InsertionModes::IN_SELECT);
        }
    }
}
