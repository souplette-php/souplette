<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Elements;

use DOMDocumentFragment;
use JoliPotage\Html\Dom\Api\HtmlTemplateElementInterface;
use JoliPotage\Html\Dom\HtmlElement;

/**
 * @property-read DOMDocumentFragment|null $content
 */
final class HtmlTemplateElement extends HtmlElement implements HtmlTemplateElementInterface
{
    /**
     * @var DOMDocumentFragment
     */
    private $internalContent;

    public function __get($name)
    {
        if ($name === 'content') {
            return $this->getContent();
        }
    }

    public function getContent(): ?DOMDocumentFragment
    {
        if ($this->internalContent) {
            return $this->internalContent;
        }
        if ($this->ownerDocument) {
            $this->internalContent = $this->ownerDocument->createDocumentFragment();
            return $this->internalContent;
        }
        return null;
    }
}
