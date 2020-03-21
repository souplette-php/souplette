<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Codegen;

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
            'no_extra_blank_lines' => ['extra', 'curly_brace_block'],
        ];
    }

    protected function fixCodeStyle(string $file, array $rules = []): void
    {
        $bin = realpath(__DIR__.'/../tools/php-cs-fixer');
        if (!$bin) {
            throw new \RuntimeException(
                'php-cs-fixer binary not found. Please run `phive install`.'
            );
        }
        $cmd = [
            escapeshellarg($bin),
            'fix',
            '--quiet',
            '--using-cache=no',
        ];
        if ($rules) {
            $cmd[] = sprintf('--rules=%s', escapeshellarg(json_encode($rules)));
        }
        $cmd[] = escapeshellarg($file);
        passthru(implode(' ', $cmd));
    }
}
