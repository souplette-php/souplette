<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder\RuleSet;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenType;
use Souplette\Html\Parser\TreeBuilder\RuleSet;
use Souplette\Html\Parser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inselect
 */
final class InSelect extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::CHARACTER) {
            if ($token->data === "\0") {
                // TODO: Parse error.
                // Ignore the token.
                return;
            }
            // Insert the token's character.
            $tree->insertCharacter($token);
        } elseif ($type === TokenType::COMMENT) {
            // Insert a comment.
            $tree->insertComment($token);
        } elseif ($type === TokenType::DOCTYPE) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } elseif ($type === TokenType::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } elseif ($type === TokenType::START_TAG && $token->name === 'option') {
            // If the current node is an option element, pop that node from the stack of open elements.
            if ($tree->openElements->top()->localName === 'option') {
                $tree->openElements->pop();
            }
            // Insert an HTML element for the token.
            $tree->insertElement($token);
        } elseif ($type === TokenType::START_TAG && $token->name === 'optgroup') {
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
        } elseif ($type === TokenType::END_TAG && $token->name === 'optgroup') {
            // First, if the current node is an option element,
            // and the node immediately before it in the stack of open elements is an optgroup element,
            // then pop the current node from the stack of open elements.
            if ($tree->openElements->top()->localName === 'option') {
                $previousNode = $tree->openElements[1] ?? null;
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
        } elseif ($type === TokenType::END_TAG && $token->name === 'option') {
            // If the current node is an option element, then pop that node from the stack of open elements.
            // Otherwise, this is a parse error; ignore the token.
            if ($tree->openElements->top()->localName === 'option') {
                $tree->openElements->pop();
            } else {
                // TODO: Parse error.
                return;
            }
        } elseif ($type === TokenType::END_TAG && $token->name === 'select') {
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
        } elseif ($type === TokenType::START_TAG && $token->name === 'select') {
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
        } elseif ($type === TokenType::START_TAG && (
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
            ($type === TokenType::START_TAG && ($token->name === 'script' || $token->name === 'template'))
            || ($type === TokenType::END_TAG && $token->name === 'template')
        ) {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } elseif ($type === TokenType::EOF) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else {
            // TODO: Parse error.
            // Ignore the token.
            return;
        }
    }
}
