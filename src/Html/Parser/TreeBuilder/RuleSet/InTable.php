<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder\RuleSet;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenType;
use Souplette\Html\Parser\TreeBuilder\InsertionModes;
use Souplette\Html\Parser\TreeBuilder\RuleSet;
use Souplette\Html\Parser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-intable
 */
final class InTable extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        $currentNode = $tree->openElements->top();
        if ($type === TokenType::EOF) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
            return;
        } else if ($type === TokenType::CHARACTER && (
            $currentNode->localName === 'table'
            || $currentNode->localName === 'tbody'
            || $currentNode->localName === 'tfoot'
            || $currentNode->localName === 'thead'
            || $currentNode->localName === 'tr'
        )) {
            // Let the pending table character tokens be an empty list of tokens.
            $tree->pendingTableCharacterTokens = [];
            // Let the original insertion mode be the current insertion mode.
            $tree->originalInsertionMode = $tree->insertionMode;
            // Switch the insertion mode to "in table text" and reprocess the token.
            $tree->insertionMode = InsertionModes::IN_TABLE_TEXT;
            $tree->processToken($token);
            return;
        } else if ($type === TokenType::COMMENT) {
            $tree->insertComment($token);
            return;
        } else if ($type === TokenType::DOCTYPE) {
            // TODO: Parse error.
            // Ignore the token
            return;
        } else if ($type === TokenType::START_TAG) {
            $tagName = $token->name;
            if ($tagName === 'caption') {
                // Clear the stack back to a table context. (See below.)
                self::clearTheStackBackToATableContext($tree);
                // Insert a marker at the end of the list of active formatting elements.
                $tree->activeFormattingElements->push(null);
                // Insert an HTML element for the token, then switch the insertion mode to "in caption".
                $tree->insertElement($token);
                $tree->insertionMode = InsertionModes::IN_CAPTION;
                return;
            } else if ($tagName === 'colgroup') {
                // Clear the stack back to a table context. (See below.)
                self::clearTheStackBackToATableContext($tree);
                // Insert an HTML element for the token, then switch the insertion mode to "in column group".
                $tree->insertElement($token);
                $tree->insertionMode = InsertionModes::IN_COLUMN_GROUP;
                return;
            } else if ($tagName === 'col') {
                // Clear the stack back to a table context. (See below.)
                self::clearTheStackBackToATableContext($tree);
                // Insert an HTML element for a "colgroup" start tag token with no attributes,
                $tree->insertElement(new Token\StartTag('colgroup'));
                // then switch the insertion mode to "in column group".
                $tree->insertionMode = InsertionModes::IN_COLUMN_GROUP;
                // Reprocess the current token.
                $tree->processToken($token);
                return;
            } else if (
                $tagName === 'tbody'
                || $tagName === 'tfoot'
                || $tagName === 'thead'
            ) {
                // Clear the stack back to a table context. (See below.)
                self::clearTheStackBackToATableContext($tree);
                // Insert an HTML element for the token, then switch the insertion mode to "in table body".
                $tree->insertElement($token);
                $tree->insertionMode = InsertionModes::IN_TABLE_BODY;
                return;
            } else if (
                $tagName === 'td'
                || $tagName === 'th'
                || $tagName === 'tr'
            ) {
                // Clear the stack back to a table context. (See below.)
                self::clearTheStackBackToATableContext($tree);
                // Insert an HTML element for a "tbody" start tag token with no attributes,
                $tree->insertElement(new Token\StartTag('tbody'));
                // then switch the insertion mode to "in table body".
                $tree->insertionMode = InsertionModes::IN_TABLE_BODY;
                // Reprocess the current token.
                $tree->processToken($token);
                return;
            } else if ($tagName === 'table') {
                // TODO: Parse error.
                // If the stack of open elements does not have a table element in table scope, ignore the token.
                if (!$tree->openElements->hasTagInScope('table')) {
                    return;
                }
                // Otherwise:
                // Pop elements from this stack until a table element has been popped from the stack.
                $tree->openElements->popUntilTag('table');
                // Reset the insertion mode appropriately.
                $tree->resetInsertionModeAppropriately();
                // Reprocess the token.
                $tree->processToken($token);
                return;
            } else if (
                $tagName === 'style'
                || $tagName === 'script'
                || $tagName === 'template'
            ) {
                // Process the token using the rules for the "in head" insertion mode.
                InHead::process($token, $tree);
                return;
            } else if ($tagName === 'input') {
                // If the token does not have an attribute with the name "type",
                // or if it does, but that attribute's value is not an ASCII case-insensitive match for the string "hidden",
                // then: act as described in the "anything else" entry below.
                if (
                    !isset($token->attributes['type'])
                    || strcasecmp($token->attributes['type'], 'hidden') !== 0
                ) {
                    goto ANYTHING_ELSE;
                }
                // Otherwise:
                // TODO: Parse error.
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                // Pop that input element off the stack of open elements.
                $tree->openElements->pop();
                // Acknowledge the token's self-closing flag, if it is set.
                $tree->acknowledgeSelfClosingFlag($token);
                return;
            } else if ($tagName === 'form') {
                // TODO: Parse error.
                // If there is a template element on the stack of open elements,
                // or if the form element pointer is not null, ignore the token.
                if ($tree->openElements->containsTag('template') || $tree->formElement !== null) {
                    return;
                }
                // Otherwise:
                // Insert an HTML element for the token, and set the form element pointer to point to the element created.
                $element = $tree->insertElement($token);
                $tree->formElement = $element;
                // Pop that form element off the stack of open elements.
                $tree->openElements->pop();
                return;
            }
        } else if ($type === TokenType::END_TAG) {
            $tagName = $token->name;
            if ($tagName === 'table') {
                // If the stack of open elements does not have a table element in table scope,
                if (!$tree->openElements->hasTagInTableScope('table')) {
                    // TODO: this is a parse error;
                    // ignore the token.
                    return;
                }
                // Otherwise:
                // Pop elements from this stack until a table element has been popped from the stack.
                $tree->openElements->popUntilTag('table');
                // Reset the insertion mode appropriately.
                $tree->resetInsertionModeAppropriately();
                return;
            } else if (
                $tagName === 'body'
                || $tagName === 'caption'
                || $tagName === 'col'
                || $tagName === 'colgroup'
                || $tagName === 'html'
                || $tagName === 'tbody'
                || $tagName === 'td'
                || $tagName === 'tfoot'
                || $tagName === 'th'
                || $tagName === 'thead'
                || $tagName === 'tr'
            ) {
                // TODO: Parse error.
                // Ignore the token.
                return;
            } else if ($tagName === 'template') {
                // Process the token using the rules for the "in head" insertion mode.
                InHead::process($token, $tree);
                return;
            }
        }
        ANYTHING_ELSE:
        // TODO: Parse error.
        // Enable foster parenting,
        $tree->fosterParenting = true;
        // process the token using the rules for the "in body" insertion mode,
        InBody::process($token, $tree);
        // and then disable foster parenting.
        $tree->fosterParenting = false;
    }

    public static function processWithFosterParenting(Token $token, TreeBuilder $tree)
    {
        // TODO: Parse error.
        // Enable foster parenting,
        $tree->fosterParenting = true;
        // process the token using the rules for the "in body" insertion mode,
        InBody::process($token, $tree);
        // and then disable foster parenting.
        $tree->fosterParenting = false;
    }

    private static function clearTheStackBackToATableContext(TreeBuilder $tree): void
    {
        // @see https://html.spec.whatwg.org/multipage/parsing.html#clear-the-stack-back-to-a-table-context
        // while the current node is not a table, template, or html element, pop elements from the stack of open elements.
        $openElements = $tree->openElements;
        while (!$openElements->isEmpty()) {
            $currentTag = $openElements->top()->localName;
            if ($currentTag === 'table' || $currentTag === 'template' || $currentTag === 'html') {
                return;
            }
            $openElements->pop();
        }
    }
}
