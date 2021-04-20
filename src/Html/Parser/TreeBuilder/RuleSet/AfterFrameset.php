<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder\RuleSet;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenTypes;
use Souplette\Html\Parser\TreeBuilder\InsertionModes;
use Souplette\Html\Parser\TreeBuilder\RuleSet;
use Souplette\Html\Parser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-afterframeset
 */
final class AfterFrameset extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenTypes::CHARACTER) {
            $data = preg_replace('/[^ \n\t\f]+/S', '', $token->data, -1, $count);
            if ($count > 0) {
                // TODO: Parse error. Ignore the character tokens.
                if (strlen($data) === 0) return;
                $token->data = $data;
            }
            // Insert the character.
            $tree->insertCharacter($token);
        } elseif ($type === TokenTypes::COMMENT) {
            // Insert a comment.
            $tree->insertComment($token);
        } elseif ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error. Ignore the token.
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'html') {
            // Switch the insertion mode to "after after frameset".
            $tree->insertionMode = InsertionModes::AFTER_AFTER_FRAMESET;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'noframes') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } elseif ($type === TokenTypes::EOF) {
            // TODO: Stop parsing.
            return;
        } else {
            // TODO: Parse error. Ignore the token.
            return;
        }
    }
}
