<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\InsertionModes;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-before-head-insertion-mode
 */
final class BeforeHead extends RuleSet
{
    private const HEAD_INSERTION_END_TAGS = [
        'head' => true,
        'body' => true,
        'html' => true,
        'br' => true,
    ];

    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::KIND;
        if ($type === TokenKind::Characters) {
            if (!$token->removeLeadingWhitespace()) {
                // Ignore the token.
                return;
            }
        }
        if ($type === TokenKind::Comment) {
            $tree->insertComment($token);
            return;
        }
        if ($type === TokenKind::Doctype) {
            // TODO: Parse error. Ignore the token.
            return;
        }
        if ($type === TokenKind::StartTag && $token->name === 'html') {
            InBody::process($token, $tree);
            return;
        }
        if ($type === TokenKind::StartTag && $token->name === 'head') {
            $head = $tree->insertElement($token);
            $tree->headElement = $head;
            $tree->insertionMode = InsertionModes::IN_HEAD;
            return;
        }
        if ($type === TokenKind::EndTag) {
            if (isset(self::HEAD_INSERTION_END_TAGS[$token->name])) {
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
