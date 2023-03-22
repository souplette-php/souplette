<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\InsertionModes;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-after-head-insertion-mode
 */
final class AfterHead extends RuleSet
{
    private const ERROR_START_TAGS = [
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

    public static function process(Token $token, TreeBuilder $tree): void
    {
        $type = $token::KIND;
        if ($type === TokenKind::Characters && ctype_space($token->data)) {
            // Insert the character.
            $tree->insertCharacter($token);
        } else if ($type === TokenKind::Comment) {
            // Insert a comment.
            $tree->insertComment($token);
        } else if ($type === TokenKind::StartTag && $token->name === 'body') {
            // Insert an HTML element for the token.
            $tree->insertElement($token);
            // Set the frameset-ok flag to "not ok".
            $tree->framesetOK = false;
            // Switch the insertion mode to "in body".
            $tree->insertionMode = InsertionModes::IN_BODY;
        } else if ($type === TokenKind::StartTag && $token->name === 'frameset') {
            // Insert an HTML element for the token.
            $tree->insertElement($token);
            // Switch the insertion mode to "in frameset".
            $tree->insertionMode = InsertionModes::IN_FRAMESET;
        } else if ($type === TokenKind::StartTag && isset(self::ERROR_START_TAGS[$token->name])) {
            // TODO: Parse error.
            // Push the node pointed to by the head element pointer onto the stack of open elements.
            $tree->openElements->push($tree->headElement);
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
            // Remove the node pointed to by the head element pointer from the stack of open elements.
            // (It might not be the current node at this point.)
            $tree->openElements->remove($tree->headElement);
            // The head element pointer cannot be null at this point.
        } else if ($type === TokenKind::EndTag && $token->name === 'template') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } else if (
            $type === TokenKind::EndTag && (
                $token->name === 'body'
                || $token->name === 'html'
                || $token->name === 'br'
        )) {
            // Act as described in the "anything else" entry below.
            goto ANYTHING_ELSE;
        } else if (
            $type === TokenKind::StartTag && $token->name === 'head'
            || $type === TokenKind::EndTag
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
