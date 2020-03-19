<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inselect
 */
final class InSelect extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::CHARACTER) {
            if ($token->data === "\0") {
                // TODO: Parse error.
                // Ignore the token.
                return;
            }
            // Insert the token's character.
            $tree->insertCharacter($token);
        } elseif ($type === TokenTypes::COMMENT) {
            // Insert a comment.
            $tree->insertComment($token);
        } elseif ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            $tree->processToken($token, InsertionModes::IN_BODY);
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'option') {
            // If the current node is an option element, pop that node from the stack of open elements.
            if ($tree->openElements->top()->localName === 'option') {
                $tree->openElements->pop();
            }
            // Insert an HTML element for the token.
            $tree->insertElement($token);
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'optgroup') {
            // If the current node is an option element, pop that node from the stack of open elements.
            if ($tree->openElements->top()->localName === 'option') {
                $tree->openElements->pop();
            }
            // If the current node is an optgroup element, pop that node from the stack of open elements.
            if ($tree->openElements->top()->localName === 'optgroup') {
                $tree->openElements->pop();
            }
            // Insert an HTML element for the token.
            $tree->insertElement($token);
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'optgroup') {
            // First, if the current node is an option element,
            // and the node immediately before it in the stack of open elements is an optgroup element,
            // then pop the current node from the stack of open elements.
            if ($tree->openElements->top()->localName === 'option') {
                $length = $tree->openElements->count();
                $previousNode = $tree->openElements[$length - 2] ?? null;
                if ($previousNode && $previousNode->localName === 'optgroup') {
                    $tree->openElements->pop();
                }
            }
            // If the current node is an optgroup element, then pop that node from the stack of open elements.
            // Otherwise, this is a parse error; ignore the token.
            if ($tree->openElements->top()->localName === 'optgroup') {
                $tree->openElements->pop();
            } else {
                // TODO: Parse error.
                return;
            }
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'option') {
            // If the current node is an option element, then pop that node from the stack of open elements.
            // Otherwise, this is a parse error; ignore the token.
            if ($tree->openElements->top()->localName === 'option') {
                $tree->openElements->pop();
            } else {
                // TODO: Parse error.
                return;
            }
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'select') {
            // If the stack of open elements does not have a select element in select scope,
            // this is a parse error; ignore the token. (fragment case)
            if (!$tree->openElements->hasTagInSelectScope('select')) {
                // TODO: Parse error.
                return;
            }
            // Otherwise:
            // Pop elements from the stack of open elements until a select element has been popped from the stack.
            $tree->openElements->popUntilTag('select');
            // Reset the insertion mode appropriately.
            $tree->resetInsertionModeAppropriately();
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'select') {
            // TODO: Parse error.
            // If the stack of open elements does not have a select element in select scope, ignore the token. (fragment case)
            if (!$tree->openElements->hasTagInSelectScope('select')) {
                return;
            }
            // Otherwise:
            // Pop elements from the stack of open elements until a select element has been popped from the stack.
            $tree->openElements->popUntilTag('select');
            // Reset the insertion mode appropriately.
            $tree->resetInsertionModeAppropriately();
        } elseif ($type === TokenTypes::START_TAG && (
            $token->name === 'input'
            || $token->name === 'keygen'
            || $token->name === 'textarea'
        )) {
            // TODO: Parse error.
            // If the stack of open elements does not have a select element in select scope, ignore the token. (fragment case)
            if (!$tree->openElements->hasTagInSelectScope('select')) {
                return;
            }
            // Otherwise:
            // Pop elements from the stack of open elements until a select element has been popped from the stack.
            $tree->openElements->popUntilTag('select');
            // Reset the insertion mode appropriately.
            $tree->resetInsertionModeAppropriately();
            // Reprocess the token.
            $tree->processToken($token);
        } elseif (
            ($type === TokenTypes::START_TAG && ($token->name === 'script' || $token->name === 'template'))
            || ($type === TokenTypes::END_TAG && $token->name === 'template')
        ) {
            // Process the token using the rules for the "in head" insertion mode.
            $tree->processToken($token, InsertionModes::IN_HEAD);
        } elseif ($type === TokenTypes::EOF) {
            // Process the token using the rules for the "in body" insertion mode.
            $tree->processToken($token, InsertionModes::IN_BODY);
        } else {
            // TODO: Parse error.
            // Ignore the token.
            return;
        }
    }
}
