<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-incaption
 */
final class InCaption extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::END_TAG && $token->name === 'caption') {
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
        } elseif (
            ($type === TokenTypes::END_TAG && $token->name === 'table')
            || ($type === TokenTypes::START_TAG && (
                $token->name === 'caption'
                || $token->name === 'col'
                || $token->name === 'colgroup'
                || $token->name === 'tbody'
                || $token->name === 'td'
                || $token->name === 'tfoot'
                || $token->name === 'th'
                || $token->name === 'thead'
                || $token->name === 'tr'
            ))
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
        } elseif ($type === TokenTypes::END_TAG && (
                $token->name === 'body'
                || $token->name === 'col'
                || $token->name === 'colgroup'
                || $token->name === 'html'
                || $token->name === 'tbody'
                || $token->name === 'td'
                || $token->name === 'tfoot'
                || $token->name === 'th'
                || $token->name === 'thead'
                || $token->name === 'tr'
        )) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } else {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        }
    }
}
