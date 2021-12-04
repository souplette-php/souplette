<?php declare(strict_types=1);

namespace Souplette\Dom\Legacy;

use Souplette\Dom\Legacy\Api\NodeInterface;
use Souplette\Dom\Legacy\Traits\NodeTrait;

final class DocumentType extends \DOMDocumentType implements NodeInterface
{
    use NodeTrait;
}
