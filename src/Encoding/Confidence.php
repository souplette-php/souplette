<?php declare(strict_types=1);

namespace Souplette\Encoding;

enum Confidence
{
    case IRRELEVANT;
    case TENTATIVE;
    case CERTAIN;
}
