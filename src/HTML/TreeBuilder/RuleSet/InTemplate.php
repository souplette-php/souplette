<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\InsertionModes;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-intemplate
 */
final class InTemplate extends RuleSet
{
    private const SWITCH_TO_HEAD_START_TAGS = [
        'base' => true,
        'basefont' => true,
        'bgsound' => true,
        'link' => true,
        'meta' => true,
        'noframes' => true,
        'script' => true,
        'style' => true,
        'template' => true,
        'title' => true,
    ];

    private const SWITCH_TO_TABLE_START_TAGS = [
        'caption' => true,
        'colgroup' => true,
        'tbody' => true,
        'tfoot' => true,
        'thead' => true,
    ];

    public static function process(Token $token, TreeBuilder $tree): void
    {
        $type = $token::KIND;
        if (
            $type === TokenKind::Characters
            || $type === TokenKind::Comment
            || $type === TokenKind::Doctype
        ) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else if (
            ($type === TokenKind::StartTag && isset(self::SWITCH_TO_HEAD_START_TAGS[$token->name]))
            || ($type === TokenKind::EndTag && $token->name === 'template')
        ) {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } else if ($type === TokenKind::StartTag && isset(self::SWITCH_TO_TABLE_START_TAGS[$token->name])) {
            // Pop the current template insertion mode off the stack of template insertion modes.
            $tree->templateInsertionModes->pop();
            // Push "in table" onto the stack of template insertion modes so that it is the new current template insertion mode.
            $tree->templateInsertionModes->push(InsertionModes::IN_TABLE);
            // Switch the insertion mode to "in table", and reprocess the token.
            $tree->insertionMode = InsertionModes::IN_TABLE;
            $tree->processToken($token);
        } else if ($type === TokenKind::StartTag && $token->name === 'col') {
            // Pop the current template insertion mode off the stack of template insertion modes.
            $tree->templateInsertionModes->pop();
            // Push "in column group" onto the stack of template insertion modes so that it is the new current template insertion mode.
            $tree->templateInsertionModes->push(InsertionModes::IN_COLUMN_GROUP);
            // Switch the insertion mode to "in column group", and reprocess the token.
            $tree->insertionMode = InsertionModes::IN_COLUMN_GROUP;
            $tree->processToken($token);
        } else if ($type === TokenKind::StartTag && $token->name === 'tr') {
            // Pop the current template insertion mode off the stack of template insertion modes.
            $tree->templateInsertionModes->pop();
            // Push "in table body" onto the stack of template insertion modes so that it is the new current template insertion mode.
            $tree->templateInsertionModes->push(InsertionModes::IN_TABLE_BODY);
            // Switch the insertion mode to "in table body", and reprocess the token.
            $tree->insertionMode = InsertionModes::IN_TABLE_BODY;
            $tree->processToken($token);
        } else if ($type === TokenKind::StartTag && ($token->name === 'td' || $token->name === 'th')) {
            // Pop the current template insertion mode off the stack of template insertion modes.
            $tree->templateInsertionModes->pop();
            // Push "in row" onto the stack of template insertion modes so that it is the new current template insertion mode.
            $tree->templateInsertionModes->push(InsertionModes::IN_ROW);
            // Switch the insertion mode to "in row", and reprocess the token.
            $tree->insertionMode = InsertionModes::IN_ROW;
            $tree->processToken($token);
        } else if ($type === TokenKind::StartTag) {
            // Pop the current template insertion mode off the stack of template insertion modes.
            $tree->templateInsertionModes->pop();
            // Push "in body" onto the stack of template insertion modes so that it is the new current template insertion mode.
            $tree->templateInsertionModes->push(InsertionModes::IN_BODY);
            // Switch the insertion mode to "in body", and reprocess the token.
            $tree->insertionMode = InsertionModes::IN_BODY;
            $tree->processToken($token);
        } else if ($type === TokenKind::EndTag) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } else if ($type === TokenKind::EOF) {
            // If there is no template element on the stack of open elements, then stop parsing. (fragment case)
            if (!$tree->openElements->containsTag('template')) {
                // TODO: Stop parsing.
                return;
            }
            // Otherwise, this is a parse error.
            // TODO: Parse error.
            // Pop elements from the stack of open elements until a template element has been popped from the stack.
            $tree->openElements->popUntilTag('template');
            // Clear the list of active formatting elements up to the last marker.
            $tree->activeFormattingElements->clearUpToLastMarker();
            // Pop the current template insertion mode off the stack of template insertion modes.
            $tree->templateInsertionModes->pop();
            // Reset the insertion mode appropriately.
            $tree->resetInsertionModeAppropriately();
            // Reprocess the token.
            $tree->processToken($token);
        }
    }
}
