<?php declare(strict_types=1);

use Symfony\Component\Finder\SplFileInfo;

// exclude generated files as they use their own rule sets.
$excludedFiles = [
    'Encoding/EncodingLookup.php' => true,
    'Html/Parser/Tokenizer/EntityLookup.php' => true,
    'Html/Parser/Tokenizer/Tokenizer.php' => true,
];

$finder = PhpCsFixer\Finder::create()
    ->exclude([
        'resources',
    ])
    ->filter(fn(SplFileInfo $f) => !isset($excludedFiles[$f->getRelativePathname()]))
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

$config = new PhpCsFixer\Config();

return $config
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/tools/.php-cs-fixer.cache')
    ->setRules([
        //'@PSR2' => true,
        'array_syntax' => true,
        'elseif' => false,
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
        ],
        'declare_strict_types' => true,
        'linebreak_after_opening_tag' => false,
        'blank_line_after_opening_tag' => false,
        //'static_lambda' => true,
        'function_declaration' => [
            'closure_function_spacing' => 'none',
        ],
    ]);
