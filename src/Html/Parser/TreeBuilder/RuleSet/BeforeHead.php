<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder\RuleSet;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenType;
use Souplette\Html\Parser\TreeBuilder\InsertionModes;
use Souplette\Html\Parser\TreeBuilder\RuleSet;
use Souplette\Html\Parser\TreeBuilder\TreeBuilder;

final class BeforeHead extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::CHARACTER) {
            if (ctype_space($token->data)) {
                // Ignore the token.
                return;
            }
            if ($l = strspn($token->data, " \n\t\f")) {
                $token->data = substr($token->data, $l);
            }
        } elseif ($type === TokenType::COMMENT) {
            $tree->insertComment($token);
            return;
        } elseif ($type === TokenType::DOCTYPE) {
            // TODO: Parse error. Ignore the token.
            return;
        } elseif ($type === TokenType::START_TAG && $token->name === 'html') {
            InBody::process($token, $tree);
            return;
        } elseif ($type === TokenType::START_TAG && $token->name === 'head') {
            $head = $tree->insertElement($token);
            $tree->headElement = $head;
            $tree->insertionMode = InsertionModes::IN_HEAD;
            return;
        } elseif ($type === TokenType::END_TAG) {
            if ($token->name === 'head' || $token->name === 'body' || $token->name === 'html' || $token->name === 'br') {
                // Act as described in the "anything else" entry below.
            } else {
                // TODO: Parse error. Ignore the token.
                return;
            }
        }
        // Insert an HTML element for a "head" start tag token with no attributes.
        $head = $tree->insertElement(new Token\StartTag('head'));
        // Set the head element pointer to the newly created head element.
        $tree->headElement = $head;
        // Switch the insertion mode to "in head".
        $tree->insertionMode = InsertionModes::IN_HEAD;
        // Reprocess the current token.
        $tree->processToken($token);
    }
}
