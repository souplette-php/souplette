<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder\RuleSet;

use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;
use Souplette\Html\TreeBuilder;
use Souplette\Html\TreeBuilder\InsertionModes;
use Souplette\Html\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-incaption
 */
final class InCaption extends RuleSet
{
    private const SWITCH_TO_TABLE_START_TAGS = [
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
    private const ERROR_END_TAGS = [
        'body' => true,
        'col' => true,
        'colgroup' => true,
        'html' => true,
        'tbody' => true,
        'td' => true,
        'tfoot' => true,
        'th' => true,
        'thead' => true,
        'tr' => true,
    ];

    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::END_TAG && $token->name === 'caption') {
            // If the stack of open elements does not have a caption element in table scope,
            // this is a parse error; ignore the token. (fragment case)
            if (!$tree->openElements->hasTagInTableScope('caption')) {
                // TODO: Parse error.
                return;
            }
            // Otherwise:
            // Generate implied end tags.
            $tree->generateImpliedEndTags();
            // Now, if the current node is not a caption element, then this is a parse error.
            if ($tree->openElements->top()->localName !== 'caption') {
                // TODO: Parse error
            }
            // Pop elements from this stack until a caption element has been popped from the stack.
            $tree->openElements->popUntilTag('caption');
            // Clear the list of active formatting elements up to the last marker.
            $tree->activeFormattingElements->clearUpToLastMarker();
            // Switch the insertion mode to "in table".
            $tree->insertionMode = InsertionModes::IN_TABLE;
        } else if (
            ($type === TokenType::END_TAG && $token->name === 'table')
            || ($type === TokenType::START_TAG && isset(self::SWITCH_TO_TABLE_START_TAGS[$token->name]))
        ) {
            // If the stack of open elements does not have a caption element in table scope,
            // this is a parse error; ignore the token. (fragment case)
            if (!$tree->openElements->hasTagInTableScope('caption')) {
                // TODO: Parse error.
                return;
            }
            // Otherwise:
            // Generate implied end tags.
            $tree->generateImpliedEndTags();
            // Now, if the current node is not a caption element, then this is a parse error.
            if ($tree->openElements->top()->localName !== 'caption') {
                // TODO: Parse error
            }
            // Pop elements from this stack until a caption element has been popped from the stack.
            $tree->openElements->popUntilTag('caption');
            // Clear the list of active formatting elements up to the last marker.
            $tree->activeFormattingElements->clearUpToLastMarker();
            // Switch the insertion mode to "in table".
            $tree->insertionMode = InsertionModes::IN_TABLE;
            // Reprocess the token.
            $tree->processToken($token);
        } else if ($type === TokenType::END_TAG && isset(self::ERROR_END_TAGS[$token->name])) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } else {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        }
    }
}
