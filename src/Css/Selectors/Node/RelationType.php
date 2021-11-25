<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

enum RelationType
{
    case NONE;
    case SUB;
    case CHILD;
    case DESCENDANT;
    case ADJACENT;
    case FOLLOWING;
    case COLUMN;
    case RELATIVE_CHILD;
    case RELATIVE_DESCENDANT;
    case RELATIVE_ADJACENT;
    case RELATIVE_FOLLOWING;

    public static function toCss(self $type): string
    {
        return match ($type) {
            self::SUB, self::NONE, self::RELATIVE_DESCENDANT => '',
            self::CHILD => ' > ',
            self::RELATIVE_CHILD => '> ',
            self::DESCENDANT => ' ',
            self::ADJACENT => ' + ',
            self::RELATIVE_ADJACENT => '+ ',
            self::FOLLOWING => ' ~ ',
            self::RELATIVE_FOLLOWING => '~ ',
            self::COLUMN => ' || ',
        };
    }

    public static function fromCombinator(Combinator $combinator, bool $relative = false): self
    {
        return match ($combinator) {
            Combinator::CHILD => $relative ? self::RELATIVE_CHILD : self::CHILD,
            Combinator::DESCENDANT => $relative ? self::RELATIVE_DESCENDANT : self::DESCENDANT,
            Combinator::NEXT_SIBLING => $relative ? self::RELATIVE_ADJACENT : self::ADJACENT,
            Combinator::SUBSEQUENT_SIBLING => $relative ? self::RELATIVE_FOLLOWING : self::FOLLOWING,
            Combinator::COLUMN => self::COLUMN,
        };
    }
}
