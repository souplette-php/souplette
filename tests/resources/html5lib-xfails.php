<?php declare(strict_types=1);

/**
 * An array of html5lib tests that are expected to fail,
 * either because the library's implementation goes against the spec,
 * or because of unavoidable discrepancies between the underlying DOM implementations.
 */
return [
    'encoding' => [
        'tests1.dat::54' => 'Not spec compliant.',
    ],
    'tree-construction' => [
        'adoption01.dat::17' => 'Invalid adoption agency behavior.',
        'webkit02.dat::14' => 'Invalid adoption agency behavior.',
        'webkit02.dat::15' => 'Invalid adoption agency behavior.',
        'webkit02.dat::16' => 'Invalid adoption agency behavior.',
        'tests11.dat::2' => 'Invalid SVG attribute normalization.',
        'tests11.dat::4' => 'Invalid SVG attribute normalization.',
        'tests11.dat::5' => 'Invalid SVG attribute normalization.',
        'tests11.dat::6' => 'Invalid SVG attribute normalization.',
    ],
];
