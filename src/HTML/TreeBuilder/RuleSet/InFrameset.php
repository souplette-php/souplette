<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\InsertionModes;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inframeset
 */
final class InFrameset extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree): void
    {
        $type = $token::KIND;
        if ($type === TokenKind::Characters) {
            $data = preg_replace('/[^ \n\t\f]+/S', '', $token->data, -1, $count);
            if ($count > 0) {
                // TODO: Parse error. Ignore the character tokens.
                if (\strlen($data) === 0) return;
                $token->data = $data;
            }
            // Insert the character.
            $tree->insertCharacter($token);
        } else if ($type === TokenKind::Comment) {
            // Insert a comment.
            $tree->insertComment($token);
        } else if ($type === TokenKind::Doctype) {
            // TODO: Parse error. Ignore the token.
            return;
        } else if ($type === TokenKind::StartTag && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else if ($type === TokenKind::StartTag && $token->name === 'frameset') {
            // Insert an HTML element for the token.
            $tree->insertElement($token);
        } else if ($type === TokenKind::EndTag && $token->name === 'frameset') {
            // If the current node is the root html element, then this is a parse error; ignore the token. (fragment case)
            if ($tree->openElements->top() === $tree->document->documentElement) {
                // TODO: Parse error.
                return;
            }
            // Otherwise, pop the current node from the stack of open elements.
            $tree->openElements->pop();
            // If the parser was not created as part of the HTML fragment parsing algorithm (fragment case),
            // and the current node is no longer a frameset element,
            // then switch the insertion mode to "after frameset".
            if (!$tree->isBuildingFragment && !$tree->openElements->currentNodeHasType('frameset')) {
                $tree->insertionMode = InsertionModes::AFTER_FRAMESET;
            }
        } else if ($type === TokenKind::StartTag && $token->name === 'frame') {
            // Insert an HTML element for the token. Immediately pop the current node off the stack of open elements.
            $tree->insertElement($token);
            $tree->openElements->pop();
            // Acknowledge the token's self-closing flag, if it is set.
            $tree->acknowledgeSelfClosingFlag($token);
        } else if ($type === TokenKind::StartTag && $token->name === 'noframes') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } else if ($type === TokenKind::EOF) {
            // If the current node is not the root html element, then this is a parse error.
            if ($tree->openElements->top() === $tree->document->documentElement) {
                // TODO: Parse error.
                return;
            }
            // TODO: Stop parsing.
        } else {
            // TODO: Parse error. Ignore the token.
            return;
        }
    }
}
