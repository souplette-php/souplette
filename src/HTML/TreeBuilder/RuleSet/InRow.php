<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\InsertionModes;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-intr
 */
final class InRow extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree): void
    {
        $type = $token::KIND;
        if ($type === TokenKind::StartTag && ($token->name === 'th' || $token->name === 'td')) {
            // Clear the stack back to a table body context.
            self::clearTheStackBackToATableRowContext($tree);
            // Insert an HTML element for the token, then switch the insertion mode to "in cell".
            $tree->insertElement($token);
            $tree->insertionMode = InsertionModes::IN_CELL;
            // Insert a marker at the end of the list of active formatting elements.
            $tree->activeFormattingElements->push(null);
        } else if ($type === TokenKind::EndTag && $token->name === 'tr') {
            // If the stack of open elements does not have a tr element in table scope,
            // this is a parse error; ignore the token.
            if (!$tree->openElements->hasTagInScope('tr')) {
                // TODO: Parse error.
                return;
            }
            // Otherwise:
            // Clear the stack back to a table row context. (See below.)
            self::clearTheStackBackToATableRowContext($tree);
            // Pop the current node (which will be a tr element) from the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to "in table body".
            $tree->insertionMode = InsertionModes::IN_TABLE_BODY;
        } else if (
            ($type === TokenKind::StartTag && (
                $token->name === 'caption'
                || $token->name === 'col'
                || $token->name === 'colgroup'
                || $token->name === 'tbody'
                || $token->name === 'tfoot'
                || $token->name === 'thead'
                || $token->name === 'tr'
            )) || (
                $type === TokenKind::EndTag && $token->name === 'table'
            )
        ) {
            // If the stack of open elements does not have a tr element in table scope,
            // this is a parse error; ignore the token.
            if (!$tree->openElements->hasTagInScope('tr')) {
                // TODO: Parse error.
                return;
            }
            // Otherwise:
            // Clear the stack back to a table row context.
            self::clearTheStackBackToATableRowContext($tree);
            // Pop the current node (which will be a tr element) from the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to "in table body".
            $tree->insertionMode = InsertionModes::IN_TABLE_BODY;
            // Reprocess the token.
            $tree->processToken($token);
        }  else if ($type === TokenKind::EndTag && (
            $token->name === 'tbody'
            || $token->name === 'tfoot'
            || $token->name === 'thead'
        )) {
            // If the stack of open elements does not have an element in table scope
            // that is an HTML element with the same tag name as the token, this is a parse error; ignore the token.
            if (!$tree->openElements->hasTagInTableScope($token->name)) {
                // TODO: Parse error.
                return;
            }
            // If the stack of open elements does not have a tr element in table scope, ignore the token.
            if (!$tree->openElements->hasTagInTableScope('tr')) {
                return;
            }
            // Otherwise:
            // Clear the stack back to a table row context. (See below.)
            self::clearTheStackBackToATableRowContext($tree);
            // Pop the current node (which will be a tr element) from the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to "in table body".
            $tree->insertionMode = InsertionModes::IN_TABLE_BODY;
            // Reprocess the token.
            $tree->processToken($token);
        } else if ($type === TokenKind::EndTag && (
            $token->name === 'body'
            || $token->name === 'caption'
            || $token->name === 'col'
            || $token->name === 'colgroup'
            || $token->name === 'html'
            || $token->name === 'td'
            || $token->name === 'th'
        )) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } else {
            // Process the token using the rules for the "in table" insertion mode.
            InTable::process($token, $tree);
        }
    }

    private static function clearTheStackBackToATableRowContext(TreeBuilder $tree): void
    {
        //  while the current node is not a tr, template, or html element, pop elements from the stack of open elements.
        $openElements = $tree->openElements;
        while (!$openElements->isEmpty()) {
            $currentTag = $openElements->top()->localName;
            if (
                $currentTag === 'tr'
                || $currentTag === 'template'
                || $currentTag === 'html'
            ) {
                return;
            }
            $openElements->pop();
        }
    }
}
