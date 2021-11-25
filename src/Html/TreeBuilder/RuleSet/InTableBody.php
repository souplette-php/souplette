<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder\RuleSet;

use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;
use Souplette\Html\TreeBuilder;
use Souplette\Html\TreeBuilder\InsertionModes;
use Souplette\Html\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-intbody
 */
final class InTableBody extends RuleSet
{
    private const SWITCH_TO_TABLE_START_TAGS = [
        'caption' => true,
        'col' => true,
        'colgroup' => true,
        'tbody' => true,
        'tfoot' => true,
        'thead' => true,
    ];
    private const SWITCH_TO_TABLE_END_TAGS = [
        'tbody' => true,
        'tfoot' => true,
        'thead' => true,
    ];
    private const PARSE_ERROR_END_TAGS = [
        'body' => true,
        'caption' => true,
        'col' => true,
        'colgroup' => true,
        'html' => true,
        'td' => true,
        'th' => true,
        'tr' => true,
    ];

    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::START_TAG && $token->name === 'tr') {
            // Clear the stack back to a table body context.
            self::clearTheStackBackToATableBodyContext($tree);
            // Insert an HTML element for the token, then switch the insertion mode to "in row".
            $tree->insertElement($token);
            $tree->insertionMode = InsertionModes::IN_ROW;
        } else if ($type === TokenType::START_TAG && ($token->name === 'th' || $token->name === 'td')) {
            // TODO: Parse error.
            // Clear the stack back to a table body context.
            self::clearTheStackBackToATableBodyContext($tree);
            // Insert an HTML element for a "tr" start tag token with no attributes,
            $tree->insertElement(new Token\StartTag('tr'));
            // then switch the insertion mode to "in row".
            $tree->insertionMode = InsertionModes::IN_ROW;
            // Reprocess the current token.
            $tree->processToken($token);
        } else if ($type === TokenType::END_TAG && isset(self::SWITCH_TO_TABLE_END_TAGS[$token->name])) {
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
        } else if (
            ($type === TokenType::START_TAG && isset(self::SWITCH_TO_TABLE_START_TAGS[$token->name]))
            || ($type === TokenType::END_TAG && $token->name === 'table')
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
        } else if ($type === TokenType::END_TAG && isset(self::PARSE_ERROR_END_TAGS[$token->name])) {
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