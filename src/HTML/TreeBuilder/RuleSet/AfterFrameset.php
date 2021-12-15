<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenType;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\InsertionModes;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-afterframeset
 */
final class AfterFrameset extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::CHARACTER) {
            $data = preg_replace('/[^ \n\t\f\r]+/S', '', $token->data, -1, $count);
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
        } else if ($type === TokenType::END_TAG && $token->name === 'html') {
            // Switch the insertion mode to "after after frameset".
            $tree->insertionMode = InsertionModes::AFTER_AFTER_FRAMESET;
        } else if ($type === TokenType::START_TAG && $token->name === 'noframes') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } else if ($type === TokenType::EOF) {
            // TODO: Stop parsing.
            return;
        } else {
            // TODO: Parse error. Ignore the token.
            return;
        }
    }
}
