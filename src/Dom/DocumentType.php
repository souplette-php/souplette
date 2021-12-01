<?php declare(strict_types=1);

namespace Souplette\Dom;

use DOMNode;
use Souplette\Dom\Api\NodeInterface;
use Souplette\Dom\Traits\NodeTrait;

final class DocumentType extends \DOMDocumentType implements NodeInterface
{
    use NodeTrait;
}
