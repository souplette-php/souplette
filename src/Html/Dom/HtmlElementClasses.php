<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom;

use JoliPotage\Html\Dom\Elements\HtmlTemplateElement;
use JoliPotage\Html\Namespaces;

final class HtmlElementClasses
{
    const ELEMENTS = [
        Namespaces::HTML => [
            'template' => HtmlTemplateElement::class,
        ],
    ];
}
