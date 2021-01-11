<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Node;

use Souplette\Html\Dom\Api\HtmlNodeInterface;
use Souplette\Html\Dom\Traits\HtmlNodeTrait;

final class HtmlAttribute extends \DOMAttr implements HtmlNodeInterface
{
    use HtmlNodeTrait;
}
