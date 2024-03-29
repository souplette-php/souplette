<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-intabletext
 */
final class InTableText extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree): void
    {
        $type = $token::KIND;
        if ($type === TokenKind::Characters) {
            if ($token->data === "\0") {
                // TODO: Parse error.
                // Ignore the token.
                return;
            }
            // Append the character token to the pending table character tokens list.
            $tree->pendingTableCharacterTokens[] = $token;
            return;
        }

        if ($tree->pendingTableCharacterTokens) {
            $pendingCharacterData = '';
            foreach ($tree->pendingTableCharacterTokens as $pendingTableCharacterToken) {
                $pendingCharacterData .= $pendingTableCharacterToken->data;
            }
            // If any of the tokens in the pending table character tokens list
            // are character tokens that are not ASCII whitespace,
            if (!ctype_space($pendingCharacterData)) {
                // then this is a parse error:
                // reprocess the character tokens in the pending table character tokens list
                // using the rules given in the "anything else" entry in the "in table" insertion mode.
                InTable::processWithFosterParenting(new Token\Character($pendingCharacterData), $tree);
            } else {
                // Otherwise, insert the characters given by the pending table character tokens list.
                $tree->insertCharacter(new Token\Character($pendingCharacterData));
            }
        }
        // Switch the insertion mode to the original insertion mode and reprocess the token.
        $tree->insertionMode = $tree->originalInsertionMode;
        $tree->processToken($token);
    }
}
