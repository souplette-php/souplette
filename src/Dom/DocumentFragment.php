<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Dom\Traits\ParentNodeTrait;

final class DocumentFragment extends \DOMDocumentFragment implements \DOMParentNode
{
    use ParentNodeTrait;
}
