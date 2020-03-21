<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Serializer;

use ju1ius\HtmlParser\TreeBuilder\Elements as TreeBuilderElements;

final class Elements
{
    const VOID_ELEMENTS = TreeBuilderElements::VOID_ELEMENTS + [
        'basefont' => true,
        'bgsound' => true,
        'frame' => true,
        'keygen' => true,
    ];

    const RCDATA_ELEMENTS = [
        'style' => true,
        'script' => true,
        'xmp' => true,
        'iframe' => true,
        'noembed' => true,
        'noframes' => true,
        'plaintext' => true,
        'noscript' => true,
    ];
}
