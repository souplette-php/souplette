<?php declare(strict_types=1);

namespace Souplette\Dom\Internal;

enum DocumentMode: string
{
    case QUIRKS = 'quirks';
    case LIMITED_QUIRKS = 'limited quirks';
    case NO_QUIRKS = 'no quirks';
}
