<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder\RuleSet;

use Souplette\Html\Parser\TreeBuilder\InsertionModes;
use Souplette\Html\Parser\TreeBuilder\RuleSet;
use Souplette\Html\Parser\TreeBuilder\TreeBuilder;
use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-before-head-insertion-mode
 */
final class BeforeHead extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::CHARACTER) {
            $l = strspn($token->data, " \n\t\f");
            if ($l === \strlen($token->data)) {
                // Ignore the token.
                return;
            }
            if ($l > 0) {
                $token->data = substr($token->data, $l);
            }
        } else if ($type === TokenType::COMMENT) {
            $tree->insertComment($token);
            return;
        } else if ($type === TokenType::DOCTYPE) {
            // TODO: Parse error. Ignore the token.
            return;
        } else if ($type === TokenType::START_TAG && $token->name === 'html') {
            InBody::process($token, $tree);
            return;
        } else if ($type === TokenType::START_TAG && $token->name === 'head') {
            $head = $tree->insertElement($token);
            $tree->headElement = $head;
            $tree->insertionMode = InsertionModes::IN_HEAD;
            return;
        } else if ($type === TokenType::END_TAG) {
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
