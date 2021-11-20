<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder\RuleSet;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenType;
use Souplette\Html\Parser\TreeBuilder\InsertionModes;
use Souplette\Html\Parser\TreeBuilder\RuleSet;
use Souplette\Html\Parser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inframeset
 */
final class InFrameset extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::CHARACTER) {
            $data = preg_replace('/[^ \n\t\f]+/S', '', $token->data, -1, $count);
            if ($count > 0) {
                // TODO: Parse error. Ignore the character tokens.
                if (\strlen($data) === 0) return;
                $token->data = $data;
            }
            // Insert the character.
            $tree->insertCharacter($token);
        } else if ($type === TokenType::COMMENT) {
            // Insert a comment.
            $tree->insertComment($token);
        } else if ($type === TokenType::DOCTYPE) {
            // TODO: Parse error. Ignore the token.
            return;
        } else if ($type === TokenType::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else if ($type === TokenType::START_TAG && $token->name === 'frameset') {
            // Insert an HTML element for the token.
            $tree->insertElement($token);
        } else if ($type === TokenType::END_TAG && $token->name === 'frameset') {
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
            if (!$tree->isBuildingFragment && $tree->openElements->top()->localName !== 'frameset') {
                $tree->insertionMode = InsertionModes::AFTER_FRAMESET;
            }
        } else if ($type === TokenType::START_TAG && $token->name === 'frame') {
            // Insert an HTML element for the token. Immediately pop the current node off the stack of open elements.
            $tree->insertElement($token);
            $tree->openElements->pop();
            // Acknowledge the token's self-closing flag, if it is set.
            $tree->acknowledgeSelfClosingFlag($token);
        } else if ($type === TokenType::START_TAG && $token->name === 'noframes') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } else if ($type === TokenType::EOF) {
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
