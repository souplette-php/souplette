<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Node;

use Souplette\Html\Dom\Traits\ParentNodeTrait;

final class HtmlDocumentFragment extends \DOMDocumentFragment implements \DOMParentNode
{
    use ParentNodeTrait;
}
