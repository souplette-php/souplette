<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-intr
 */
final class InRow extends RuleSet
{
    public function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::START_TAG && ($token->name === 'th' || $token->name === 'td')) {
            // Clear the stack back to a table body context.
            $this->clearTheStackBackToATableRowContext($tree);
            // Insert an HTML element for the token, then switch the insertion mode to "in cell".
            $tree->insertElement($token);
            $tree->setInsertionMode(InsertionModes::IN_CELL);
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'tr') {
            // If the stack of open elements does not have a tr element in table scope,
            // this is a parse error; ignore the token.
            if (!$tree->openElements->hasTagInScope('tr')) {
                // TODO: Parse error.
                return;
            }
            // Otherwise:
            // Clear the stack back to a table row context. (See below.)
            $this->clearTheStackBackToATableRowContext($tree);
            // Pop the current node (which will be a tr element) from the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to "in table body".
            $tree->setInsertionMode(InsertionModes::IN_TABLE_BODY);
        } elseif (
            ($type === TokenTypes::START_TAG && (
                $token->name === 'caption'
                || $token->name === 'col'
                || $token->name === 'colgroup'
                || $token->name === 'tbody'
                || $token->name === 'tfoot'
                || $token->name === 'thead'
                || $token->name === 'tr'
            )) || (
                $type === TokenTypes::END_TAG && $token->name === 'table'
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
            $this->clearTheStackBackToATableRowContext($tree);
            // Pop the current node (which will be a tr element) from the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to "in table body".
            $tree->setInsertionMode(InsertionModes::IN_TABLE_BODY);
            // Reprocess the token.
            $tree->processToken($token);
        }  elseif ($type === TokenTypes::END_TAG && (
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
            $this->clearTheStackBackToATableRowContext($tree);
            // Pop the current node (which will be a tr element) from the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to "in table body".
            $tree->setInsertionMode(InsertionModes::IN_TABLE_BODY);
            // Reprocess the token.
            $tree->processToken($token);
        } elseif ($type === TokenTypes::END_TAG && (
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
            $tree->processToken($token, InsertionModes::IN_TABLE);
        }
    }

    private function clearTheStackBackToATableRowContext(TreeBuilder $tree): void
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
