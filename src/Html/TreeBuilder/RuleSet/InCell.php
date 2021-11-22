<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder\RuleSet;

use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;
use Souplette\Html\TreeBuilder\InsertionModes;
use Souplette\Html\TreeBuilder\RuleSet;
use Souplette\Html\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-intd
 */
final class InCell extends RuleSet
{
    private const CLOSE_CELL_START_TAGS = [
        'caption' => true,
        'col' => true,
        'colgroup' => true,
        'tbody' => true,
        'td' => true,
        'tfoot' => true,
        'th' => true,
        'thead' => true,
        'tr' => true,
    ];
    private const CLOSE_CELL_END_TAGS = [
        'table' => true,
        'tbody' => true,
        'tfoot' => true,
        'thead' => true,
        'tr' => true,
    ];
    private const ERROR_END_TAGS = [
        'body' => true,
        'caption' => true,
        'col' => true,
        'colgroup' => true,
        'html' => true,
    ];

    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::END_TAG && ($token->name === 'td' || $token->name === 'th')) {
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
        } else if ($type === TokenType::START_TAG && isset(self::CLOSE_CELL_START_TAGS[$token->name])) {
            // If the stack of open elements does not have a td or th element in table scope,
            // then this is a parse error; ignore the token. (fragment case)
            if (!$tree->openElements->hasTagsInTableScope(['td', 'th'])) {
                // TODO: Parse error.
                return;
            }
            // Otherwise, close the cell (see below) and reprocess the token.
            self::closeTheCell($tree);
            $tree->processToken($token);
        } else if ($type === TokenType::END_TAG && isset(self::ERROR_END_TAGS[$token->name])) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } else if ($type === TokenType::END_TAG && isset(self::CLOSE_CELL_END_TAGS[$token->name])) {
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
