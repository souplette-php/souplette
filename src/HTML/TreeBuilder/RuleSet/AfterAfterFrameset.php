<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\InsertionLocation;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-after-after-frameset-insertion-mode
 */
final class AfterAfterFrameset extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree): void
    {
        $type = $token::KIND;
        if ($type === TokenKind::Comment) {
            // Insert a comment as the last child of the Document object.
            $tree->insertComment($token, new InsertionLocation($tree->document));
        } else if (
            $type === TokenKind::Doctype
            || ($type === TokenKind::Characters && strspn($token->data, "\t\n\f\r ") === \strlen($token->data))
            || ($type === TokenKind::StartTag && $token->name === 'html')
        ) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else if ($type === TokenKind::EOF) {
            // TODO: Stop parsing.
            return;
        } else if ($type === TokenKind::StartTag && $token->name === 'noframes') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } else {
            // TODO: Parse error. Ignore the token.
            return;
        }
    }
}
