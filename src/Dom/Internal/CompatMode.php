<?php declare(strict_types=1);

namespace Souplette\Dom\Internal;

enum CompatMode: string
{
    case CSS1 = 'CSS1Compat';
    case BACK = 'BackCompat';
}
