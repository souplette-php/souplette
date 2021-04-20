<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder\RuleSet;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenTypes;
use Souplette\Html\Parser\TreeBuilder\InsertionModes;
use Souplette\Html\Parser\TreeBuilder\RuleSet;
use Souplette\Html\Parser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-intd
 */
final class InCell extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenTypes::END_TAG && ($token->name === 'td' || $token->name === 'th')) {
            // If the stack of open elements does not have an element in table scope
            // that is an HTML element with the same tag name as that of the token,
            // then this is a parse error; ignore the token.
            if (!$tree->openElements->hasTagInTableScope($token->name)) {
                // TODO: Parse error.
                return;
            }
            // Otherwise:
            // Generate implied end tags.
            $tree->generateImpliedEndTags();
            // Now, if the current node is not an HTML element with the same tag name as the token, then this is a parse error.
            if ($tree->openElements->top()->localName !== $token->name) {
                // TODO: Parse error.
            }
            // Pop elements from the stack of open elements stack until an HTML element with the same tag name as the token has been popped from the stack.
            $tree->openElements->popUntilTag($token->name);
            // Clear the list of active formatting elements up to the last marker.
            $tree->activeFormattingElements->clearUpToLastMarker();
            // Switch the insertion mode to "in row".
            $tree->insertionMode = InsertionModes::IN_ROW;
        } elseif ($type === TokenTypes::START_TAG && (
            $token->name === 'caption'
            || $token->name === 'col'
            || $token->name === 'colgroup'
            || $token->name === 'tbody'
            || $token->name === 'td'
            || $token->name === 'tfoot'
            || $token->name === 'th'
            || $token->name === 'thead'
            || $token->name === 'tr'
        )) {
            // If the stack of open elements does not have a td or th element in table scope,
            // then this is a parse error; ignore the token. (fragment case)
            if (!$tree->openElements->hasTagsInTableScope(['td', 'th'])) {
                // TODO: Parse error.
                return;
            }
            // Otherwise, close the cell (see below) and reprocess the token.
            self::closeTheCell($tree);
            $tree->processToken($token);
        } elseif ($type === TokenTypes::END_TAG && (
            $token->name === 'body'
            || $token->name === 'caption'
            || $token->name === 'col'
            || $token->name === 'colgroup'
            || $token->name === 'html'
        )) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } elseif ($type === TokenTypes::END_TAG && (
            $token->name === 'table'
            || $token->name === 'tbody'
            || $token->name === 'tfoot'
            || $token->name === 'thead'
            || $token->name === 'tr'
        )) {
            // If the stack of open elements does not have an element in table scope
            // that is an HTML element with the same tag name as that of the token,
            // then this is a parse error; ignore the token.
            if (!$tree->openElements->hasTagInTableScope($token->name)) {
                // TODO: Parse error.
                // Ignore the token.
                return;
            }
            // Otherwise, close the cell and reprocess the token.
            self::closeTheCell($tree);
            $tree->processToken($token);
        } else {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        }
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#close-the-cell
     * @param TreeBuilder $tree
     */
    private static function closeTheCell(TreeBuilder $tree): void
    {
        // Generate implied end tags.
        $tree->generateImpliedEndTags();
        // If the current node is not now a td element or a th element, then this is a parse error.
        $currentTag = $tree->openElements->top()->localName;
        if ($currentTag !== 'td' && $currentTag !== 'th') {
            // TODO: Parse error.
        }
        // Pop elements from the stack of open elements stack until a td element or a th element has been popped from the stack.
        $tree->openElements->popUntilOneOf(['td', 'th']);
        // Clear the list of active formatting elements up to the last marker.
        $tree->activeFormattingElements->clearUpToLastMarker();
        // Switch the insertion mode to "in row".
        $tree->insertionMode = InsertionModes::IN_ROW;
    }
}
