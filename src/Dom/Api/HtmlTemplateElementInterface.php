<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use DOMDocumentFragment;

/**
 * @property-read DOMDocumentFragment|null $content
 */
interface HtmlTemplateElementInterface extends HtmlElementInterface
{
    const PROPERTIES_READ = [
        'content' => 'getContent',
    ];
    public function getContent(): ?DOMDocumentFragment;
}
