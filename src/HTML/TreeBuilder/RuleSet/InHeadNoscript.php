<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\InsertionModes;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inheadnoscript
 */
final class InHeadNoscript extends RuleSet
{
    private const SWITCH_TO_IN_HEAD_START_TAGS = [
        'basefont' => true,
        'bgsound' => true,
        'link' => true,
        'meta' => true,
        'noframes' => true,
        'style' => true,
    ];

    public static function process(Token $token, TreeBuilder $tree): void
    {
        $type = $token::KIND;
        if ($type === TokenKind::Doctype) {
            // TODO: Parse error. Ignore the token.
            return;
        } else if ($type === TokenKind::StartTag && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
            return;
        } else if ($type === TokenKind::EndTag && $token->name === 'noscript') {
            // Pop the current node (which will be a noscript element) from the stack of open elements;
            // the new current node will be a head element.
            $tree->openElements->pop();
            // Switch the insertion mode to "in head".
            $tree->insertionMode = InsertionModes::IN_HEAD;
            return;
        } else if (
            ($type === TokenKind::Characters && ctype_space($token->data))
            || $type === TokenKind::Comment
            || ($type === TokenKind::StartTag && isset(self::SWITCH_TO_IN_HEAD_START_TAGS[$token->name]))
        ) {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
            return;
        } else if ($type === TokenKind::EndTag && $token->name === 'br') {
            // Act as described in the "anything else" entry below.
            goto ANYTHING_ELSE;
        } else if (
            ($type === TokenKind::StartTag && ($token->name === 'head' || $token->name === 'noscript'))
            || $type === TokenKind::EndTag
        ) {
            // TODO: Parse error. Ignore the token.
            return;
        }
        ANYTHING_ELSE:
        // TODO: Parse error.
        // Pop the current node (which will be a noscript element) from the stack of open elements;
        // the new current node will be a head element.
        $tree->openElements->pop();
        // Switch the insertion mode to "in head".
        $tree->insertionMode = InsertionModes::IN_HEAD;
        // Reprocess the token.
        $tree->processToken($token);
    }
}
