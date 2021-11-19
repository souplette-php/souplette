<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Internal;

use Souplette\Html\Dom\Elements\HtmlTemplateElement;
use Souplette\Html\Dom\Node\HtmlElement;
use Souplette\Html\Dom\Node\MathMLElement;
use Souplette\Html\Dom\Node\SvgElement;
use Souplette\Html\Namespaces;

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
