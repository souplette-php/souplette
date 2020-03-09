<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inbody
 */
final class InBody extends RuleSet
{
    public function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::CHARACTER) {
            $data = $token->data;
            if ($data === "\0") {
                // TODO: Parse error. Ignore the token.
            } elseif (ctype_space($data)) {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert the token's character.
                $tree->insertCharacter($token);
            } else {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert the token's character.
                $tree->insertCharacter($token);
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
            }
            return;
        } elseif ($type === TokenTypes::COMMENT) {
            $tree->insertComment($token);
            return;
        } elseif ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error. Ignore the token.
            return;
        }
        if ($type === TokenTypes::CHARACTER && $token->data === "\0") {
        } elseif ($type === TokenTypes::CHARACTER && ctype_space($token->data)) {
            // Reconstruct the active formatting elements, if any.
            $tree->reconstructTheListOfActiveElements();
            // Insert the token's character.
            $tree->insertCharacter($token);
            return;
        }
    }
}
