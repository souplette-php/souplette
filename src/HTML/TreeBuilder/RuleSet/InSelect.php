<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inselect
 */
final class InSelect extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree): void
    {
        $type = $token::KIND;
        if ($type === TokenKind::Characters) {
            if ($token->data === "\0") {
                // TODO: Parse error.
                // Ignore the token.
                return;
            }
            // Insert the token's character.
            $tree->insertCharacter($token);
        } else if ($type === TokenKind::Comment) {
            // Insert a comment.
            $tree->insertComment($token);
        } else if ($type === TokenKind::Doctype) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } else if ($type === TokenKind::StartTag && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else if ($type === TokenKind::StartTag && $token->name === 'option') {
            // If the current node is an option element, pop that node from the stack of open elements.
            if ($tree->openElements->currentNodeHasType('option')) {
                $tree->openElements->pop();
            }
            // Insert an HTML element for the token.
            $tree->insertElement($token);
        } else if ($type === TokenKind::StartTag && $token->name === 'optgroup') {
            // If the current node is an option element, pop that node from the stack of open elements.
            if ($tree->openElements->currentNodeHasType('option')) {
                $tree->openElements->pop();
            }
            // If the current node is an optgroup element, pop that node from the stack of open elements.
            if ($tree->openElements->currentNodeHasType('optgroup')) {
                $tree->openElements->pop();
            }
            // Insert an HTML element for the token.
            $tree->insertElement($token);
        } else if ($type === TokenKind::EndTag && $token->name === 'optgroup') {
            // First, if the current node is an option element,
            // and the node immediately before it in the stack of open elements is an optgroup element,
            // then pop the current node from the stack of open elements.
            if ($tree->openElements->currentNodeHasType('option')) {
                $previousNode = $tree->openElements[1] ?? null;
                if ($previousNode && $previousNode->localName === 'optgroup') {
                    $tree->openElements->pop();
                }
            }
            // If the current node is an optgroup element, then pop that node from the stack of open elements.
            // Otherwise, this is a parse error; ignore the token.
            if ($tree->openElements->currentNodeHasType('optgroup')) {
                $tree->openElements->pop();
            } else {
                // TODO: Parse error.
                return;
            }
        } else if ($type === TokenKind::EndTag && $token->name === 'option') {
            // If the current node is an option element, then pop that node from the stack of open elements.
            // Otherwise, this is a parse error; ignore the token.
            if ($tree->openElements->currentNodeHasType('option')) {
                $tree->openElements->pop();
            } else {
                // TODO: Parse error.
                return;
            }
        } else if ($type === TokenKind::EndTag && $token->name === 'select') {
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
        } else if ($type === TokenKind::StartTag && $token->name === 'select') {
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
        } else if ($type === TokenKind::StartTag && (
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
        } else if (
            ($type === TokenKind::StartTag && ($token->name === 'script' || $token->name === 'template'))
            || ($type === TokenKind::EndTag && $token->name === 'template')
        ) {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } else if ($type === TokenKind::EOF) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else {
            // TODO: Parse error.
            // Ignore the token.
            return;
        }
    }
}
