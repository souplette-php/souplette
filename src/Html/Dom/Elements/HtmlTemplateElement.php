<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Elements;

use DOMDocumentFragment;
use Souplette\Html\Dom\Api\HtmlTemplateElementInterface;
use Souplette\Html\Dom\Node\HtmlElement;

/**
 * @property-read DOMDocumentFragment|null $content
 */
final class HtmlTemplateElement extends HtmlElement implements HtmlTemplateElementInterface
{
    private DOMDocumentFragment $internalContent;

    public function __get($name)
    {
        if ($name === 'content') {
            return $this->getContent();
        }
        return parent::__get($name);
    }

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
