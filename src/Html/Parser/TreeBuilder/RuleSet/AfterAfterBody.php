<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\TreeBuilder\RuleSet;

use JoliPotage\Html\Parser\Tokenizer\Token;
use JoliPotage\Html\Parser\Tokenizer\TokenTypes;
use JoliPotage\Html\Parser\TreeBuilder\InsertionLocation;
use JoliPotage\Html\Parser\TreeBuilder\InsertionModes;
use JoliPotage\Html\Parser\TreeBuilder\RuleSet;
use JoliPotage\Html\Parser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-after-after-body-insertion-mode
 */
final class AfterAfterBody extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::COMMENT) {
            // Insert a comment as the last child of the Document object.
            $tree->insertComment($token, new InsertionLocation($tree->document));
        } elseif (
            $type === TokenTypes::DOCTYPE
            || ($type === TokenTypes::CHARACTER && ctype_space($token->data))
            || ($type === TokenTypes::START_TAG && $token->name === 'html')
        ) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } elseif ($type === TokenTypes::EOF) {
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
