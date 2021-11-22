<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder\RuleSet;

use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;
use Souplette\Html\TreeBuilder;
use Souplette\Html\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inselectintable
 */
final class InSelectInTable extends RuleSet
{
    private const PARSE_ERROR_START_TAGS = [
        'caption' => true,
        'table' => true,
        'tbody' => true,
        'tfoot' => true,
        'thead' => true,
        'tr' => true,
        'td' => true,
        'th' => true,
    ];
    private const PARSE_ERROR_END_TAGS = [
        'caption' => true,
        'table' => true,
        'tbody' => true,
        'tfoot' => true,
        'thead' => true,
        'tr' => true,
        'td' => true,
        'th' => true,
    ];

    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::START_TAG && isset(self::PARSE_ERROR_START_TAGS[$token->name])) {
            // TODO: Parse error.
            // Pop elements from the stack of open elements until a select element has been popped from the stack.
            $tree->openElements->popUntilTag('select');
            // Reset the insertion mode appropriately.
            $tree->resetInsertionModeAppropriately();
            // Reprocess the token.
            $tree->processToken($token);
        } else if ($type === TokenType::END_TAG && isset(self::PARSE_ERROR_END_TAGS[$token->name])) {
            // TODO: Parse error.
            // If the stack of open elements does not have an element in table scope
            // that is an HTML element with the same tag name as that of the token, then ignore the token.
            if (!$tree->openElements->hasTagInTableScope($token->name)) {
                return;
            }
            // Otherwise:
            // Pop elements from the stack of open elements until a select element has been popped from the stack.
            $tree->openElements->popUntilTag('select');
            // Reset the insertion mode appropriately.
            $tree->resetInsertionModeAppropriately();
            // Reprocess the token.
            $tree->processToken($token);
        } else {
            // Process the token using the rules for the "in select" insertion mode.
            InSelect::process($token, $tree);
        }
    }
}
