<?php declare(strict_types=1);

namespace Souplette\Dom\Elements;

use DOMDocumentFragment;
use Souplette\Dom\Api\HtmlTemplateElementInterface;
use Souplette\Dom\Node\HtmlElement;

/**
 * @property-read DOMDocumentFragment|null $content
 */
final class HtmlTemplateElement extends HtmlElement implements HtmlTemplateElementInterface
{
    private DOMDocumentFragment $internalContent;

    public function getContent(): ?DOMDocumentFragment
    {
        if (isset($this->internalContent)) {
            return $this->internalContent;
        }
        if ($this->ownerDocument) {
            $this->internalContent = $this->ownerDocument->createDocumentFragment();
            return $this->internalContent;
        }
        return null;
    }
}
