<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-after-head-insertion-mode
 */
final class AfterHead extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::CHARACTER && ctype_space($token->data)) {
            // Insert the character.
            $tree->insertCharacter($token);
            return;
        } elseif ($type === TokenTypes::COMMENT) {
            // Insert a comment.
            $tree->insertComment($token);
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'body') {
            // Insert an HTML element for the token.
            $tree->insertElement($token);
            // Set the frameset-ok flag to "not ok".
            $tree->framesetOK = false;
            // Switch the insertion mode to "in body".
            $tree->insertionMode = InsertionModes::IN_BODY;
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'frameset') {
            // Insert an HTML element for the token.
            $tree->insertElement($token);
            // Switch the insertion mode to "in frameset".
            $tree->insertionMode = InsertionModes::IN_FRAMESET;
            return;
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
            foreach ($tree->openElements as $i => $element) {
                if ($element === $tree->headElement) {
                    unset($tree->openElements[$i]);
                    break;
                }
            }
            // The head element pointer cannot be null at this point.
            return;
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
        } elseif (
            $type === TokenTypes::START_TAG && $token->name === 'head'
            || $type === TokenTypes::END_TAG
        ) {
            //TODO: Parse Error. Ignore the token
            return;
        }

        // Insert an HTML element for a "body" start tag token with no attributes.
        $tree->insertElement(new Token\StartTag('body'));
        // Switch the insertion mode to "in body".
        $tree->insertionMode = InsertionModes::IN_BODY;
        // Reprocess the current token.
        $tree->processToken($token);
    }
}
