<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder\RuleSet;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenTypes;
use Souplette\Html\Parser\TreeBuilder\InsertionModes;
use Souplette\Html\Parser\TreeBuilder\RuleSet;
use Souplette\Html\Parser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-after-head-insertion-mode
 */
final class AfterHead extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenTypes::CHARACTER && ctype_space($token->data)) {
            // Insert the character.
            $tree->insertCharacter($token);
        } elseif ($type === TokenTypes::COMMENT) {
            // Insert a comment.
            $tree->insertComment($token);
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'body') {
            // Insert an HTML element for the token.
            $tree->insertElement($token);
            // Set the frameset-ok flag to "not ok".
            $tree->framesetOK = false;
            // Switch the insertion mode to "in body".
            $tree->insertionMode = InsertionModes::IN_BODY;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'frameset') {
            // Insert an HTML element for the token.
            $tree->insertElement($token);
            // Switch the insertion mode to "in frameset".
            $tree->insertionMode = InsertionModes::IN_FRAMESET;
        } elseif ($type === TokenTypes::START_TAG && (
                $token->name === 'base'
                || $token->name === 'basefont'
                || $token->name === 'bgsound'
                || $token->name === 'link'
                || $token->name === 'meta'
                || $token->name === 'noframes'
                || $token->name === 'script'
                || $token->name === 'style'
                || $token->name === 'template'
                || $token->name === 'title'
        )) {
            // TODO: Parse error.
            // Push the node pointed to by the head element pointer onto the stack of open elements.
            $tree->openElements->push($tree->headElement);
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
            // Remove the node pointed to by the head element pointer from the stack of open elements.
            // (It might not be the current node at this point.)
            $tree->openElements->remove($tree->headElement);
            // The head element pointer cannot be null at this point.
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'template') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } elseif (
            $type === TokenTypes::END_TAG && (
                $token->name === 'body'
                || $token->name === 'html'
                || $token->name === 'br'
        )) {
            // Act as described in the "anything else" entry below.
            goto ANYTHING_ELSE;
        } elseif (
            $type === TokenTypes::START_TAG && $token->name === 'head'
            || $type === TokenTypes::END_TAG
        ) {
            //TODO: Parse Error. Ignore the token
            return;
        } else {
            ANYTHING_ELSE:
            // Insert an HTML element for a "body" start tag token with no attributes.
            $tree->insertElement(new Token\StartTag('body'));
            // Switch the insertion mode to "in body".
            $tree->insertionMode = InsertionModes::IN_BODY;
            // Reprocess the current token.
            $tree->processToken($token);
        }
    }
}
