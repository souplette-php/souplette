<?php declare(strict_types=1);

namespace Souplette\Codegen;

use Souplette\Codegen\Twig\CodeGeneratorExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class AbstractCodeGenerator
{
    const CACHE_DIR = __DIR__.'/../tmp';

    final public function generate()
    {
        $twig = $this->createEnvironment();
        $context = $this->createContext();
        $code = $twig->render($this->getTemplateFile(), $context);
        file_put_contents($this->getOutputFile(), $code);
        $this->fixCodeStyle($this->getOutputFile(), $this->getCodeStyleRules());
    }

    abstract protected function getTemplateFile(): string;
    abstract protected function getOutputFile(): string;

    protected function createEnvironment(): Environment
    {
        $loader = new FilesystemLoader(__DIR__ . '/templates');
        $twig = new Environment($loader, [
            'strict_variables' => true,
            'autoescape' => false,
        ]);
        $twig->addExtension(new CodeGeneratorExtension());

        return $twig;
    }

    protected function createContext(): array
    {
        return [];
    }

    protected function getCacheDirectory(): string
    {
        $cacheDir = realpath(self::CACHE_DIR);
        if (!$cacheDir && !mkdir($cacheDir, 0755, true)) {
            throw new \RuntimeException(sprintf(
                "Could not create cache directory: %s",
                self::CACHE_DIR
            ));
        }

        return $cacheDir;
    }

    protected function getCodeStyleRules(): array
    {
        return [
            '@PSR2' => true,
            'array_indentation' => true,
            //'declare_strict_types' => true,
            //'linebreak_after_opening_tag' => false,
            //'blank_line_after_opening_tag' => false,
            'no_extra_blank_lines' => [
                'tokens' => ['extra', 'curly_brace_block', 'square_brace_block'],
            ],
            'native_function_invocation' => [
                'include' => ['@all'],
            ],
        ];
    }

    protected function fixCodeStyle(string $file, array $rules = []): void
    {
        $bin = realpath(__DIR__.'/../tools/php-cs-fixer.phar');
        if (!$bin) {
            throw new \RuntimeException(
                'php-cs-fixer.phar not found. Please run `phive install`.'
            );
        }
        $cmd = [
            escapeshellarg($bin),
            'fix',
            '--allow-risky=yes',
            '--quiet',
            '--using-cache=no',
        ];
        if ($rules) {
            $cmd[] = sprintf('--rules=%s', escapeshellarg(json_encode($rules)));
        }
        if (version_compare(PHP_VERSION, '8.1', '>=')) {
            array_unshift($cmd, 'PHP_CS_FIXER_IGNORE_ENV=1');
        }
        $cmd[] = escapeshellarg($file);
        passthru(implode(' ', $cmd));
    }
}
