<?php declare(strict_types=1);

namespace Souplette\Html\Dom;

use Souplette\Html\Dom\Elements\HtmlTemplateElement;
use Souplette\Html\Namespaces;

final class HtmlElementClasses
{
    const ELEMENTS = [
        Namespaces::HTML => [
            'template' => HtmlTemplateElement::class,
        ],
    ];
}
