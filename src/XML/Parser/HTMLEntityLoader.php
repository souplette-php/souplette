<?php declare(strict_types=1);

namespace Souplette\XML\Parser;

final class HTMLEntityLoader implements ExternalEntityLoaderInterface
{
    private const HTML_PUBLIC_IDS = [
        '-//W3C//DTD XHTML 1.0 Transitional//EN' => true,
        '-//W3C//DTD XHTML 1.1//EN' => true,
        '-//W3C//DTD XHTML 1.0 Strict//EN' => true,
        '-//W3C//DTD XHTML 1.0 Frameset//EN' => true,
        '-//W3C//DTD XHTML Basic 1.0//EN' => true,
        '-//W3C//DTD XHTML 1.1 plus MathML 2.0//EN' => true,
        '-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN' => true,
        '-//W3C//DTD MathML 2.0//EN' => true,
        '-//WAPFORUM//DTD XHTML Mobile 1.0//EN' => true,
    ];

    public function __invoke(?string $publicId, string $systemId, array $context): string|null
    {
        if (isset(self::HTML_PUBLIC_IDS[$publicId])) {
            return __DIR__ . '/html.dtd';
        }
        return null;
    }
}
