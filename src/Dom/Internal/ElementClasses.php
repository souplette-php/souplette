<?php declare(strict_types=1);

namespace Souplette\Dom\Internal;

use Souplette\Dom\Elements\HtmlTemplateElement;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node\HtmlElement;
use Souplette\Dom\Node\MathMLElement;
use Souplette\Dom\Node\SvgElement;

final class ElementClasses
{
    const BASES = [
        Namespaces::HTML => HtmlElement::class,
        Namespaces::SVG => SvgElement::class,
        Namespaces::MATHML => MathMLElement::class,
    ];

    const ELEMENTS = [
        Namespaces::HTML => [
            'template' => HtmlTemplateElement::class,
        ],
    ];
}
