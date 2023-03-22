<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\InsertionLocation;
use Souplette\HTML\TreeBuilder\InsertionModes;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-after-after-body-insertion-mode
 */
final class AfterAfterBody extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::KIND;
        if ($type === TokenKind::Comment) {
            // Insert a comment as the last child of the Document object.
            $tree->insertComment($token, new InsertionLocation($tree->document));
        } else if (
            $type === TokenKind::Doctype
            || ($type === TokenKind::Characters && ctype_space($token->data))
            || ($type === TokenKind::StartTag && $token->name === 'html')
        ) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else if ($type === TokenKind::EOF) {
            // TODO: Stop parsing.
            return;
        } else {
            // TODO: Parse error.
            // Switch the insertion mode to "in body" and reprocess the token.
            $tree->insertionMode = InsertionModes::IN_BODY;
            $tree->processToken($token);
        }
    }
}
