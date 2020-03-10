<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-incdata
 */
final class Text extends RuleSet
{
    public function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::CHARACTER) {
            $tree->insertCharacter($token);
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'script') {
            // Pop the current node off the stack of open elements.
            $script = $tree->openElements->pop();
            // Switch the insertion mode to the original insertion mode.
            $tree->setInsertionMode($tree->originalInsertionMode);
            // TODO: check if the steps in the specs are relevant since we don't execute scripts.
        } elseif ($type === TokenTypes::END_TAG) {
            // Pop the current node off the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to the original insertion mode.
            $tree->setInsertionMode($tree->originalInsertionMode);
        }
    }
}
