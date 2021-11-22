<?php declare(strict_types=1);

namespace Souplette\Html\Serializer;

use Souplette\Html\TreeBuilder\Elements as TreeBuilderElements;

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

    const BOOLEAN_ATTRIBUTES = [
        '*' => [
            'hidden' => true,
            'irrelevant' => true,
            'itemscope' => true,
        ],
        'style' => [
            'scoped' => true,
        ],
        'img' => [
            'ismap' => true,
        ],
        'audio' => [
            'autoplay' => true,
            'controls' => true,
        ],
        'video' => [
            'autoplay' => true,
            'controls' => true,
        ],
        'script' => [
            'defer' => true,
            'async' => true,
        ],
        'details' => [
            'open' => true,
        ],
        'datagrid' => [
            'multiple' => true,
            'disabled' => true,
        ],
        'command' => [
            'disabled' => true,
            'checked' => true,
            'default' => true,
        ],
        'hr' => [
            'noshade' => true,
        ],
        'menu' => [
            'autosubmit' => true,
        ],
        'fieldset' => [
            'disabled' => true,
            'readonly' => true,
        ],
        'option' => [
            'disabled' => true,
            'readonly' => true,
            'selected' => true,
        ],
        'optgroup' => [
            'disabled' => true,
            'readonly' => true,
        ],
        'button' => [
            'disabled' => true,
            'autofocus' => true,
        ],
        'input' => [
            'disabled' => true,
            'readonly' => true,
            'required' => true,
            'autofocus' => true,
            'checked' => true,
            'ismap' => true,
        ],
        'select' => [
            'disabled' => true,
            'readonly' => true,
            'autofocus' => true,
            'multiple' => true,
        ],
        'output' => [
            'disabled' => true,
            'readonly' => true,
        ],
        'iframe' => [
            'seamless' => true,
        ],
    ];
}
