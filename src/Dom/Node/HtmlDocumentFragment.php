<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

use Souplette\Dom\Traits\ParentNodeTrait;

final class HtmlDocumentFragment extends \DOMDocumentFragment implements \DOMParentNode
{
    use ParentNodeTrait;
}
