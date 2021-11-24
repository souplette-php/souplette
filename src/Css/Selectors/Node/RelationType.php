<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

enum RelationType
{
    case NONE;
    case SUB;
    case CHILD;
    case DESCENDANT;
    case NEXT;
    case FOLLOWING;
    case COLUMN;

    public static function toCss(self $type): string
    {
        return match ($type) {
            self::SUB, self::NONE => '',
            self::CHILD => ' > ',
            self::DESCENDANT => ' ',
            self::NEXT => ' + ',
            self::FOLLOWING => ' ~ ',
            self::COLUMN => ' || ',
        };
    }

    public static function fromCombinator(Combinator $combinator): self
    {
        return match ($combinator) {
            Combinator::CHILD => self::CHILD,
            Combinator::DESCENDANT => self::DESCENDANT,
            Combinator::NEXT_SIBLING => self::NEXT,
            Combinator::SUBSEQUENT_SIBLING => self::FOLLOWING,
            Combinator::COLUMN => self::COLUMN,
        };
    }
}
