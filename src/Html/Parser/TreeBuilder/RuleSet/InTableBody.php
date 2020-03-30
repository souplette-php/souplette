<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\TreeBuilder\RuleSet;

use JoliPotage\Html\Parser\Tokenizer\Token;
use JoliPotage\Html\Parser\Tokenizer\TokenTypes;
use JoliPotage\Html\Parser\TreeBuilder\InsertionModes;
use JoliPotage\Html\Parser\TreeBuilder\RuleSet;
use JoliPotage\Html\Parser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-intbody
 */
final class InTableBody extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::START_TAG && $token->name === 'tr') {
            // Clear the stack back to a table body context.
            self::clearTheStackBackToATableBodyContext($tree);
            // Insert an HTML element for the token, then switch the insertion mode to "in row".
            $tree->insertElement($token);
            $tree->insertionMode = InsertionModes::IN_ROW;
        } elseif ($type === TokenTypes::START_TAG && ($token->name === 'th' || $token->name === 'td')) {
            // TODO: Parse error.
            // Clear the stack back to a table body context.
            self::clearTheStackBackToATableBodyContext($tree);
            // Insert an HTML element for a "tr" start tag token with no attributes,
            $tree->insertElement(new Token\StartTag('tr'));
            // then switch the insertion mode to "in row".
            $tree->insertionMode = InsertionModes::IN_ROW;
            // Reprocess the current token.
            $tree->processToken($token);
        } elseif ($type === TokenTypes::END_TAG && (
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
            // Otherwise:
            // Clear the stack back to a table body context.
            self::clearTheStackBackToATableBodyContext($tree);
            // Pop the current node from the stack of open elements. Switch the insertion mode to "in table".
            $tree->openElements->pop();
            $tree->insertionMode = InsertionModes::IN_TABLE;
        } elseif (
            ($type === TokenTypes::START_TAG && (
                $token->name === 'caption'
                || $token->name === 'col'
                || $token->name === 'colgroup'
                || $token->name === 'tbody'
                || $token->name === 'tfoot'
                || $token->name === 'thead'
            )) || (
                $type === TokenTypes::END_TAG && $token->name === 'table'
            )
        ) {
            // If the stack of open elements does not have a tbody, thead, or tfoot element in table scope,
            // this is a parse error; ignore the token.
            if (!$tree->openElements->hasTagsInScope(['tbody', 'thead', 'tfoot'])) {
                // TODO: Parse error.
                return;
            }
            // Otherwise:
            // Clear the stack back to a table body context.
            self::clearTheStackBackToATableBodyContext($tree);
            // Pop the current node from the stack of open elements. Switch the insertion mode to "in table".
            $tree->openElements->pop();
            $tree->insertionMode = InsertionModes::IN_TABLE;
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
            || $token->name === 'tr'
        )) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } else {
            // Process the token using the rules for the "in table" insertion mode.
            InTable::process($token, $tree);
        }
    }

    private static function clearTheStackBackToATableBodyContext(TreeBuilder $tree): void
    {
        // while the current node is not a tbody, tfoot, thead, template, or html element,
        // pop elements from the stack of open elements.
        $openElements = $tree->openElements;
        while (!$openElements->isEmpty()) {
            $currentTag = $openElements->top()->localName;
            if (
                $currentTag === 'tbody'
                || $currentTag === 'tfoot'
                || $currentTag === 'thead'
                || $currentTag === 'template'
                || $currentTag === 'html'
            ) {
                return;
            }
            $openElements->pop();
        }
    }
}
