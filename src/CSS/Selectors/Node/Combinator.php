<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node;

enum Combinator: string
{
    case DESCENDANT = ' ';
    case CHILD = '>';
    case NEXT_SIBLING = '+';
    case SUBSEQUENT_SIBLING = '~';
    case COLUMN = '||';
}
